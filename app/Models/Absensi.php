<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\Concerns\MilikPeriodePkl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory, MilikPeriodePkl;

    /**
     * Kolom yang menyimpan ID siswa pemilik data.
     * Dipakai trait MilikPeriodePkl untuk mengisi periode_id otomatis.
     */
    protected string $kolomPemilikPeriode = 'siswa_id';

    protected $fillable = [
        'siswa_id',
        'periode_id',   // periode PKL tempat data ini dibuat (diisi otomatis)
        'tanggal',
        'status',            // Hadir | Izin | Sakit | Alpha
        'jam_masuk',
        'jam_pulang',
        'status_validasi',   // draft | diajukan | disetujui
        'foto_bukti',

        // --- Tanda tangan digital guru pembimbing saat memvalidasi absensi ---
        'ttd_guru',            // path berkas paraf di disk 'public' (mis. ttd/absensi/guru/xxx.png)
        'ttd_guru_nama',       // nama guru yang membubuhkan paraf
        'ttd_guru_signed_at',  // waktu paraf dibubuhkan

        'catatan_instruktur',
        'validated_by_guru_id',
        'validated_at',
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'validated_at'       => 'datetime',
        'ttd_guru_signed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */
    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id')->withTrashed();
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_guru_id')->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor: keterangan "Telat Masuk"
    |--------------------------------------------------------------------------
    | Bernilai true bila siswa TIDAK melakukan absen masuk namun MELAKUKAN
    | absen pulang (status Hadir, jam_masuk kosong, jam_pulang terisi).
    | Dipakai di tampilan untuk menampilkan keterangan "Telat Masuk".
    */
    public function getTelatMasukAttribute(): bool
    {
        return $this->status === 'Hadir'
            && empty($this->jam_masuk)
            && ! empty($this->jam_pulang);
    }

    /*
    |--------------------------------------------------------------------------
    | Penandaan Otomatis ALPHA (logika di controller, bukan scheduler)
    |--------------------------------------------------------------------------
    | Menandai siswa sebagai "Alpha" untuk setiap hari (dalam bulan berjalan)
    | yang jendela absensinya SUDAH LEWAT (batas jam masuk & pulang terlampaui)
    | namun siswa TIDAK memiliki baris absensi apa pun pada hari itu.
    |
    | Dipanggil saat halaman absensi dibuka (siswa / guru / admin) sehingga
    | tidak lagi bergantung pada cron/scheduler.
    |
    | HARI LIBUR TIDAK PERNAH DITANDAI ALPHA. Hari kerja mengikuti jadwal
    | efektif siswa (Senin-Jumat atau Senin-Sabtu, lihat User::adalahHariKerja).
    | Sabtu/Minggu di luar jadwal dibiarkan kosong tanpa baris absensi.
    */
    public static function sinkronkanAlpa(User $siswa): void
    {
        if (($siswa->status_pkl ?? null) !== 'aktif') {
            return;
        }

        $tz  = config('app.timezone', 'Asia/Makassar');
        $now = Carbon::now($tz);

        // OPTIMASI (hindari tulis-saat-GET yang membebani server):
        // Halaman absensi bisa dibuka berkali-kali. Tanpa guard, SETIAP request GET
        // menjalankan query SELECT + loop (dan berpotensi INSERT). Guard cache ini
        // membuat sinkronisasi hanya berjalan sekali sampai ada kemungkinan data
        // baru, yaitu setelah jendela absensi HARI INI tertutup.
        $tanggalHariIni = $now->format('Y-m-d');
        $cacheKey       = "sinkron_alpa:{$siswa->id}:{$tanggalHariIni}";

        if (Cache::has($cacheKey)) {
            return;
        }

        // Cegah duplikasi baris Alpha bila beberapa request datang bersamaan
        // (mis. dua tab / klik ganda). Hanya satu request yang memproses.
        $lock = Cache::lock("sinkron_alpa_lock:{$siswa->id}", 10);

        if (! $lock->get()) {
            return;
        }

        try {
            // Double-check di dalam lock: mungkin request lain sudah menyelesaikannya.
            if (Cache::has($cacheKey)) {
                return;
            }

            $durasi = (int) Pengaturan::ambil('absensi_durasi_menit', 30);
            if ($durasi <= 0) {
                $durasi = 30;
            }

            // Batas akhir jendela pulang mengikuti jam EFEKTIF siswa.
            $jamPulang = $siswa->jamPulangEfektif();

            // Rentang penandaan: awal bulan berjalan s.d. hari ini.
            $mulai = $now->copy()->startOfMonth();

            $sudahAda = static::where('siswa_id', $siswa->id)
                ->whereBetween('tanggal', [$mulai->format('Y-m-d'), $now->format('Y-m-d')])
                ->pluck('tanggal')
                ->map(fn ($t) => Carbon::parse($t)->format('Y-m-d'))
                ->all();

            // insert() massal MELEWATI event model, sehingga trait
            // MilikPeriodePkl tidak ikut jalan. Periode diisi manual di sini.
            $periodeId = $siswa->periode_id ?: static::periodeAktifId();

            $baris = [];
            for ($d = $mulai->copy(); $d->lte($now); $d->addDay()) {
                $tgl = $d->format('Y-m-d');

                if (in_array($tgl, $sudahAda, true)) {
                    continue; // sudah ada baris (Hadir/Izin/Sakit/Alpha)
                }

                // BUKAN hari kerja (Minggu, dan Sabtu bila jadwal siswa hanya
                // Senin-Jumat) -> jangan pernah ditandai Alpha. Hari libur
                // sengaja dibiarkan KOSONG tanpa baris absensi sama sekali.
                if (! $siswa->adalahHariKerja($d)) {
                    continue;
                }

                // Batas terakhir absensi hari itu (jam pulang + durasi).
                $pulangEnd = Carbon::parse($tgl . ' ' . $jamPulang, $tz)->addMinutes($durasi);

                // Jendela masuk & pulang BELUM lewat -> jangan tandai.
                if ($now->lte($pulangEnd)) {
                    continue;
                }

                $baris[] = [
                    'siswa_id'        => $siswa->id,
                    'periode_id'      => $periodeId,
                    'tanggal'         => $tgl,
                    'status'          => 'Alpha',
                    'status_validasi' => 'disetujui',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            if (! empty($baris)) {
                // insertOrIgnore: bila ada request lain yang menyisipkan baris
                // untuk tanggal yang sama pada saat bersamaan, unique index
                // (siswa_id, tanggal) akan menolaknya secara diam-diam,
                // bukan melempar error ke wajah pengguna.
                static::insertOrIgnore($baris);
            }

            // Tandai sudah sinkron. Flag berlaku sampai jendela absensi HARI INI
            // tertutup (agar Alpha hari ini tetap bisa ditandai begitu lewat),
            // atau sampai akhir hari bila jendela hari ini sudah lewat.
            $pulangEndHariIni = Carbon::parse($tanggalHariIni . ' ' . $jamPulang, $tz)->addMinutes($durasi);
            $berlakuSampai    = $now->lt($pulangEndHariIni) ? $pulangEndHariIni : $now->copy()->endOfDay();

            Cache::put($cacheKey, true, $berlakuSampai);
        } finally {
            $lock->release();
        }
    }
}

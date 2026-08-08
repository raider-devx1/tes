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

        // --- Penolakan FOTO oleh guru pembimbing ---
        // Absensi yang ditolak TIDAK dihapus & datanya TIDAK diubah.
        // Siswa hanya wajib mengganti fotonya sebelum batas waktu.
        'foto_ditolak',       // true = foto ditolak, wajib diganti siswa
        'catatan_penolakan',  // alasan penolakan yang diketik guru
        'foto_ditolak_at',    // waktu penolakan (dasar hitung batas waktu)
        'foto_ditolak_by',    // id guru yang menolak
        'foto_diganti_at',    // waktu siswa mengganti foto
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'validated_at'       => 'datetime',
        'ttd_guru_signed_at' => 'datetime',
        'foto_ditolak'       => 'boolean',
        'foto_ditolak_at'    => 'datetime',
        'foto_diganti_at'    => 'datetime',
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

    /** Guru yang menolak foto absensi ini. */
    public function penolak()
    {
        return $this->belongsTo(User::class, 'foto_ditolak_by')->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | PENOLAKAN FOTO: batas waktu & status
    |--------------------------------------------------------------------------
    | Aturan yang disepakati:
    |  - Foto HANYA diambil pada absen JAM MASUK.
    |  - Bila guru menolak, seluruh informasi absensi (tanggal, status, jam
    |    masuk, jam pulang) TETAP. Siswa cukup mengganti fotonya saja.
    |  - Batas waktu mengganti foto: sampai jendela JAM PULANG berakhir
    |    (jam pulang efektif siswa + durasi absensi).
    |  - Selama foto belum diganti, siswa TIDAK boleh absen pulang.
    |  - Bila foto diganti sebelum batas waktu, absensi TIDAK menjadi Alpha.
    */

    /**
     * Batas akhir siswa boleh mengganti foto yang ditolak.
     *
     * Acuan: tanggal PENOLAKAN (bukan tanggal absensi), karena guru bisa saja
     * baru memeriksa absensi beberapa saat setelah siswa mengajukan. Bila guru
     * menolak ketika jendela pulang hari itu SUDAH lewat, siswa otomatis diberi
     * tenggang sampai jendela pulang hari berikutnya (supaya tidak langsung
     * kedaluwarsa begitu ditolak).
     */
    public function batasGantiFoto(): ?Carbon
    {
        if (! $this->foto_ditolak) {
            return null;
        }

        $tz = config('app.timezone', 'Asia/Makassar');

        $siswa     = $this->relationLoaded('siswa') ? $this->siswa : $this->siswa()->first();
        $jamPulang = $siswa
            ? $siswa->jamPulangEfektif()
            : Pengaturan::ambil('absensi_jam_pulang', '16:00');

        $durasi = (int) Pengaturan::ambil('absensi_durasi_menit', 30);
        if ($durasi <= 0) {
            $durasi = 30;
        }

        $ditolakPada = $this->foto_ditolak_at
            ? Carbon::parse($this->foto_ditolak_at)->setTimezone($tz)
            : Carbon::parse($this->tanggal)->setTimezone($tz);

        $batas = Carbon::parse($ditolakPada->format('Y-m-d') . ' ' . $jamPulang, $tz)
            ->addMinutes($durasi);

        // Ditolak setelah jendela pulang hari itu tertutup -> beri tenggang
        // sampai jendela pulang hari berikutnya.
        if ($ditolakPada->gt($batas)) {
            $batas = $batas->addDay();
        }

        return $batas;
    }

    /** Foto ditolak DAN masih dalam batas waktu penggantian. */
    public function getPerluGantiFotoAttribute(): bool
    {
        if (! $this->foto_ditolak) {
            return false;
        }

        $batas = $this->batasGantiFoto();

        return $batas ? Carbon::now(config('app.timezone', 'Asia/Makassar'))->lte($batas) : true;
    }

    /** Foto ditolak TAPI batas waktu penggantian sudah lewat. */
    public function getGantiFotoKedaluwarsaAttribute(): bool
    {
        return $this->foto_ditolak && ! $this->perlu_ganti_foto;
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

        // Foto yang ditolak tapi TIDAK diganti sampai batas waktu -> Alpha.
        // Dipanggil lebih dulu (di luar guard cache Alpha harian) agar batas
        // waktu ganti foto tetap ditegakkan walau baris absensi sudah ada.
        static::tandaiAlpaFotoTidakDiganti($siswa);

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

    /*
    |--------------------------------------------------------------------------
    | Penandaan ALPHA untuk foto DITOLAK yang tidak diganti
    |--------------------------------------------------------------------------
    | Siswa yang fotonya ditolak guru diberi kesempatan mengganti foto sampai
    | jendela JAM PULANG hari itu berakhir. Selama foto sudah diganti sebelum
    | batas waktu, absensi TIDAK menjadi Alpha.
    |
    | Bila sampai batas waktu foto TETAP tidak diganti, absensi hari itu
    | dianggap tidak terbukti dan ditandai Alpha. Jam masuk/pulang dikosongkan
    | agar rekap & PDF konsisten dengan baris Alpha lainnya, dan alasannya
    | dicatat pada kolom catatan_penolakan sebagai jejak.
    */
    public static function tandaiAlpaFotoTidakDiganti(User $siswa): void
    {
        $tz  = config('app.timezone', 'Asia/Makassar');
        $now = Carbon::now($tz);

        // Guard ringan: query hanya dijalankan maksimal sekali per 2 menit
        // per siswa supaya halaman guru (yang memutar banyak siswa) tidak
        // menambah puluhan query pada setiap request.
        $cacheKey = "cek_foto_ditolak:{$siswa->id}";

        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, $now->copy()->addMinutes(2));

        $daftar = static::where('siswa_id', $siswa->id)
            ->where('foto_ditolak', true)
            ->get();

        if ($daftar->isEmpty()) {
            return;
        }

        foreach ($daftar as $absensi) {
            $absensi->setRelation('siswa', $siswa);

            $batas = $absensi->batasGantiFoto();

            // Masih dalam batas waktu -> biarkan, siswa masih boleh ganti foto.
            if (! $batas || $now->lte($batas)) {
                continue;
            }

            $catatanLama = trim((string) $absensi->catatan_penolakan);
            $keterangan  = 'Ditandai Alpha otomatis oleh sistem: foto tidak diganti sampai batas waktu '
                . $batas->format('d/m/Y H:i') . ' WITA.';

            $absensi->forceFill([
                'status'            => 'Alpha',
                'status_validasi'   => 'disetujui',
                'jam_masuk'         => null,
                'jam_pulang'        => null,
                'foto_ditolak'      => false,
                'catatan_penolakan' => $catatanLama !== ''
                    ? $catatanLama . "\n" . $keterangan
                    : $keterangan,
            ])->save();
        }
    }
}

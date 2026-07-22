<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'tanggal',
        'status',            // Hadir | Izin | Sakit | Alpha
        'jam_masuk',
        'jam_pulang',
        'status_validasi',   // draft | diajukan | disetujui
        'foto_bukti',
        'catatan_instruktur',
        'validated_by_guru_id',
        'validated_at',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'validated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */
    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_guru_id');
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
    */
    public static function sinkronkanAlpa(User $siswa): void
    {
        if (($siswa->status_pkl ?? null) !== 'aktif') {
            return;
        }

        $tz  = config('app.timezone', 'Asia/Makassar');
        $now = Carbon::now($tz);

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

        $baris = [];
        for ($d = $mulai->copy(); $d->lte($now); $d->addDay()) {
            $tgl = $d->format('Y-m-d');

            if (in_array($tgl, $sudahAda, true)) {
                continue; // sudah ada baris (Hadir/Izin/Sakit/Alpha)
            }

            // Batas terakhir absensi hari itu (jam pulang + durasi).
            $pulangEnd = Carbon::parse($tgl . ' ' . $jamPulang, $tz)->addMinutes($durasi);

            // Jendela masuk & pulang BELUM lewat -> jangan tandai.
            if ($now->lte($pulangEnd)) {
                continue;
            }

            $baris[] = [
                'siswa_id'        => $siswa->id,
                'tanggal'         => $tgl,
                'status'          => 'Alpha',
                'status_validasi' => 'disetujui',
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        if (! empty($baris)) {
            static::insert($baris);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\Pengaturan;
use App\Models\PeriodePkl;
use Carbon\Carbon;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakPdfController extends Controller
{
    /**
     * Tentukan siswa target + cek hak akses sesuai role.
     * Guru & instruktur hanya boleh mencetak siswa bimbingannya.
     */
    private function resolveSiswa($siswaId = null): User
    {
        $user = auth()->user();

        // Siswa hanya boleh mencetak miliknya sendiri (param diabaikan)
        if ($user->role === 'siswa_pkl') {
            return $user;
        }

        abort_if(empty($siswaId), 404, 'Siswa tidak ditemukan.');
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($siswaId);

        // Menggunakan perbandingan longgar (==) karena di beberapa environment hosting (seperti Namecheap),
        // ID dari database bisa terbaca sebagai string sedangkan auth ID bertipe integer.
        if ($user->role === 'guru_pembimbing') {
            abort_unless(
                $siswa->guru_id == $user->id,
                403,
                'Bukan siswa bimbingan Anda.'
            );
        } elseif ($user->role === 'instruktur_industri') {
            abort_unless(
                $siswa->instruktur_id == $user->id,
                403,
                'Bukan siswa bimbingan Anda.'
            );
        }
        // admin: tanpa batasan

        return $siswa;
    }

    private function getPengaturan(): array
    {
        return Pengaturan::pluck('nilai', 'kunci')->toArray();
    }

    // ====== 1. JURNAL (FK: siswa_id) ======
    /** Bangun data lembar jurnal: 1 siswa = 1 lembar, semua jurnal jadi baris bernomor urut. */
    private function buildJurnalLembar(User $siswa, ?string $tanggal = null): array
    {
        $query = Jurnal::where('siswa_id', $siswa->id)->with('items');

        if ($tanggal) {
            $query->whereDate('hari_tanggal', $tanggal);
        }

        $jurnals = $query->orderBy('hari_tanggal', 'asc')->get();

        // Satu lembar berisi SEMUA jurnal -> tampil sebagai baris bernomor urut (1,2,3,...)
        return [
            'siswa'   => $siswa,
            'jurnals' => $jurnals,
        ];
    }

    public function cetakJurnal($siswa_id = null)
    {
        $siswa = $this->resolveSiswa($siswa_id);
        $siswa->loadMissing(['perusahaan', 'instruktur', 'guru']);

        $query = Jurnal::where('siswa_id', $siswa->id)->with('items');

        // Cetak SATU entri jurnal (tombol PDF per baris)
        if (request()->filled('jurnal_id')) {
            $query->where('id', request('jurnal_id'));
        }
        // (opsional) semua jurnal pada satu tanggal
        elseif (request()->filled('tanggal')) {
            $query->whereDate('hari_tanggal', request('tanggal'));
        }
        // Tanpa filter -> semua jurnal siswa (tombol "Cetak Semua PDF")

        $jurnals = $query->orderBy('hari_tanggal', 'asc')->get();

        abort_if(
            (request()->filled('jurnal_id') || request()->filled('tanggal')) && $jurnals->isEmpty(),
            404,
            'Jurnal tidak ditemukan untuk dicetak.'
        );

        // Semua jurnal dalam SATU lembar/tabel, bernomor urut (tanggal berikutnya jadi nomor 2, dst.)
        $lembar = $jurnals->isEmpty() ? [] : [[
            'siswa'   => $siswa,
            'jurnals' => $jurnals,
        ]];

        $pengaturan = $this->getPengaturan();

        $pdf = Pdf::loadView('pdf.jurnal', compact('lembar', 'pengaturan'))
                  ->setPaper('a4', 'portrait');

        $suffix = request('jurnal_id')
            ? '_'.request('jurnal_id')
            : (request('tanggal') ? '_'.request('tanggal') : '');

        return $pdf->stream('Jurnal_PKL_'.$siswa->name.$suffix.'.pdf');
    }

    // ====== 1b. JURNAL - CETAK SEMUA (semua siswa bimbingan, 1 siswa 1 halaman) ======
    public function cetakJurnalSemua()
    {
        $user = auth()->user();

        if (!in_array($user->role, ['instruktur_industri', 'guru_pembimbing', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        // Default: hanya jurnal HARI INI. Jika ada filter tanggal → pakai tanggal itu.
        $tanggal = request()->filled('tanggal')
            ? request('tanggal')
            : Carbon::today()->toDateString();

        $query = User::where('role', 'siswa_pkl')
            ->where('status_pkl', 'aktif')
            ->with(['perusahaan', 'instruktur', 'guru']);

        if ($user->role === 'instruktur_industri') {
            $query->where('instruktur_id', $user->id);
        } elseif ($user->role === 'guru_pembimbing') {
            $query->where('guru_id', $user->id);
        }

        $siswas = $query->orderBy('name')->get();

        // Hanya sertakan siswa yang punya jurnal pada tanggal tsb (1 siswa = 1 halaman)
        $lembar = [];
        foreach ($siswas as $siswa) {
            $data = $this->buildJurnalLembar($siswa, $tanggal);
            if ($data['jurnals']->isNotEmpty()) {
                $lembar[] = $data;
            }
        }

        abort_if(empty($lembar), 404, 'Tidak ada jurnal pada tanggal tersebut untuk dicetak.');

        $pengaturan = $this->getPengaturan();

        $pdf = Pdf::loadView('pdf.jurnal', compact('lembar', 'pengaturan'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('Jurnal_PKL_Semua_'.$tanggal.'.pdf');
    }

    // ====== 2. CATATAN (FK: user_id) ======
    public function cetakCatatan($siswa_id = null)
    {
        $siswa = $this->resolveSiswa($siswa_id);

        $query = CatatanKegiatan::with(['user.perusahaan', 'user.instruktur', 'user.guru'])
            ->where('user_id', $siswa->id);

        // Jika ada catatan_id → cetak SATU data (baris yang dipilih) saja
        if (request()->filled('catatan_id')) {
            $query->where('id', request('catatan_id'));
        } else {
            // Tanpa catatan_id → cetak semua yang sudah disetujui (milik siswa ini)
            $query->where('is_approved', true);
        }

        $catatan = $query->orderBy('created_at', 'asc')->get();

        $data = [
            'tanggal_cetak' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'catatan'       => $catatan,
        ];

        $pdf = Pdf::loadView('pdf.catatan', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Catatan_Kegiatan_PKL_'.$siswa->name.'.pdf');
    }

    // ====== 2b. CATATAN - CETAK SEMUA (semua siswa bimbingan, 1 catatan 1 halaman) ======
    public function cetakCatatanSemua()
    {
        $user = auth()->user();

        if (!in_array($user->role, ['instruktur_industri', 'guru_pembimbing', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $catatan = CatatanKegiatan::with(['user.perusahaan', 'user.instruktur', 'user.guru'])
            ->where('is_approved', true)
            ->whereHas('user', function ($u) use ($user) {
                $u->where('role', 'siswa_pkl')->where('status_pkl', 'aktif');

                if ($user->role === 'instruktur_industri') {
                    $u->where('instruktur_id', $user->id);
                } elseif ($user->role === 'guru_pembimbing') {
                    $u->where('guru_id', $user->id);
                }
            })
            ->orderBy('user_id')
            ->orderBy('created_at', 'asc')
            ->get();

        abort_if($catatan->isEmpty(), 404, 'Belum ada catatan yang disetujui untuk dicetak.');

        $data = [
            'tanggal_cetak' => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'catatan'       => $catatan,
        ];

        $pdf = Pdf::loadView('pdf.catatan', $data)->setPaper('a4', 'portrait');
        return $pdf->stream('Catatan_Kegiatan_PKL_Semua.pdf');
    }

    // ====== 3. OBSERVASI (FK: user_id) ======
    /** Bangun data 1 lembar observasi (identitas siswa + daftar poin). */
    private function buildObservasiLembar(Observasi $obs): array
    {
        $siswa = $obs->user;

        $rows = collect();
        foreach ($obs->items as $poin) {
            $rows->push((object) [
                'permasalahan' => $poin->permasalahan,
                'solusi'       => $poin->solusi,
                'is_approved'  => $obs->is_approved,
            ]);
        }

        return [
            'nama_siswa'       => $siswa->name ?? '-',
            'kelas'            => $siswa->kelas ?? 'Belum Diatur',
            'dunia_kerja'      => $siswa->perusahaan->nama_perusahaan ?? 'Belum Diatur',
            'nama_instruktur'  => $siswa->instruktur->name ?? 'Belum Diatur',
            'nama_guru'        => $siswa->guru->name ?? 'Belum Diatur',
            'pekerjaan_projek' => $obs->pekerjaan_projek ?? '-',
            'rows'             => $rows,
        ];
    }

    public function cetakObservasi($siswa_id = null)
    {
        $siswa = $this->resolveSiswa($siswa_id);

        $query = Observasi::with(['items', 'user.perusahaan', 'user.instruktur', 'user.guru'])
            ->where('user_id', $siswa->id);

        // Jika ada observasi_id → cetak SATU observasi saja
        if (request()->filled('observasi_id')) {
            $query->where('id', request('observasi_id'));
        }

        $observasi = $query->orderBy('hari_tanggal', 'asc')->get();

        // Tiap observasi = 1 lembar (1 halaman)
        $lembar = $observasi->map(fn ($obs) => $this->buildObservasiLembar($obs))->all();

        $pdf = Pdf::loadView('pdf.observasi', ['lembar' => $lembar])->setPaper('a4', 'portrait');
        return $pdf->stream('Lembar_Observasi_PKL_'.$siswa->name.'.pdf');
    }

    // ====== 3b. OBSERVASI - CETAK SEMUA (semua siswa bimbingan, 1 observasi 1 halaman) ======
    public function cetakObservasiSemua()
    {
        $user = auth()->user();

        if (!in_array($user->role, ['instruktur_industri', 'guru_pembimbing', 'admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $observasi = Observasi::with(['items', 'user.perusahaan', 'user.instruktur', 'user.guru'])
            ->whereHas('user', function ($u) use ($user) {
                $u->where('role', 'siswa_pkl')->where('status_pkl', 'aktif');

                if ($user->role === 'instruktur_industri') {
                    $u->where('instruktur_id', $user->id);
                } elseif ($user->role === 'guru_pembimbing') {
                    $u->where('guru_id', $user->id);
                }
            })
            ->orderBy('user_id')
            ->orderBy('hari_tanggal', 'asc')
            ->get();

        abort_if($observasi->isEmpty(), 404, 'Belum ada observasi yang bisa dicetak.');

        $lembar = $observasi->map(fn ($obs) => $this->buildObservasiLembar($obs))->all();

        $pdf = Pdf::loadView('pdf.observasi', ['lembar' => $lembar])->setPaper('a4', 'portrait');
        return $pdf->stream('Lembar_Observasi_PKL_Semua.pdf');
    }

    // ====== 4. NILAI (FK: user_id) ======
    /** Bangun paket data untuk 1 lembar nilai siswa (dipakai cetak satuan & cetak semua). */
    private function buildNilaiData(User $siswa): ?array
    {
        $nilai = Nilai::where('user_id', $siswa->id)->first();

        if (!$nilai) {
            return null;
        }

        // Rekap kehadiran otomatis dari tabel absensi
        $kehadiran = [
            'sakit' => Absensi::where('siswa_id', $siswa->id)->where('status', 'Sakit')->count(),
            'izin'  => Absensi::where('siswa_id', $siswa->id)->where('status', 'Izin')->count(),
            'alpha' => Absensi::where('siswa_id', $siswa->id)->where('status', 'Alpha')->count(),
        ];

        // Tanggal observasi terakhir (jika ada)
        $tanggalObservasi = optional(
            Observasi::where('user_id', $siswa->id)->orderBy('hari_tanggal', 'desc')->first()
        )->hari_tanggal;

        $pengaturan  = $this->getPengaturan();
        $tahunAjaran = optional(PeriodePkl::aktif())->tahun_ajaran ?? '2025/2026';

        return [
            'nama_sekolah'      => $pengaturan['nama_sekolah'] ?? 'UPTD SMKN 1 MAJENE',
            'tahun_ajaran'      => $tahunAjaran,
            'nama_siswa'        => $siswa->name,
            'kelas'             => $siswa->kelas ?? 'Belum Diatur',
            'program_keahlian'  => $siswa->jurusan ?? 'Belum Diatur',
            'dunia_kerja'       => $siswa->perusahaan->nama_perusahaan ?? 'Belum Diatur',
            'tanggal_observasi' => $tanggalObservasi,
            'nama_instruktur'   => $siswa->instruktur->name ?? 'Belum Diatur',
            'nama_guru'         => $siswa->guru->name ?? 'Belum Diatur',
            'nip_guru'          => $siswa->guru->nip ?? '-',
            'tanggal_cetak'     => Carbon::now()->locale('id')->translatedFormat('d F Y'),
            'nilai'             => $nilai,
            'kehadiran'         => $kehadiran,
        ];
    }

    public function cetakNilai($siswa_id = null)
    {
        $siswa = $this->resolveSiswa($siswa_id);
        $data  = $this->buildNilaiData($siswa);

        if (!$data) {
            return redirect()->back()->with('error', 'Cetak gagal, nilai siswa belum diinput oleh instruktur industri.');
        }

        // Cetak satuan = daftar berisi 1 siswa
        $pdf = Pdf::loadView('pdf.nilai', ['lembar' => [$data]])->setPaper('a4', 'portrait');
        return $pdf->stream('Daftar_Nilai_PKL_'.$siswa->name.'.pdf');
    }

    // ====== 4b. NILAI - CETAK SEMUA (template per-siswa, 1 siswa 1 halaman) ======
    public function cetakNilaiSemua()
    {
        $user = auth()->user();

        $query = User::where('role', 'siswa_pkl');

        if ($user->role === 'instruktur_industri') {
            $query->where('instruktur_id', $user->id)->where('status_pkl', 'aktif');
        } elseif ($user->role === 'guru_pembimbing') {
            $query->where('guru_id', $user->id)->where('status_pkl', 'aktif');
        } elseif ($user->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $siswas = $query->orderBy('name')->get();

        $lembar = [];
        foreach ($siswas as $siswa) {
            $data = $this->buildNilaiData($siswa);
            if ($data && $data['nilai']->rata_rata !== null) {
                $lembar[] = $data;
            }
        }

        abort_if(empty($lembar), 404, 'Belum ada nilai siswa yang bisa dicetak.');

        // Cetak semua = daftar berisi banyak siswa, tetap pakai template pdf.nilai
        $pdf = Pdf::loadView('pdf.nilai', ['lembar' => $lembar])->setPaper('a4', 'portrait');
        return $pdf->stream('Daftar_Nilai_PKL_Semua.pdf');
    }


    // ====== 5. ABSENSI (FK: siswa_id) ======
/** Hitung rekap kehadiran dari koleksi absensi. */
private function rekapAbsensi($absensis): array
{
    return [
        'hadir' => $absensis->where('status', 'Hadir')->count(),
        'izin'  => $absensis->where('status', 'Izin')->count(),
        'sakit' => $absensis->where('status', 'Sakit')->count(),
        'alpha' => $absensis->where('status', 'Alpha')->count(),
    ];
}

/** Bangun data lembar absensi: 1 siswa = 1 lembar, semua absensi jadi baris bernomor urut. */
private function buildAbsensiLembar(User $siswa, ?string $bulan = null): array
{
    $query = Absensi::where('siswa_id', $siswa->id);

    // $bulan format: YYYY-MM
    if ($bulan) {
        $query->whereYear('tanggal', substr($bulan, 0, 4))
              ->whereMonth('tanggal', substr($bulan, 5, 2));
    }

    $absensis = $query->orderBy('tanggal', 'asc')->get();

    return [
        'siswa'    => $siswa,
        'absensis' => $absensis,
        'rekap'    => $this->rekapAbsensi($absensis),
    ];
}

public function cetakAbsensi($siswa_id = null)
{
    $siswa = $this->resolveSiswa($siswa_id);
    $siswa->loadMissing(['perusahaan', 'instruktur', 'guru']);

    $query = Absensi::where('siswa_id', $siswa->id);

    // Cetak SATU baris absensi (tombol PDF per baris)
    if (request()->filled('absensi_id')) {
        $query->where('id', request('absensi_id'));
    }
    // (opsional) semua absensi pada satu bulan (format YYYY-MM)
    elseif (request()->filled('bulan')) {
        $query->whereYear('tanggal', substr(request('bulan'), 0, 4))
              ->whereMonth('tanggal', substr(request('bulan'), 5, 2));
    }
    // Tanpa filter -> semua absensi siswa (tombol "Cetak Semua PDF")

    $absensis = $query->orderBy('tanggal', 'asc')->get();

    abort_if(
        (request()->filled('absensi_id') || request()->filled('bulan')) && $absensis->isEmpty(),
        404,
        'Absensi tidak ditemukan untuk dicetak.'
    );

    // Semua absensi dalam SATU lembar/tabel, bernomor urut.
    $lembar = $absensis->isEmpty() ? [] : [[
        'siswa'    => $siswa,
        'absensis' => $absensis,
        'rekap'    => $this->rekapAbsensi($absensis),
    ]];

    $pengaturan = $this->getPengaturan();

    $pdf = Pdf::loadView('pdf.absensi', compact('lembar', 'pengaturan'))
              ->setPaper('a4', 'portrait');

    $suffix = request('absensi_id')
        ? '_'.request('absensi_id')
        : (request('bulan') ? '_'.request('bulan') : '');

    return $pdf->stream('Absensi_PKL_'.$siswa->name.$suffix.'.pdf');
}

// ====== 5b. ABSENSI - CETAK SEMUA (semua siswa bimbingan, 1 siswa 1 halaman) ======
public function cetakAbsensiSemua()
{
    $user = auth()->user();

    if (!in_array($user->role, ['instruktur_industri', 'guru_pembimbing', 'admin'])) {
        abort(403, 'Akses ditolak.');
    }

    // Jika ada filter bulan (YYYY-MM) → batasi ke bulan itu, jika tidak → semua data.
    $bulan = request()->filled('bulan') ? request('bulan') : null;

    $query = User::where('role', 'siswa_pkl')
        ->where('status_pkl', 'aktif')
        ->with(['perusahaan', 'instruktur', 'guru']);

    if ($user->role === 'instruktur_industri') {
        $query->where('instruktur_id', $user->id);
    } elseif ($user->role === 'guru_pembimbing') {
        $query->where('guru_id', $user->id);
    }

    $siswas = $query->orderBy('name')->get();

    // Hanya sertakan siswa yang punya absensi (1 siswa = 1 halaman)
    $lembar = [];
    foreach ($siswas as $siswa) {
        $data = $this->buildAbsensiLembar($siswa, $bulan);
        if ($data['absensis']->isNotEmpty()) {
            $lembar[] = $data;
        }
    }

    abort_if(empty($lembar), 404, 'Tidak ada data absensi untuk dicetak.');

    $pengaturan = $this->getPengaturan();

    $pdf = Pdf::loadView('pdf.absensi', compact('lembar', 'pengaturan'))
              ->setPaper('a4', 'portrait');

    return $pdf->stream('Absensi_PKL_Semua'.($bulan ? '_'.$bulan : '').'.pdf');
}

   /**
     * FUNGSI BARU: Cetak Format Penilaian Khusus Guru Pembimbing
     */
    public function cetakNilaiGuruSatuan($siswaId)
    {
        // Pengecekan role dan data siswa dengan helper yang sudah ada
        $siswa = $this->resolveSiswa($siswaId);

        // Mengambil data absensi berdasarkan siswa_id
        $absensi = Absensi::where('siswa_id', $siswa->id)->get(); 
        
        // Menghitung status absensi (case-insensitive & handle berbagai enum)
        $sakit = $absensi->where('status', 'Sakit')->count() + $absensi->where('status', 'sakit')->count();
        $ijin  = $absensi->where('status', 'Izin')->count() + $absensi->where('status', 'izin')->count() + $absensi->where('status', 'Ijin')->count();
        $alpa  = $absensi->where('status', 'Alpa')->count() + $absensi->where('status', 'alpa')->count() + $absensi->where('status', 'Tanpa Keterangan')->count();

        // Mengambil data Periode PKL
        $periodePkl = PeriodePkl::where('id', $siswa->periode_pkl_id)->first();

        // --- PROSES LOGIKA VARIABEL UNTUK BLADE DI SINI ---
        // Menggunakan fallback jika relasi di model atau pencarian query yang aktif
        // PERBAIKAN 1: Tambahkan fallback ke PeriodePkl::aktif() jika relasi kosong
        $periode = $siswa->periodePkl ?? $siswa->periode_pkl ?? $periodePkl ?? (method_exists(PeriodePkl::class, 'aktif') ? PeriodePkl::aktif() : null);
        
        $mulaiPkl = ($periode && $periode->tanggal_mulai)
            ? \Carbon\Carbon::parse($periode->tanggal_mulai)
            : \Carbon\Carbon::now();
            
        // Ambil tahun ajaran dari periode jika ada
        $tahunAjaran = $periode->tahun_ajaran ?? ($mulaiPkl->format('Y') . '/' . $mulaiPkl->copy()->addYear()->format('Y'));

        // Menentukan Nama Perusahaan / Tempat PKL
        // PERBAIKAN 2: Prioritaskan mengambil dari relasi $siswa->perusahaan (sama seperti fitur cetakNilai reguler)
        $namaPerusahaan = $siswa->perusahaan->nama_perusahaan ?? $periode->perusahaan->nama_perusahaan ?? $periode->perusahaan->nama ?? '-';
        
        // Memformat Tanggal Mulai dan Selesai PKL
        $tanggalMulaiFormat = ($periode && $periode->tanggal_mulai) 
            ? \Carbon\Carbon::parse($periode->tanggal_mulai)->translatedFormat('d F Y') 
            : '-';
            
        $tanggalSelesaiFormat = ($periode && $periode->tanggal_selesai) 
            ? \Carbon\Carbon::parse($periode->tanggal_selesai)->translatedFormat('d F Y') 
            : '-';
        // --------------------------------------------------

        // Menyiapkan data array untuk dilempar ke View PDF
        $data = [
            'siswa'                => $siswa,
            'nilai'                => Nilai::where('user_id', $siswa->id)->first(),
            'periodePkl'           => $periodePkl,
            'sakit'                => $sakit,
            'ijin'                 => $ijin,
            'alpa'                 => $alpa,
            
            // Variabel tambahan yang dikirim langsung ke Blade
            'tahunAjaran'          => $tahunAjaran,
            'namaPerusahaan'       => $namaPerusahaan,
            'tanggalMulaiFormat'   => $tanggalMulaiFormat,
            'tanggalSelesaiFormat' => $tanggalSelesaiFormat,
        ];

        // Memuat view PDF dengan data yang sudah lengkap
        $pdf = Pdf::loadView('pdf.nilai-guru', $data)->setPaper('a4', 'portrait');
        
        return $pdf->stream('Nilai_PKL_Guru_'.$siswa->name.'.pdf');
    }
}
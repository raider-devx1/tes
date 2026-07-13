<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    // Bobot nilai akhir (silakan sesuaikan)
    private const BOBOT_INSTRUKTUR = 0.50; // 1–5 dikonversi ke 0–100
    private const BOBOT_GURU       = 0.20; // 0–100
    private const BOBOT_LAPORAN    = 0.30; // 0–100

    /** Hitung nilai akhir (0–100). Null jika komponen belum lengkap. */
    private function hitungNilaiAkhir(Nilai $n): ?float
    {
        if (is_null($n->rata_rata) || is_null($n->nilai_guru) || is_null($n->nilai_laporan)) {
            return null;
        }

        $instruktur100 = ($n->rata_rata / 5) * 100;

        return round(
            ($instruktur100 * self::BOBOT_INSTRUKTUR)
            + ($n->nilai_guru * self::BOBOT_GURU)
            + ($n->nilai_laporan * self::BOBOT_LAPORAN),
            2
        );
    }

    /* ===================== INSTRUKTUR INDUSTRI ===================== */
    public function indexInstruktur(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $status = $request->get('status');

        $rekapQuery = User::where('role', 'siswa_pkl')
            ->where('instruktur_id', Auth::id())
            ->where('status_pkl', 'aktif');

        $totalSiswa   = (clone $rekapQuery)->count();
        $sudahDinilai = (clone $rekapQuery)
            ->whereHas('nilai', fn ($n) => $n->whereNotNull('rata_rata'))
            ->count();

        $rekap = [
            'total' => $totalSiswa,
            'sudah' => $sudahDinilai,
            'belum' => $totalSiswa - $sudahDinilai,
        ];

        $siswa = User::where('role', 'siswa_pkl')
            ->where('instruktur_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->with('nilai')
            ->when($q, fn ($query) => $query->where(fn ($u) =>
                $u->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%")))
            ->when($status === 'sudah', fn ($query) =>
                $query->whereHas('nilai', fn ($n) => $n->whereNotNull('rata_rata')))
            ->when($status === 'belum', fn ($query) =>
                $query->where(fn ($u) =>
                    $u->whereDoesntHave('nilai')
                      ->orWhereHas('nilai', fn ($n) => $n->whereNull('rata_rata'))))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('instruktur.nilai.index', compact('siswa', 'q', 'status', 'rekap'));
    }

    public function createInstruktur(Request $request)
    {
        $siswaId = $request->query('siswa_id');

        $siswa = User::where('role', 'siswa_pkl')
            ->where('instruktur_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->findOrFail($siswaId);

        return view('instruktur.nilai.create', compact('siswa'));
    }

    public function storeInstruktur(Request $request)
    {
        $request->validate([
            'user_id'                 => 'required|exists:users,id',
            'soft_skill'              => 'required|integer|between:1,5',
            'hard_skill'              => 'required|integer|between:1,5',
            'pengembangan_hard_skill' => 'required|integer|between:1,5',
            'kewirausahaan'           => 'required|integer|between:1,5',
            'catatan_rekomendasi'     => 'nullable|string',
        ]);

        $siswa = User::where('role', 'siswa_pkl')
            ->where('instruktur_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->findOrFail($request->user_id);

        $rataRata = ($request->soft_skill + $request->hard_skill
            + $request->pengembangan_hard_skill + $request->kewirausahaan) / 4;

        $nilai = Nilai::firstOrNew(['user_id' => $siswa->id]);
        $nilai->instruktur_id           = Auth::id();
        $nilai->soft_skill              = $request->soft_skill;
        $nilai->hard_skill              = $request->hard_skill;
        $nilai->pengembangan_hard_skill = $request->pengembangan_hard_skill;
        $nilai->kewirausahaan           = $request->kewirausahaan;
        $nilai->rata_rata               = $rataRata;
        $nilai->catatan_rekomendasi     = $request->catatan_rekomendasi;
        $nilai->nilai_akhir             = $this->hitungNilaiAkhir($nilai);
        $nilai->save();

        return redirect()->route('instruktur.nilai.index')
            ->with('success', 'Lembar evaluasi penilaian instruktur sukses disimpan.');
    }

    /* ===================== SISWA PKL ===================== */
    public function indexSiswa()
    {
        $nilai = Nilai::where('user_id', Auth::id())
            ->with(['instruktur', 'guru'])
            ->first();

        return view('siswa.nilai.index', compact('nilai'));
    }

    /* ===================== GURU PEMBIMBING ===================== */
    public function indexGuru(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $status = $request->get('status');

        $rekapQuery = User::where('role', 'siswa_pkl')
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif');

        $totalSiswa = (clone $rekapQuery)->count();
        
        // Cek jumlah yang dinilai Instruktur
        $sudahDinilaiInstruktur = (clone $rekapQuery)
            ->whereHas('nilai', fn ($n) => $n->whereNotNull('rata_rata'))
            ->count();

        // Cek jumlah yang SUDAH dinilai GURU berdasarkan keberadaan skor_soft_skill
        $sudahDinilaiGuru = (clone $rekapQuery)
            ->whereHas('nilai', fn ($n) => $n->whereNotNull('skor_soft_skill'))
            ->count();

        $rekap = [
            'total' => $totalSiswa,
            'sudah_dinilai_instruktur' => $sudahDinilaiInstruktur,
            'sudah_dinilai_guru' => $sudahDinilaiGuru,
        ];

        $siswa = User::where('role', 'siswa_pkl')
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->with('nilai')
            ->when($q, fn ($query) => $query->where(fn ($u) =>
                $u->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%")))
            ->when($status === 'sudah', fn ($query) =>
                $query->whereHas('nilai', fn ($n) => $n->whereNotNull('skor_soft_skill')))
            ->when($status === 'belum', fn ($query) =>
                $query->where(fn ($u) =>
                    $u->whereDoesntHave('nilai')
                      ->orWhereHas('nilai', fn ($n) => $n->whereNull('skor_soft_skill'))))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('guru.nilai.index', compact('siswa', 'q', 'status', 'rekap'));
    }

    public function storeGuru(Request $request)
    {
        // Validasi untuk 6 kriteria form guru
        $request->validate([
            'user_id'                 => 'required|exists:users,id',
            'skor_soft_skill'         => 'required|numeric|between:0,100',
            'deskripsi_soft_skill'    => 'required|string',
            'skor_hard_skill'         => 'required|numeric|between:0,100',
            'deskripsi_hard_skill'    => 'required|string',
            'skor_pengembangan'       => 'required|numeric|between:0,100',
            'deskripsi_pengembangan'  => 'required|string',
            'skor_kewirausahaan'      => 'required|numeric|between:0,100',
            'deskripsi_kewirausahaan' => 'required|string',
            'skor_laporan'            => 'required|numeric|between:0,100',
            'deskripsi_laporan'       => 'required|string',
            'skor_presentasi'         => 'required|numeric|between:0,100',
            'deskripsi_presentasi'    => 'required|string',
            'catatan_guru'            => 'nullable|string',
        ]);

        $siswa = User::where('id', $request->user_id)
            ->where('role', 'siswa_pkl')
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->firstOrFail();

        $nilai = Nilai::firstOrNew(['user_id' => $siswa->id]);
        $nilai->guru_id = Auth::id();
        
        // Simpan 6 kriteria dan deskripsinya
        $nilai->skor_soft_skill         = $request->skor_soft_skill;
        $nilai->deskripsi_soft_skill    = $request->deskripsi_soft_skill;
        $nilai->skor_hard_skill         = $request->skor_hard_skill;
        $nilai->deskripsi_hard_skill    = $request->deskripsi_hard_skill;
        $nilai->skor_pengembangan       = $request->skor_pengembangan;
        $nilai->deskripsi_pengembangan  = $request->deskripsi_pengembangan;
        $nilai->skor_kewirausahaan      = $request->skor_kewirausahaan;
        $nilai->deskripsi_kewirausahaan = $request->deskripsi_kewirausahaan;
        $nilai->skor_laporan            = $request->skor_laporan;
        $nilai->deskripsi_laporan       = $request->deskripsi_laporan;
        $nilai->skor_presentasi         = $request->skor_presentasi;
        $nilai->deskripsi_presentasi    = $request->deskripsi_presentasi;
        $nilai->catatan_guru            = $request->catatan_guru;

        // Backup perhitungan nilai (untuk kompatibilitas fungsi hitungNilaiAkhir lama)
        $rataGuru = ($request->skor_soft_skill + $request->skor_hard_skill + $request->skor_pengembangan + $request->skor_kewirausahaan + $request->skor_laporan + $request->skor_presentasi) / 6;
        
        $nilai->nilai_guru    = $rataGuru;
        $nilai->nilai_laporan = $request->skor_laporan; 
        
        $nilai->nilai_akhir   = $this->hitungNilaiAkhir($nilai);
        $nilai->save();

        return redirect()->route('guru.nilai.index')
            ->with('success', 'Penilaian PKL berhasil disimpan.');
    }
}
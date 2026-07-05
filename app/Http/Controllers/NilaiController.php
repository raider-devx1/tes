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
    $status = $request->get('status'); // 'sudah' | 'belum' (status penilaian, bukan status_pkl)

    // Rekap seluruh siswa bimbingan aktif (tidak terpengaruh filter/pagination)
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

    // Hanya siswa bimbingan instruktur ini yang MASIH aktif yang boleh dinilai
    $siswa = User::where('role', 'siswa_pkl')
        ->where('instruktur_id', Auth::id())  // ⬅️ pastikan memang bimbingannya
        ->where('status_pkl', 'aktif')        // ⬅️ tolak siswa yang sudah "selesai" / "belum"
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

    // Pastikan siswa memang bimbingan instruktur ini & masih aktif PKL
    $siswa = User::where('role', 'siswa_pkl')
        ->where('instruktur_id', Auth::id())  // ⬅️ cegah menilai siswa instruktur lain
        ->where('status_pkl', 'aktif')        // ⬅️ cegah menilai siswa yang sudah selesai
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
    $nilai->nilai_akhir             = $this->hitungNilaiAkhir($nilai); // hitung ulang
    $nilai->save();

    return redirect()->route('instruktur.nilai.index')
        ->with('success', 'Lembar evaluasi penilaian siswa sukses disimpan.');
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
    $status = $request->get('status'); // 'sudah' | 'belum' (status penilaian)

    // Rekap seluruh siswa bimbingan guru ini (tidak terpengaruh filter/pagination)
    $rekapQuery = User::where('role', 'siswa_pkl')
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif');

    $totalSiswa   = (clone $rekapQuery)->count();
    $sudahDinilai = (clone $rekapQuery)
        ->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir'))
        ->count();

    $rekap = [
        'total' => $totalSiswa,
        'sudah' => $sudahDinilai,
        'belum' => $totalSiswa - $sudahDinilai,
    ];

    $siswa = User::where('role', 'siswa_pkl')
        ->where('guru_id', Auth::id())
        ->where('status_pkl', 'aktif')
        ->with('nilai')
        ->when($q, fn ($query) => $query->where(fn ($u) =>
            $u->where('name', 'like', "%{$q}%")
              ->orWhere('nisn', 'like', "%{$q}%")))
        ->when($status === 'sudah', fn ($query) =>
            $query->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir')))
        ->when($status === 'belum', fn ($query) =>
            $query->where(fn ($u) =>
                $u->whereDoesntHave('nilai')
                  ->orWhereHas('nilai', fn ($n) => $n->whereNull('nilai_akhir'))))
        ->orderBy('name')
        ->paginate(15)
        ->withQueryString();

    return view('guru.nilai.index', compact('siswa', 'q', 'status', 'rekap'));
}

    public function storeGuru(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'nilai_guru'    => 'required|numeric|between:0,100',
            'nilai_laporan' => 'required|numeric|between:0,100',
            'catatan_guru'  => 'nullable|string',
        ]);

       // Pastikan siswa benar-benar bimbingan guru ini & masih aktif PKL
        $siswa = User::where('id', $request->user_id)
            ->where('role', 'siswa_pkl')
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->firstOrFail();

        $nilai = Nilai::firstOrNew(['user_id' => $siswa->id]);
        $nilai->guru_id       = Auth::id();
        $nilai->nilai_guru    = $request->nilai_guru;
        $nilai->nilai_laporan = $request->nilai_laporan;
        $nilai->catatan_guru  = $request->catatan_guru;
        $nilai->nilai_akhir   = $this->hitungNilaiAkhir($nilai); // recompute
        $nilai->save();

        return redirect()->route('guru.nilai.index')
            ->with('success', 'Nilai guru & laporan berhasil disimpan.');
    }
}
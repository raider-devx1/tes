<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NilaiController extends Controller
{
    /**
     * Hitung rata-rata akhir (0-100) dari 6 komponen penilaian guru.
     * Mengembalikan null bila belum semua komponen terisi.
     */
    private function hitungRataRata(Nilai $nilai): ?float
    {
        $daftarSkor = [
            $nilai->skor_soft_skill,
            $nilai->skor_hard_skill,
            $nilai->skor_pengembangan,
            $nilai->skor_kewirausahaan,
            $nilai->skor_laporan,
            $nilai->skor_presentasi,
        ];

        if (in_array(null, $daftarSkor, true)) {
            return null;
        }

        return round(array_sum($daftarSkor) / count($daftarSkor), 2);
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

        // Sudah dinilai LENGKAP = 6 komponen terisi semua
        $sudahDinilai = (clone $rekapQuery)
            ->whereHas('nilai', fn ($n) => $n
                ->whereNotNull('skor_soft_skill')
                ->whereNotNull('skor_hard_skill')
                ->whereNotNull('skor_pengembangan')
                ->whereNotNull('skor_kewirausahaan')
                ->whereNotNull('skor_laporan')
                ->whereNotNull('skor_presentasi'))
            ->count();

        $rekap = [
            'total'         => $totalSiswa,
            'sudah_dinilai' => $sudahDinilai,
            'belum_dinilai' => $totalSiswa - $sudahDinilai,
        ];

        $siswa = User::where('role', 'siswa_pkl')
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->with('nilai')
            ->when($q, fn ($query) => $query->where(fn ($u) =>
                $u->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%")))
            ->when($status === 'sudah', fn ($query) =>
                $query->whereHas('nilai', fn ($n) => $n->whereNotNull('skor_presentasi')))
            ->when($status === 'belum', fn ($query) =>
                $query->where(fn ($u) =>
                    $u->whereDoesntHave('nilai')
                      ->orWhereHas('nilai', fn ($n) => $n->whereNull('skor_presentasi'))))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('guru.nilai.index', compact('siswa', 'q', 'status', 'rekap'));
    }

    public function storeGuru(Request $request)
    {
        $siswa = User::where('id', $request->user_id)
            ->where('role', 'siswa_pkl')
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->firstOrFail();

        $nilai = Nilai::firstOrNew(['user_id' => $siswa->id]);

        // Foto wajib hanya jika belum pernah diunggah sebelumnya
        $aturanFoto = $nilai->foto_lembar_instruktur ? 'nullable' : 'required';

        $request->validate([
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
            'foto_lembar_instruktur'  => $aturanFoto . '|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'foto_lembar_instruktur.required' => 'Foto lembar penilaian instruktur wajib diunggah.',
            'foto_lembar_instruktur.image'    => 'File harus berupa gambar (JPG/JPEG/PNG).',
            'foto_lembar_instruktur.mimes'    => 'Format foto harus JPG, JPEG, atau PNG.',
            'foto_lembar_instruktur.max'      => 'Ukuran foto maksimal 2 MB.',
        ]);

        $nilai->guru_id = Auth::id();

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

        // Simpan / ganti foto lembar penilaian instruktur
        if ($request->hasFile('foto_lembar_instruktur')) {
            if ($nilai->foto_lembar_instruktur && Storage::disk('public')->exists($nilai->foto_lembar_instruktur)) {
                Storage::disk('public')->delete($nilai->foto_lembar_instruktur);
            }
            $nilai->foto_lembar_instruktur = $request->file('foto_lembar_instruktur')
                ->store('nilai/lembar-instruktur', 'public');
        }

        // Nilai akhir = rata-rata 6 komponen (0-100)
        $nilai->nilai_akhir   = $this->hitungRataRata($nilai);
        $nilai->nilai_guru    = $nilai->nilai_akhir;    // kompatibilitas kolom lama
        $nilai->nilai_laporan = $request->skor_laporan; // kompatibilitas kolom lama

        $nilai->save();

        return redirect()->route('guru.nilai.index')
            ->with('success', 'Penilaian PKL berhasil disimpan.');
    }
}
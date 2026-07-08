<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\Observasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluasiController extends Controller
{
    // Bobot nilai akhir (samakan dengan NilaiController)
    private const BOBOT_INSTRUKTUR = 0.50; // rata-rata 1–5 dikonversi ke 0–100
    private const BOBOT_GURU       = 0.20; // 0–100
    private const BOBOT_LAPORAN    = 0.30; // 0–100

    /** Opsi filter kelas & jurusan dari seluruh siswa PKL. */
    private function opsiFilter(): array
    {
        $kelasList = User::where('role', 'siswa_pkl')
            ->whereNotNull('kelas')->where('kelas', '!=', '')
            ->distinct()->orderBy('kelas')->pluck('kelas');

        $jurusanList = User::where('role', 'siswa_pkl')
            ->whereNotNull('jurusan')->where('jurusan', '!=', '')
            ->distinct()->orderBy('jurusan')->pluck('jurusan');

        return [$kelasList, $jurusanList];
    }

    /** Daftar siswa PKL untuk pencocokan NISN pada modal tambah/edit. */
    private function siswaList()
    {
        return User::where('role', 'siswa_pkl')
            ->orderBy('name')
            ->get(['id', 'name', 'nisn']);
    }

    /** Hitung nilai akhir (0–100). Null bila komponen belum lengkap. */
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

    /*
    |--------------------------------------------------------------------------
    | OBSERVASI — Evaluasi Lembar Observasi Guru
    |--------------------------------------------------------------------------
    */
    public function observasi(Request $request)
    {
        [$kelasList, $jurusanList] = $this->opsiFilter();

        $q       = trim($request->get('q', ''));
        $kelas   = $request->get('kelas');
        $jurusan = $request->get('jurusan');
        $status  = $request->get('status'); // '1' = disetujui, '0' = menunggu

        $rekap = [
            'total'     => Observasi::count(),
            'disetujui' => Observasi::where('is_approved', true)->count(),
            'menunggu'  => Observasi::where('is_approved', false)->count(),
        ];

        $jumlahGuru = User::where('role', 'guru_pembimbing')->count();

        $observasi = Observasi::with(['user', 'guru', 'items'])
            ->when($q !== '', fn ($query) => $query->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('kelas', $kelas)))
            ->when($jurusan, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('jurusan', $jurusan)))
            ->when($status !== null && $status !== '', fn ($query) =>
                $query->where('is_approved', $status === '1'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $siswaList = $this->siswaList();

        return view('admin.evaluasi.observasi', compact(
            'observasi', 'rekap', 'jumlahGuru', 'kelasList', 'jurusanList', 'siswaList'
        ));
    }

    private function validasiObservasi(Request $request): array
    {
        return $request->validate([
            'user_id'              => 'required|exists:users,id',
            'hari_tanggal'         => 'required|date',
            'pekerjaan_projek'     => 'nullable|string|max:255',
            'is_approved'          => 'nullable|boolean',
            'items'                => 'required|array|min:1',
            'items.*.permasalahan' => 'required|string',
            'items.*.solusi'       => 'required|string',
        ], [
            'items.required'                => 'Minimal harus ada 1 poin permasalahan & solusi.',
            'items.*.permasalahan.required' => 'Permasalahan pada setiap poin wajib diisi.',
            'items.*.solusi.required'       => 'Solusi pada setiap poin wajib diisi.',
        ]);
    }

    public function storeObservasi(Request $request)
    {
        $validated = $this->validasiObservasi($request);
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($validated['user_id']);

        DB::transaction(function () use ($validated, $siswa, $request) {
            $observasi = Observasi::create([
                'user_id'          => $siswa->id,
                'guru_id'          => $siswa->guru_id ?? Auth::id(),
                'hari_tanggal'     => $validated['hari_tanggal'],
                'pekerjaan_projek' => $validated['pekerjaan_projek'] ?? null,
                'is_approved'      => $request->boolean('is_approved'),
            ]);

            foreach ($validated['items'] as $item) {
                $observasi->items()->create([
                    'permasalahan' => $item['permasalahan'],
                    'solusi'       => $item['solusi'],
                ]);
            }
        });

        return redirect()->route('admin.evaluasi.observasi')
            ->with('success', 'Data observasi berhasil ditambahkan.');
    }

    public function updateObservasi(Request $request, Observasi $observasi)
    {
        $validated = $this->validasiObservasi($request);
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($validated['user_id']);

        DB::transaction(function () use ($observasi, $validated, $siswa, $request) {
            $observasi->update([
                'user_id'          => $siswa->id,
                'guru_id'          => $siswa->guru_id ?? $observasi->guru_id,
                'hari_tanggal'     => $validated['hari_tanggal'],
                'pekerjaan_projek' => $validated['pekerjaan_projek'] ?? null,
                'is_approved'      => $request->boolean('is_approved'),
            ]);

            // Ganti seluruh poin dengan data terbaru dari form
            $observasi->items()->delete();
            foreach ($validated['items'] as $item) {
                $observasi->items()->create([
                    'permasalahan' => $item['permasalahan'],
                    'solusi'       => $item['solusi'],
                ]);
            }
        });

        return redirect()->route('admin.evaluasi.observasi')
            ->with('success', 'Data observasi berhasil diperbarui.');
    }

    public function destroyObservasi(Observasi $observasi)
    {
        $observasi->items()->delete();
        $observasi->delete();

        return redirect()->route('admin.evaluasi.observasi')
            ->with('success', 'Data observasi berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | PENILAIAN — Rekap & Penilaian Siswa PKL
    |--------------------------------------------------------------------------
    */
    public function penilaian(Request $request)
    {
        [$kelasList, $jurusanList] = $this->opsiFilter();

        $q       = trim($request->get('q', ''));
        $kelas   = $request->get('kelas');
        $jurusan = $request->get('jurusan');
        $status  = $request->get('status'); // 'sudah' | 'belum'

        $total = User::where('role', 'siswa_pkl')->count();
        $sudah = User::where('role', 'siswa_pkl')
            ->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir'))->count();

        $rekap = ['total' => $total, 'sudah' => $sudah, 'belum' => $total - $sudah];

        $siswa = User::where('role', 'siswa_pkl')
            ->with(['nilai', 'guru'])
            ->when($q !== '', fn ($query) => $query->where(fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas, fn ($query) => $query->where('kelas', $kelas))
            ->when($jurusan, fn ($query) => $query->where('jurusan', $jurusan))
            ->when($status === 'sudah', fn ($query) =>
                $query->whereHas('nilai', fn ($n) => $n->whereNotNull('nilai_akhir')))
            ->when($status === 'belum', fn ($query) =>
                $query->where(fn ($u) =>
                    $u->whereDoesntHave('nilai')
                      ->orWhereHas('nilai', fn ($n) => $n->whereNull('nilai_akhir'))))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $siswaList = $this->siswaList();

        return view('admin.evaluasi.penilaian', compact(
            'siswa', 'rekap', 'kelasList', 'jurusanList', 'siswaList'
        ));
    }

    private function validasiPenilaian(Request $request): void
    {
        $request->validate([
            'user_id'                 => 'required|exists:users,id',
            'soft_skill'              => 'nullable|integer|between:1,5',
            'hard_skill'              => 'nullable|integer|between:1,5',
            'pengembangan_hard_skill' => 'nullable|integer|between:1,5',
            'kewirausahaan'           => 'nullable|integer|between:1,5',
            'catatan_rekomendasi'     => 'nullable|string',
            'nilai_guru'              => 'nullable|numeric|between:0,100',
            'nilai_laporan'           => 'nullable|numeric|between:0,100',
            'catatan_guru'            => 'nullable|string',
        ]);
    }

    /** Isi seluruh komponen nilai + hitung rata-rata & nilai akhir. */
    private function isiNilai(Nilai $nilai, Request $request, User $siswa): void
    {
        $nilai->user_id                 = $siswa->id;
        $nilai->soft_skill              = $request->soft_skill;
        $nilai->hard_skill              = $request->hard_skill;
        $nilai->pengembangan_hard_skill = $request->pengembangan_hard_skill;
        $nilai->kewirausahaan           = $request->kewirausahaan;

        // Rata-rata instruktur hanya dihitung bila 4 komponen terisi penuh
        $komponen = [
            $request->soft_skill,
            $request->hard_skill,
            $request->pengembangan_hard_skill,
            $request->kewirausahaan,
        ];
        $terisiPenuh = count(array_filter($komponen, fn ($v) => $v !== null && $v !== '')) === 4;
        $nilai->rata_rata = $terisiPenuh ? array_sum($komponen) / 4 : null;

        $nilai->catatan_rekomendasi = $request->catatan_rekomendasi;
        $nilai->nilai_guru          = $request->nilai_guru;
        $nilai->nilai_laporan       = $request->nilai_laporan;
        $nilai->catatan_guru        = $request->catatan_guru;

        // Lengkapi penilai dari data siswa bila belum ada (tanpa menimpa yang sudah tercatat)
        $nilai->instruktur_id = $nilai->instruktur_id ?? $siswa->instruktur_id;
        $nilai->guru_id       = $nilai->guru_id ?? $siswa->guru_id;

        $nilai->nilai_akhir = $this->hitungNilaiAkhir($nilai);
    }

    public function storePenilaian(Request $request)
    {
        $this->validasiPenilaian($request);
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($request->user_id);

        $nilai = Nilai::firstOrNew(['user_id' => $siswa->id]);
        $this->isiNilai($nilai, $request, $siswa);
        $nilai->save();

        return redirect()->route('admin.evaluasi.penilaian')
            ->with('success', 'Penilaian siswa berhasil disimpan.');
    }

    public function updatePenilaian(Request $request, Nilai $nilai)
    {
        $this->validasiPenilaian($request);
        $siswa = User::where('role', 'siswa_pkl')->findOrFail($request->user_id);

        $this->isiNilai($nilai, $request, $siswa);
        $nilai->save();

        return redirect()->route('admin.evaluasi.penilaian')
            ->with('success', 'Penilaian siswa berhasil diperbarui.');
    }

    public function destroyPenilaian(Nilai $nilai)
    {
        $nilai->delete();

        return redirect()->route('admin.evaluasi.penilaian')
            ->with('success', 'Data penilaian berhasil dihapus.');
    }
}
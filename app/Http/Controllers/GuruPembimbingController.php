<?php

namespace App\Http\Controllers;

use App\Exports\GuruExport;
use App\Exports\GuruTemplateExport;
use App\Imports\GuruImport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class GuruPembimbingController extends Controller
{
    /** Validasi akun guru (dipakai store & update). */
    private function validateData(Request $request, ?User $guru = null): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            // NIP = identitas login guru: wajib & unik (email tidak dipakai lagi)
            'nip'      => ['required', 'string', 'max:30', Rule::unique('users', 'nip')->ignore($guru?->id)],
            'no_hp'    => ['nullable', 'string', 'max:20'],
            'password' => [$guru ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Buang spasi, tab, baris baru, dan spasi tak-terputus (U+00A0).
     *
     * Wajib dipakai pada NIP dan PASSWORD. Laravel sengaja TIDAK memangkas
     * spasi pada field password (lihat $except pada middleware TrimStrings),
     * sehingga spasi yang ikut terbawa saat admin menyalin-tempel NIP akan
     * tersimpan diam-diam dan membuat guru tidak pernah bisa login walau
     * merasa password-nya sudah benar.
     */
    private function rapikan(?string $nilai): string
    {
        return preg_replace('/[\s\x{00A0}]+/u', '', (string) $nilai);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        // ---- Kartu informasi ----
        $totalGuru = User::where('role', 'guru_pembimbing')->count();

        $guruIdsDenganBimbingan = User::siswaBerjalan()
            ->whereNotNull('guru_id')
            ->distinct()
            ->pluck('guru_id');

        $guruAdaBimbingan   = $guruIdsDenganBimbingan->count();
        $totalSiswaDibimbing = User::siswaBerjalan()->whereNotNull('guru_id')->count();

        $totalWakasek = User::where('role', 'guru_pembimbing')->where('is_wakasek', true)->count();

        $rekap = [
            'total'           => $totalGuru,
            'ada_bimbingan'   => $guruAdaBimbingan,
            'tanpa_bimbingan' => max($totalGuru - $guruAdaBimbingan, 0),
            'siswa_dibimbing' => $totalSiswaDibimbing,
            'wakasek'         => $totalWakasek,
        ];

        $guru = User::query()
            ->where('role', 'guru_pembimbing')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('nip', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.guru.index', compact('guru', 'q', 'rekap'));
    }

    public function create()
    {
        return view('admin.guru.create', ['guru' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['role']     = 'guru_pembimbing';
        $data['nip']      = $this->rapikan($data['nip']);
        $data['password'] = Hash::make($this->rapikan($data['password']));

        User::create($data);

        return redirect()->route('admin.guru.index')
            ->with('success', 'Akun guru pembimbing berhasil ditambahkan.');
    }

    public function edit(User $guru)
    {
        return view('admin.guru.edit', compact('guru'));
    }

    public function update(Request $request, User $guru)
    {
        $data = $this->validateData($request, $guru);

        $data['nip'] = $this->rapikan($data['nip']);

        $passwordBaru = $this->rapikan($data['password'] ?? '');

        if ($passwordBaru !== '') {
            $data['password'] = Hash::make($passwordBaru);
        } else {
            unset($data['password']);
        }

        $guru->update($data);

        return redirect()->route('admin.guru.index')
            ->with('success', 'Akun guru pembimbing berhasil diperbarui.');
    }

    public function destroy(User $guru)
    {
        $guru->delete();
        return back()->with('success', 'Akun guru pembimbing berhasil dihapus.');
    }

    /**
     * Hapus SEMUA akun guru pembimbing sekaligus (kecuali akun sendiri).
     * - jurnals.disetujui_oleh dilepas dulu (FK RESTRICT) agar tidak gagal.
     * - guru_id pada siswa dilepas otomatis.
     * - lembar observasi milik guru ikut terhapus (FK cascade).
     */
    public function destroyAll()
    {
        $guruIds = User::where('role', 'guru_pembimbing')
            ->where('id', '!=', auth()->id())
            ->pluck('id');

        if ($guruIds->isEmpty()) {
            return back()->with('error', 'Tidak ada akun guru pembimbing yang bisa dihapus.');
        }

        DB::transaction(function () use ($guruIds) {
            \App\Models\Jurnal::whereIn('disetujui_oleh', $guruIds)->update(['disetujui_oleh' => null]);

            User::siswa()->whereIn('guru_id', $guruIds)->update(['guru_id' => null]);

            User::whereIn('id', $guruIds)->delete();
        });

        return back()->with('success', "Berhasil menghapus {$guruIds->count()} akun guru pembimbing. Lembar observasi milik guru ikut terhapus; penilaian & bimbingan siswa dilepas otomatis.");
    }

    /**
     * Tetapkan guru pembimbing sebagai Wakasek.
     * Wakasek berhak memvalidasi lembar observasi guru lain
     * dan boleh memvalidasi lembar observasinya sendiri.
     */
    public function jadikanWakasek(User $guru)
    {
        abort_unless($guru->role === 'guru_pembimbing', 404);

        $guru->update(['is_wakasek' => true]);

        return back()->with('success', "\"{$guru->name}\" kini ditetapkan sebagai Wakasek dan dapat memvalidasi lembar observasi.");
    }

    /**
     * Batalkan status Wakasek pada guru pembimbing.
     * Setelah dibatalkan, guru tersebut harus kembali mengajukan validasi
     * lembar observasinya ke Wakasek lain yang sudah ditetapkan admin.
     */
    public function batalkanWakasek(User $guru)
    {
        abort_unless($guru->role === 'guru_pembimbing', 404);

        $guru->update(['is_wakasek' => false]);

        return back()->with('success', "Status Wakasek untuk \"{$guru->name}\" telah dibatalkan.");
    }

    /**
     * Tetapkan guru pembimbing agar juga dapat mengakses panel admin.
     * Guru tetap login sebagai guru, namun mendapat kartu & akses ke halaman admin.
     */
    public function jadikanAdmin(User $guru)
    {
        abort_unless($guru->role === 'guru_pembimbing', 404);

        $guru->update(['is_admin' => true]);

        return back()->with('success', "\"{$guru->name}\" kini juga dapat mengakses panel admin.");
    }

    /**
     * Batalkan akses admin pada guru pembimbing.
     */
    public function batalkanAdmin(User $guru)
    {
        abort_unless($guru->role === 'guru_pembimbing', 404);

        $guru->update(['is_admin' => false]);

        return back()->with('success', "Akses admin untuk \"{$guru->name}\" telah dibatalkan.");
    }

    // =====================================================
    //  IMPORT / EXPORT
    // =====================================================

    public function exportExcel(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        return Excel::download(new GuruExport($q), 'data-guru-' . date('Ymd-His') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $guru = User::query()
            ->where('role', 'guru_pembimbing')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('nip', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('admin.guru.pdf', compact('guru'))->setPaper('a4', 'portrait');

        return $pdf->download('data-guru-' . date('Ymd-His') . '.pdf');
    }

    public function template()
    {
        return Excel::download(new GuruTemplateExport, 'template-import-guru.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            Excel::import(new GuruImport, $request->file('file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $pesan = collect($e->failures())
                ->map(fn ($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))
                ->take(10)
                ->implode(' | ');

            return back()->with('error', 'Sebagian data gagal diimpor. ' . $pesan);
        }

        return back()->with('success', 'Data guru pembimbing berhasil diimpor.');
    }
}
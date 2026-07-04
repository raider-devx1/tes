<?php

namespace App\Http\Controllers;

use App\Exports\GuruExport;
use App\Exports\GuruTemplateExport;
use App\Imports\GuruImport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
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

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        // ---- Kartu informasi ----
        $totalGuru = User::where('role', 'guru_pembimbing')->count();

        $guruIdsDenganBimbingan = User::where('role', 'siswa_pkl')
            ->whereNotNull('guru_id')
            ->distinct()
            ->pluck('guru_id');

        $guruAdaBimbingan   = $guruIdsDenganBimbingan->count();
        $totalSiswaDibimbing = User::where('role', 'siswa_pkl')->whereNotNull('guru_id')->count();

        $rekap = [
            'total'           => $totalGuru,
            'ada_bimbingan'   => $guruAdaBimbingan,
            'tanpa_bimbingan' => max($totalGuru - $guruAdaBimbingan, 0),
            'siswa_dibimbing' => $totalSiswaDibimbing,
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
        $data['role'] = 'guru_pembimbing';
        $data['password'] = Hash::make($data['password']);

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

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
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
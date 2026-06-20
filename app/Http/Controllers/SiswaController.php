<?php

namespace App\Http\Controllers;

use App\Models\PeriodePkl;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    /** Validasi data siswa (dipakai store & update). */
    private function validateData(Request $request, ?User $siswa = null): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($siswa?->id)],
            'nisn'          => ['nullable', 'string', 'max:20'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'no_hp'         => ['nullable', 'string', 'max:20'],
            'kelas'         => ['nullable', 'string', 'max:50'],
            'jurusan'       => ['nullable', 'string', 'max:100'],
            'status_pkl'    => ['required', Rule::in(['belum', 'aktif', 'selesai'])],
            'perusahaan_id' => ['nullable', 'exists:perusahaans,id'],
            'instruktur_id' => ['nullable', 'exists:users,id'],
            'guru_id'       => ['nullable', 'exists:users,id'],
            'periode_id'    => ['nullable', 'exists:periode_pkls,id'],
            'foto'          => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'password'      => [$siswa ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /** Data untuk semua dropdown form. */
    private function dropdownData(): array
    {
        return [
            'perusahaanList' => Perusahaan::orderBy('nama_perusahaan')->get(),
            'guruList'       => User::where('role', 'guru_pembimbing')->orderBy('name')->get(),
            'instrukturList' => User::where('role', 'instruktur_industri')->orderBy('name')->get(),
            'periodeList'    => PeriodePkl::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get(),
        ];
    }

    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $status = $request->get('status', '');

        $siswa = User::query()
            ->where('role', 'siswa_pkl')
            ->with(['perusahaan', 'guru', 'instruktur', 'periode'])
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('nisn', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->when($status, fn ($query) => $query->where('status_pkl', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.siswa.index', compact('siswa', 'q', 'status'));
    }

    public function create()
    {
        return view('admin.siswa.create', array_merge(
            ['siswa' => new User()],
            $this->dropdownData()
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['role'] = 'siswa_pkl';
        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto-siswa', 'public');
        }

        User::create($data);

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(User $siswa)
    {
        return view('admin.siswa.edit', array_merge(
            ['siswa' => $siswa],
            $this->dropdownData()
        ));
    }

    public function update(Request $request, User $siswa)
    {
        $data = $this->validateData($request, $siswa);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('foto')) {
            if ($siswa->foto) {
                Storage::disk('public')->delete($siswa->foto);
            }
            $data['foto'] = $request->file('foto')->store('foto-siswa', 'public');
        }

        $siswa->update($data);

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(User $siswa)
    {
        if ($siswa->foto) {
            Storage::disk('public')->delete($siswa->foto);
        }
        $siswa->delete();

        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
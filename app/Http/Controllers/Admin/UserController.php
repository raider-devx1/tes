<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Admin mengelola seluruh akun (siswa, guru, instruktur) + pemetaan PKL.
 * Satu controller resource menggantikan banyak method tersebar.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->input('role', User::ROLE_SISWA);

        $users = User::where('role', $role)
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->q . '%'))
            ->with(['perusahaan', 'instruktur', 'guru'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $perusahaans = Perusahaan::orderBy('nama')->get();
        $gurus       = User::where('role', User::ROLE_GURU)->orderBy('name')->get();
        $instrukturs = User::where('role', User::ROLE_INSTRUKTUR)->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'role', 'perusahaans', 'gurus', 'instrukturs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['required', Rule::in([User::ROLE_SISWA, User::ROLE_GURU, User::ROLE_INSTRUKTUR, User::ROLE_ADMIN])],
            'password' => ['required', 'string', 'min:8'],
            'nis'      => ['nullable', 'string', 'max:50'],
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return back()->with('success', 'Akun pengguna berhasil dibuat.');
    }

    /** Pemetaan siswa -> kelas/jurusan/perusahaan/instruktur/guru. */
    public function updateMapping(Request $request, User $user)
    {
        $data = $request->validate([
            'kelas'         => ['nullable', 'string', 'max:50'],
            'jurusan'       => ['nullable', 'string', 'max:100'],
            'perusahaan_id' => ['nullable', 'exists:perusahaans,id'],
            'instruktur_id' => ['nullable', 'exists:users,id'],
            'guru_id'       => ['nullable', 'exists:users,id'],
        ]);
        $user->update($data);

        return back()->with('success', 'Pemetaan PKL ' . $user->name . ' berhasil disimpan.');
    }

    public function destroy(User $user)
    {
        abort_if($user->isAdmin(), 403, 'Akun admin tidak dapat dihapus.');
        $user->delete();
        return back()->with('success', 'Akun pengguna dihapus.');
    }
}

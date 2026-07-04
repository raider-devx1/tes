<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminAkunController extends Controller
{
    /** Validasi akun admin (dipakai store & update). */
    private function validateData(Request $request, ?User $admin = null): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            // Email = identitas login admin: wajib & unik
            'email'    => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($admin?->id)],
            'no_hp'    => ['nullable', 'string', 'max:20'],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        // ---- Kartu informasi ----
        $totalAdmin = User::where('role', 'admin')->count();

        $rekap = [
            'total'      => $totalAdmin,
            'akun_anda'  => 1,
            'admin_lain' => max($totalAdmin - 1, 0),
        ];

        $admins = User::query()
            ->where('role', 'admin')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.akun_admin.index', compact('admins', 'q', 'rekap'));
    }

    public function create()
    {
        return view('admin.akun_admin.create', ['admin' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['role'] = 'admin';
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.akun-admin.index')
            ->with('success', 'Akun admin berhasil ditambahkan.');
    }

    public function edit(User $admin)
    {
        return view('admin.akun_admin.edit', compact('admin'));
    }

    public function update(Request $request, User $admin)
    {
        $data = $this->validateData($request, $admin);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $admin->update($data);

        return redirect()->route('admin.akun-admin.index')
            ->with('success', 'Akun admin berhasil diperbarui.');
    }

    public function destroy(User $admin)
    {
        // Pengaman 1: tidak boleh menghapus akun sendiri
        if ($admin->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Pengaman 2: minimal harus tersisa satu akun admin
        if (User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Minimal harus ada satu akun admin yang tersisa.');
        }

        $admin->delete();

        return back()->with('success', 'Akun admin berhasil dihapus.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan form profil pengguna.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Perbarui informasi profil pengguna.
     * Nama & foto boleh diubah semua peran; email hanya instruktur & admin.
     * NIP/NISN tidak pernah diubah dari halaman ini.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Pengaman: siswa & guru tidak boleh mengubah email walau kirim POST langsung.
        if (! in_array($user->role, ['instruktur_industri', 'admin'], true)) {
            unset($data['email']);
        }

        // Upload / perbarui foto profil
        if ($request->hasFile('foto')) {
            // Hapus foto lama bila ada
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $data['foto'] = $request->file('foto')->store('foto-profil', 'public');
        } else {
            // Jangan menimpa foto lama dengan null bila tidak ada file baru
            unset($data['foto']);
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Hapus akun dinonaktifkan untuk semua peran.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort(403, 'Penghapusan akun tidak diizinkan.');
    }
}
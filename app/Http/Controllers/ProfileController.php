<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\ImageCompressor;
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
     * Nama, nomor HP, & foto boleh diubah semua peran (siswa, guru, admin);
     * email hanya admin. NIP/NISN tidak pernah diubah dari halaman ini.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Pengaman: siswa & guru tidak boleh mengubah email walau kirim POST langsung.
        if (! in_array($user->role, ['admin'], true)) {
            unset($data['email']);
        }

        // Upload / perbarui foto profil
        if ($request->hasFile('foto')) {
            // Hapus foto lama bila ada
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $data['foto'] = ImageCompressor::store($request->file('foto'), 'foto-profil');
        } else {
            // Jangan menimpa foto lama dengan null bila tidak ada file baru
            unset($data['foto']);
        }

        $user->fill($data);

        // Catat bagian apa saja yang benar-benar berubah, untuk pesan sukses.
        $berubah = [];

        if ($user->isDirty('name')) {
            $berubah[] = 'nama';
        }

        if ($user->isDirty('email')) {
            $berubah[] = 'email';
            $user->email_verified_at = null;
        }

        // Nomor HP: dihitung berubah juga ketika dikosongkan (diisi -> NULL),
        // supaya siswa/guru mendapat konfirmasi bahwa nomornya benar-benar dihapus.
        if ($user->isDirty('no_hp')) {
            $berubah[] = 'nomor HP';
        }

        if ($user->isDirty('foto')) {
            $berubah[] = 'foto profil';
        }

        $user->save();

        // Susun kalimat: "nama", "nama dan foto profil", "nama, email, dan foto profil".
        $adaPerubahan = ! empty($berubah);

        if (! $adaPerubahan) {
            $pesan = 'Tidak ada perubahan pada profil Anda. Data tetap seperti sebelumnya.';
        } else {
            $jumlah  = count($berubah);
            $terakhir = array_pop($berubah);
            $daftar  = $jumlah > 1
                ? implode(', ', $berubah) . ' dan ' . $terakhir
                : $terakhir;

            $pesan = 'Berhasil! Anda telah mengubah ' . $daftar . ' akun Anda.';
        }

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated')
            ->with('profil_pesan', $pesan)
            ->with('profil_pesan_sukses', $adaPerubahan);
    }

    /**
     * Hapus akun dinonaktifkan untuk semua peran.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort(403, 'Penghapusan akun tidak diizinkan.');
    }
}
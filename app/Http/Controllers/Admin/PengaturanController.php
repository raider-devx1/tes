<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function edit()
    {
        $pengaturan = Pengaturan::semua();
        return view('admin.pengaturan.edit', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_sekolah'   => ['nullable', 'string', 'max:255'],
            'tahun_ajaran'   => ['nullable', 'string', 'max:50'],
            'kepala_sekolah' => ['nullable', 'string', 'max:255'],
            'nip_kepala'     => ['nullable', 'string', 'max:50'],
            'alamat_sekolah' => ['nullable', 'string'],
        ]);

        foreach ($data as $kunci => $nilai) {
            Pengaturan::simpan($kunci, $nilai);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}

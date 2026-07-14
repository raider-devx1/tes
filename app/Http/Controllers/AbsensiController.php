<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ROLE: SISWA PKL (mengisi & melihat rekap kehadiran sendiri)
    |--------------------------------------------------------------------------
    */
    public function indexSiswa(Request $request)
    {
        $query = Absensi::where('siswa_id', Auth::id());

        if ($request->filled('bulan')) {
            $tanggal = \Carbon\Carbon::parse($request->bulan . '-01');
            $query->whereYear('tanggal', $tanggal->year)
                  ->whereMonth('tanggal', $tanggal->month);
        }

        $absensis = $query->orderBy('tanggal', 'desc')->get();

        $rekap = [
            'Hadir' => $absensis->where('status', 'Hadir')->count(),
            'Izin'  => $absensis->where('status', 'Izin')->count(),
            'Sakit' => $absensis->where('status', 'Sakit')->count(),
            'Alpha' => $absensis->where('status', 'Alpha')->count(),
        ];

        $bulan = $request->bulan ?? date('Y-m');

        return view('siswa.absensi.index', compact('absensis', 'rekap', 'bulan'));
    }

    /**
     * Siswa membuat 1 baris absensi (status_validasi = draft).
     */
    public function storeSiswa(Request $request)
    {
        $validated = $request->validate([
            'tanggal'    => ['required', 'date'],
            'status'     => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
            'jam_masuk'  => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
        ]);

        Absensi::updateOrCreate(
            ['siswa_id' => Auth::id(), 'tanggal' => $validated['tanggal']],
            [
                'status'          => $validated['status'],
                'jam_masuk'       => $validated['jam_masuk'] ?? null,
                'jam_pulang'      => $validated['jam_pulang'] ?? null,
                'status_validasi' => 'draft',
            ]
        );

        return back()->with('success', 'Absensi tersimpan (draft). Cetak draf, minta paraf instruktur, lalu ajukan.');
    }

    /**
     * Siswa mengajukan: WAJIB unggah foto bukti + ketik ulang catatan instruktur.
     */
    public function ajukanSiswa(Request $request, $id)
    {
        $absensi = Absensi::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'catatan_instruktur' => 'required|string',
            'foto_bukti'         => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'catatan_instruktur.required' => 'Catatan dari instruktur wajib diketik ulang.',
            'foto_bukti.required'         => 'Foto bukti fisik lembar berparaf wajib diunggah.',
            'foto_bukti.image'            => 'File harus berupa gambar.',
            'foto_bukti.mimes'            => 'Format foto harus jpeg, png, atau jpg.',
            'foto_bukti.max'              => 'Ukuran foto maksimal 2MB.',
        ]);

        if ($absensi->foto_bukti) {
            Storage::disk('public')->delete($absensi->foto_bukti);
        }
        $path = $request->file('foto_bukti')->store('bukti_fisik/absensi', 'public');

        $absensi->update([
            'catatan_instruktur' => $validated['catatan_instruktur'],
            'foto_bukti'         => $path,
            'status_validasi'    => 'diajukan',
        ]);

        return back()->with('success', 'Absensi berhasil diajukan ke Guru Pembimbing.');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE: GURU PEMBIMBING (validasi)
    |--------------------------------------------------------------------------
    */
    public function validasiByGuru(Request $request, $id)
    {
        $absensi = Absensi::with('siswa')->findOrFail($id);

        abort_unless(
            $absensi->siswa && (int) $absensi->siswa->guru_id === (int) Auth::id(),
            403,
            'Akses ditolak: absensi ini bukan milik siswa bimbingan Anda.'
        );

        $aksi = $request->input('aksi', 'valid');

        if ($aksi === 'tolak') {
            $absensi->update([
                'status_validasi'      => 'draft',
                'validated_by_guru_id' => null,
                'validated_at'         => null,
            ]);

            return back()->with('success', 'Pengajuan ditolak. Absensi dikembalikan ke siswa (draft).');
        }

        $absensi->update([
            'status_validasi'      => 'disetujui',
            'validated_by_guru_id' => Auth::id(),
            'validated_at'         => now(),
        ]);

        return back()->with('success', 'Absensi berhasil divalidasi (disetujui).');
    }
}
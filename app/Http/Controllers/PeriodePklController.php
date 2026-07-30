<?php

namespace App\Http\Controllers;

use App\Models\PeriodePkl;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodePklController extends Controller
{
    /**
     * Aturan validasi periode (dipakai store & update).
     *
     * Nama periode WAJIB UNIK. Alasannya dua:
     *   1. Admin memilih periode dengan cara MENGETIK namanya pada fitur
     *      "Atur Status Siswa per Periode". Kalau ada dua periode bernama
     *      sama, sistem tidak mungkin tahu yang mana yang dimaksud.
     *   2. Nama periode juga tampil di PDF & rekap, sehingga nama ganda
     *      membuat berkas angkatan lama dan baru tidak bisa dibedakan.
     *
     * Saat update, periode yang sedang diedit dikecualikan (ignore) supaya
     * admin tetap bisa menyimpan tanpa harus mengganti namanya.
     */
    private function validateData(Request $request, ?PeriodePkl $periode = null): array
    {
        $request->merge(['is_active' => $request->boolean('is_active')]);

        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('periode_pkls', 'nama')->ignore($periode?->id),
            ],
            'tahun_ajaran'    => ['required', 'string', 'max:20'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'is_active'       => ['boolean'],
            'keterangan'      => ['nullable', 'string'],
        ], [
            'nama.required'   => 'Nama periode wajib diisi.',
            'nama.unique'     => 'Nama periode ":input" sudah dipakai. Gunakan nama lain yang berbeda.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);
    }

    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $periode = PeriodePkl::query()
            ->when($q, function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                      ->orWhere('tahun_ajaran', 'like', "%{$q}%");
            })
            ->orderByDesc('is_active')
            ->orderByDesc('tanggal_mulai')
            ->paginate(10)
            ->withQueryString();

        // Dipakai sebagai daftar saran (datalist) pada input "Nama Periode"
        // di kartu "Atur Status Siswa per Periode". Bukan lagi dropdown,
        // tapi tetap membantu admin mengetik nama dengan benar.
        $semuaPeriode = PeriodePkl::orderByDesc('is_active')
            ->orderByDesc('tanggal_mulai')
            ->get();

        return view('admin.periode.index', compact('periode', 'q', 'semuaPeriode'));
    }

    public function create()
    {
        return view('admin.periode.create', ['periode' => new PeriodePkl()]);
    }

    public function store(Request $request)
    {
        PeriodePkl::create($this->validateData($request));

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode PKL berhasil ditambahkan.');
    }

    public function edit(PeriodePkl $periode)
    {
        return view('admin.periode.edit', compact('periode'));
    }

    public function update(Request $request, PeriodePkl $periode)
    {
        // Kirim $periode agar aturan unique mengecualikan dirinya sendiri.
        $periode->update($this->validateData($request, $periode));

        return redirect()->route('admin.periode.index')
            ->with('success', 'Periode PKL berhasil diperbarui.');
    }

    public function destroy(PeriodePkl $periode)
    {
        if ($periode->siswa()->exists()) {
            return back()->with('error', 'Tidak bisa menghapus periode yang masih memiliki siswa terdaftar.');
        }

        $periode->delete();

        return back()->with('success', 'Periode PKL berhasil dihapus.');
    }

    /** Jadikan satu periode aktif (model otomatis menonaktifkan lainnya). */
    public function aktifkan(PeriodePkl $periode)
    {
        $periode->update(['is_active' => true]);

        return back()->with('success', "Periode \"{$periode->nama}\" kini menjadi periode aktif.");
    }

    /**
     * Ubah status_pkl SEMUA siswa dalam satu periode sekaligus.
     *
     * Admin kini MENGETIK nama periode (bukan memilih dari dropdown).
     * Pencarian nama dibuat tidak peka huruf besar/kecil dan mengabaikan
     * spasi berlebih di awal/akhir, supaya " pkl gelombang 1 " tetap cocok
     * dengan "PKL Gelombang 1".
     */
    public function updateStatusSiswa(Request $request)
    {
        $request->merge(['nama_periode' => trim((string) $request->input('nama_periode'))]);

        $request->validate([
            'nama_periode' => ['required', 'string', 'max:100'],
            'status_pkl'   => ['required', 'in:belum,aktif,selesai'],
        ], [
            'nama_periode.required' => 'Silakan masukkan nama periode terlebih dahulu.',
            'status_pkl.in'         => 'Status harus salah satu dari: belum, aktif, atau selesai.',
        ]);

        $nama = $request->input('nama_periode');

        // Karena kolom nama sudah unik, whereRaw LOWER(...) pasti menghasilkan
        // paling banyak satu baris -- tidak ada ambiguitas.
        $periode = PeriodePkl::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])->first();

        if (! $periode) {
            return back()
                ->withInput()
                ->with('error', "Periode dengan nama \"{$nama}\" tidak ditemukan. Periksa kembali ejaan nama periode.");
        }

        $jumlah = \App\Models\User::siswa()
            ->periode($periode->id)
            ->update(['status_pkl' => $request->status_pkl]);

        if ($jumlah === 0) {
            return back()
                ->withInput()
                ->with('error', "Tidak ada siswa terdaftar pada periode \"{$periode->nama}\".");
        }

        return back()->with('success', "Status {$jumlah} siswa pada periode \"{$periode->nama}\" berhasil diubah menjadi \"{$request->status_pkl}\".");
    }
}

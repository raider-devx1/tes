<?php

namespace App\Http\Controllers;

use App\Exports\SiswaExport;
use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;
use App\Models\PeriodePkl;
use App\Models\Perusahaan;
use App\Models\User;
use App\Support\ImageCompressor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    /**
     * Validasi data siswa (dipakai store & update).
     *
     * CATATAN PENTING soal NISN dan arsip
     * -----------------------------------
     * Kolom `nisn` memiliki UNIQUE INDEX di database. Index tersebut bekerja
     * pada level tabel dan TIDAK mengenal konsep soft delete: baris yang
     * sudah diarsipkan tetap menempati NISN-nya.
     *
     * Karena itu aturan uniknya dipecah menjadi dua lapis:
     *
     *  1. Rule::unique(...)->withoutTrashed()
     *     Menangani bentrok dengan siswa AKTIF, dengan pesan bawaan Laravel.
     *
     *  2. Closure pemeriksa arsip
     *     Menangani bentrok dengan siswa TERARSIP. Tanpa ini, admin hanya
     *     melihat pesan "NISN sudah digunakan" sementara siswa pemilik NISN
     *     itu tidak muncul di daftar mana pun -- persis kebingungan yang
     *     ingin dihindari. Pesan di bawah menyebut nama siswanya dan
     *     mengarahkan admin ke halaman Arsip.
     */
    private function validateData(Request $request, ?User $siswa = null): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            // NISN = identitas login siswa: wajib & unik (email tidak dipakai lagi)
            'nisn'          => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'nisn')->ignore($siswa?->id)->withoutTrashed(),
                function ($attribute, $value, $fail) use ($siswa) {
                    $terarsip = User::onlyTrashed()
                        ->where('nisn', $value)
                        ->when($siswa, fn ($query) => $query->whereKeyNot($siswa->id))
                        ->first();

                    if ($terarsip) {
                        $fail(
                            "NISN {$value} masih tercatat atas nama \"{$terarsip->name}\" yang berada di Arsip Siswa. "
                            . 'Buka menu Arsip lalu pulihkan siswa tersebut, atau ubah NISN-nya lebih dahulu.'
                        );
                    }
                },
            ],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'no_hp'         => ['nullable', 'string', 'max:20'],
            'kelas'         => ['nullable', 'string', 'max:50'],
            'jurusan'       => ['nullable', 'string', 'max:100'],
            'status_pkl'    => ['required', Rule::in(['belum', 'aktif', 'selesai'])],
            'perusahaan_id' => ['nullable', 'exists:perusahaans,id'],
            'guru_id'       => ['nullable', 'exists:users,id'],
            'periode_id'    => ['nullable', 'exists:periode_pkls,id'],
            'foto'          => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:3072'],
            'password'      => [$siswa ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /** Data untuk semua dropdown form. */
    private function dropdownData(): array
    {
        return [
            'perusahaanList' => Perusahaan::orderBy('nama_perusahaan')->get(),
            'guruList'       => User::where('role', 'guru_pembimbing')->orderBy('name')->get(),
            'periodeList'    => PeriodePkl::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get(),
        ];
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');
        $periode = (string) $request->get('periode', '');

        // ---- Kartu informasi (dihitung menyeluruh, tidak terpengaruh filter) ----
        $rekap = [
            'total'   => User::siswa()->count(),
            'aktif'   => User::siswa()->where('status_pkl', 'aktif')->count(),
            'belum'   => User::siswa()->where('status_pkl', 'belum')->count(),
            'selesai' => User::siswa()->where('status_pkl', 'selesai')->count(),
        ];

        // Jumlah siswa terarsip, untuk lencana pada tombol "Arsip".
        $jumlahArsip = User::onlyTrashed()->siswa()->count();

        $siswa = User::query()
            ->siswa()
            ->with(['perusahaan', 'guru', 'periode'])
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('nisn', 'like', "%{$q}%");
            })
            ->when($status, fn ($query) => $query->where('status_pkl', $status))
            ->periode($periode)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $periodeList = PeriodePkl::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();

        return view('admin.siswa.index', compact('siswa', 'q', 'status', 'periode', 'rekap', 'periodeList', 'jumlahArsip'));
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
            $data['foto'] = ImageCompressor::store($request->file('foto'), 'foto-siswa');
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
            $data['foto'] = ImageCompressor::store($request->file('foto'), 'foto-siswa');
        }

        $siswa->update($data);

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Hapus data siswa (soft delete).
     *
     * Sejak model User memakai SoftDeletes, pemanggilan delete() hanya
     * mengisi kolom deleted_at. Tidak ada DELETE sungguhan ke database,
     * sehingga foreign key onDelete('cascade') TIDAK berjalan dan seluruh
     * jurnal, absensi, nilai, observasi, catatan, serta dokumen siswa
     * tetap tersimpan utuh.
     *
     * Foto profil SENGAJA tidak dihapus dari storage. Karena datanya masih
     * bisa dipulihkan, menghapus berkasnya akan membuat pemulihan tidak utuh.
     */
    public function destroy(User $siswa)
    {
        $siswa->delete();

        return back()->with('success', 'Data siswa dipindahkan ke arsip. Seluruh riwayat PKL-nya tetap tersimpan dan dapat dipulihkan lewat menu Arsip.');
    }

    /**
     * Arsipkan SELURUH data siswa pada satu periode PKL sekaligus.
     *
     * PENTING: sejak User memakai SoftDeletes, operasi ini TIDAK lagi
     * memusnahkan data. Baris siswa hanya ditandai terhapus, dan seluruh
     * riwayat PKL (absensi, jurnal, nilai, observasi, dokumen, catatan)
     * tetap utuh di database sebagai arsip angkatan.
     *
     * Sejak setiap baris transaksi menyimpan periode_id-nya sendiri, arsip
     * angkatan lama tetap bisa ditelusuri dan dicetak walaupun periodenya
     * sudah tidak aktif.
     */
    public function destroyByPeriode(Request $request)
    {
        $data = $request->validate([
            'periode_id' => ['required', 'exists:periode_pkls,id'],
        ]);

        $jumlahSiswa = User::siswa()
            ->periode($data['periode_id'])
            ->count();

        if ($jumlahSiswa === 0) {
            return back()->with('error', 'Tidak ada data siswa pada periode tersebut.');
        }

        // Berkas foto TIDAK dihapus: data masih bisa dipulihkan, sehingga
        // menghapus fotonya akan membuat pemulihan menjadi tidak utuh.
        $jumlah = User::siswa()
            ->periode($data['periode_id'])
            ->delete();

        $namaPeriode = optional(PeriodePkl::find($data['periode_id']))->nama ?? 'terpilih';

        return back()->with('success', "Berhasil mengarsipkan {$jumlah} data siswa pada periode \"{$namaPeriode}\". Seluruh riwayat PKL (absensi, jurnal, nilai, dokumen) tetap tersimpan sebagai arsip dan dapat dipulihkan lewat menu Arsip.");
    }

    /*
    |--------------------------------------------------------------------------
    | ARSIP SISWA
    |--------------------------------------------------------------------------
    | Halaman ini menampilkan siswa yang sudah diarsipkan (soft delete).
    | Tanpa halaman ini, data yang "dihapus" menjadi tidak terlihat sekaligus
    | tidak bisa dikembalikan lewat antarmuka -- padahal datanya masih ada dan
    | NISN-nya masih terkunci di database.
    */

    /** Daftar siswa yang berada di arsip. */
    public function arsip(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $periode = (string) $request->get('periode', '');

        $siswa = User::onlyTrashed()
            ->siswa()
            ->with(['perusahaan', 'guru', 'periode'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            })
            ->periode($periode)
            ->orderByDesc('deleted_at')
            ->paginate(10)
            ->withQueryString();

        /*
         * Tandai baris yang NISN-nya sudah dipakai siswa aktif.
         *
         * Baris seperti ini tidak bisa dipulihkan karena akan melanggar
         * unique index pada kolom nisn. Lebih baik admin mengetahuinya dari
         * tampilan daftar sejak awal daripada baru tahu setelah menekan
         * tombol Pulihkan dan menerima pesan gagal.
         */
        $daftarNisn = $siswa->pluck('nisn')->filter()->values()->all();

        $nisnBentrok = $daftarNisn === []
            ? []
            : User::withoutTrashed()
                ->whereIn('nisn', $daftarNisn)
                ->pluck('nisn')
                ->all();

        $periodeList = PeriodePkl::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();

        $jumlahArsip = User::onlyTrashed()->siswa()->count();

        return view('admin.siswa.arsip', compact('siswa', 'q', 'periode', 'periodeList', 'nisnBentrok', 'jumlahArsip'));
    }

    /**
     * Pulihkan satu siswa dari arsip.
     *
     * Route model binding sengaja TIDAK dipakai di sini. Binding bawaan
     * mengecualikan baris yang sudah di-soft-delete, sehingga siswa terarsip
     * justru akan menghasilkan 404. Pencarian dilakukan manual dengan
     * onlyTrashed().
     */
    public function restore(int $siswa)
    {
        $data = User::onlyTrashed()->siswa()->findOrFail($siswa);

        // Pemeriksaan bentrok NISN sebelum memulihkan. Tanpa ini, pemulihan
        // akan ditolak oleh database dengan pesan galat teknis yang tidak
        // bisa dipahami admin sekolah.
        if ($data->nisn) {
            $bentrok = User::withoutTrashed()->where('nisn', $data->nisn)->first();

            if ($bentrok) {
                return back()->with('error',
                    "Data \"{$data->name}\" belum bisa dipulihkan. NISN {$data->nisn} sedang dipakai oleh siswa aktif bernama \"{$bentrok->name}\". "
                    . 'Perbaiki dulu NISN salah satu dari keduanya, lalu ulangi pemulihan.'
                );
            }
        }

        $data->restore();

        return back()->with('success', "Data siswa \"{$data->name}\" berhasil dipulihkan beserta seluruh riwayat PKL-nya.");
    }

    /**
     * Pulihkan seluruh siswa terarsip pada satu periode sekaligus.
     *
     * Pasangan dari destroyByPeriode(). Baris yang NISN-nya bentrok dengan
     * siswa aktif sengaja DILEWATI, bukan membatalkan seluruh proses, supaya
     * satu data bermasalah tidak menghalangi pemulihan seangkatan. Nama yang
     * dilewati dilaporkan kembali ke admin.
     */
    public function restoreByPeriode(Request $request)
    {
        $data = $request->validate([
            'periode_id' => ['required', 'exists:periode_pkls,id'],
        ]);

        $kandidat = User::onlyTrashed()
            ->siswa()
            ->periode($data['periode_id'])
            ->get();

        $namaPeriode = optional(PeriodePkl::find($data['periode_id']))->nama ?? 'terpilih';

        if ($kandidat->isEmpty()) {
            return back()->with('error', "Tidak ada data siswa terarsip pada periode \"{$namaPeriode}\".");
        }

        $daftarNisn = $kandidat->pluck('nisn')->filter()->values()->all();

        $nisnBentrok = $daftarNisn === []
            ? []
            : User::withoutTrashed()
                ->whereIn('nisn', $daftarNisn)
                ->pluck('nisn')
                ->all();

        $dipulihkan = 0;
        $dilewati = [];

        foreach ($kandidat as $baris) {
            if ($baris->nisn && in_array($baris->nisn, $nisnBentrok, true)) {
                $dilewati[] = $baris->name;
                continue;
            }

            $baris->restore();
            $dipulihkan++;
        }

        if ($dipulihkan === 0) {
            return back()->with('error', "Tidak ada data yang dapat dipulihkan pada periode \"{$namaPeriode}\" karena seluruh NISN-nya sedang dipakai siswa aktif.");
        }

        $pesan = "Berhasil memulihkan {$dipulihkan} data siswa pada periode \"{$namaPeriode}\".";

        if ($dilewati !== []) {
            $contoh = implode(', ', array_slice($dilewati, 0, 5));
            $sisa = count($dilewati) - 5;
            $pesan .= ' ' . count($dilewati) . ' data dilewati karena NISN-nya bentrok dengan siswa aktif: '
                . $contoh . ($sisa > 0 ? " dan {$sisa} lainnya" : '') . '.';
        }

        return back()->with('success', $pesan);
    }

    public function exportExcel(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');
        $periode = (string) $request->get('periode', '');

        return Excel::download(new SiswaExport($q, $status, $periode), 'data-siswa-' . date('Ymd-His') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');
        $periode = (string) $request->get('periode', '');

        $siswa = User::query()
            ->siswa()
            ->with(['perusahaan', 'guru', 'periode'])
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('nisn', 'like', "%{$q}%");
            })
            ->when($status, fn ($query) => $query->where('status_pkl', $status))
            ->periode($periode)
            ->orderBy('name')
            ->get();

        $pdf = Pdf::loadView('admin.siswa.pdf', compact('siswa'))->setPaper('a4', 'landscape');

        return $pdf->download('data-siswa-' . date('Ymd-His') . '.pdf');
    }

    public function template()
    {
        return Excel::download(new SiswaTemplateExport, 'template-import-siswa.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            Excel::import(new SiswaImport, $request->file('file'));
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $pesan = collect($e->failures())
                ->map(fn ($f) => "Baris {$f->row()}: " . implode(', ', $f->errors()))
                ->take(10)
                ->implode(' | ');

            return back()->with('error', 'Sebagian data gagal diimpor. ' . $pesan);
        }

        return back()->with('success', 'Data siswa berhasil diimpor.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\CatatanKegiatan;
use App\Models\Jurnal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MonitoringController extends Controller
{
    /** Opsi dropdown filter kelas & jurusan (diambil dari siswa PKL). */
    private function opsiFilter(): array
    {
        $base = User::where('role', 'siswa_pkl');

        return [
            'kelasList'   => (clone $base)->whereNotNull('kelas')->distinct()->orderBy('kelas')->pluck('kelas'),
            'jurusanList' => (clone $base)->whereNotNull('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan'),
        ];
    }

    /** Daftar siswa PKL untuk dropdown form tambah/edit. */
    private function siswaList()
    {
        return User::where('role', 'siswa_pkl')->orderBy('name')->get(['id', 'name', 'nisn']);
    }

    // ===================================================================
    // JURNAL
    // ===================================================================
    public function jurnal(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $status  = $request->get('status', '');
        $kelas   = $request->get('kelas', '');
        $jurusan = $request->get('jurusan', '');
        $tanggal = $request->get('tanggal', '');

        $jurnal = Jurnal::query()
            ->with(['siswa', 'items'])
            ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
                $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas,   fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
            ->when($jurusan, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jurusan', $jurusan)))
            ->when($status,  fn ($query) => $query->where('status_persetujuan', $status))
            ->when($tanggal, fn ($query) => $query->whereDate('hari_tanggal', $tanggal))
            ->orderByDesc('hari_tanggal')
            ->paginate(15)
            ->withQueryString();

        $rekap = [
            'total'     => Jurnal::count(),
            'disetujui' => Jurnal::where('status_persetujuan', 'disetujui')->count(),
            'pending'   => Jurnal::where('status_persetujuan', 'pending')->count(),
            'revisi'    => Jurnal::where('status_persetujuan', 'revisi')->count(),
        ];

        return view('admin.monitoring.jurnal', array_merge(
            compact('jurnal', 'q', 'status', 'kelas', 'jurusan', 'tanggal', 'rekap'),
            ['siswaList' => $this->siswaList()],
            $this->opsiFilter()
        ));
    }

    public function storeJurnal(Request $request)
    {
        $data = $request->validate([
            'siswa_id'           => ['required', 'exists:users,id'],
            'hari_tanggal'       => ['required', 'date'],
            'status_persetujuan' => ['required', Rule::in(['pending', 'disetujui', 'revisi'])],
            'catatan_instruktur' => ['nullable', 'string'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.unit_kerja' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $jurnal = Jurnal::create([
                'siswa_id'           => $data['siswa_id'],
                'hari_tanggal'       => $data['hari_tanggal'],
                'status_persetujuan' => $data['status_persetujuan'],
                'catatan_instruktur' => $data['catatan_instruktur'] ?? null,
                'disetujui_oleh'     => $data['status_persetujuan'] === 'disetujui' ? Auth::id() : null,
            ]);

            foreach ($request->input('items', []) as $row) {
                $unit = trim((string) ($row['unit_kerja'] ?? ''));
                if ($unit === '') {
                    continue;
                }
                $jurnal->items()->create(['unit_kerja' => $unit]);
            }
        });

        return back()->with('success', 'Jurnal berhasil ditambahkan.');
    }

    public function updateJurnal(Request $request, Jurnal $jurnal)
    {
        $data = $request->validate([
            'siswa_id'           => ['required', 'exists:users,id'],
            'hari_tanggal'       => ['required', 'date'],
            'status_persetujuan' => ['required', Rule::in(['pending', 'disetujui', 'revisi'])],
            'catatan_instruktur' => ['nullable', 'string'],
            'items'              => ['nullable', 'array'],
            'items.*.id'         => ['nullable', 'integer'],
            'items.*.unit_kerja' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $data, $jurnal) {
            $jurnal->update([
                'siswa_id'           => $data['siswa_id'],
                'hari_tanggal'       => $data['hari_tanggal'],
                'status_persetujuan' => $data['status_persetujuan'],
                'catatan_instruktur' => $data['catatan_instruktur'] ?? null,
                'disetujui_oleh'     => $data['status_persetujuan'] === 'disetujui'
                    ? ($jurnal->disetujui_oleh ?? Auth::id())
                    : null,
            ]);

            // Sinkronkan item (unit kerja). Foto/dokumentasi milik item lama tetap dipertahankan.
            $idDipakai = [];
            foreach ($request->input('items', []) as $row) {
                $unit = trim((string) ($row['unit_kerja'] ?? ''));

                if (! empty($row['id'])) {
                    $item = $jurnal->items()->find($row['id']);
                    if (! $item) {
                        continue;
                    }
                    if ($unit === '') { // dikosongkan = hapus item
                        if ($item->dokumentasi) {
                            Storage::disk('public')->delete($item->dokumentasi);
                        }
                        $item->delete();
                        continue;
                    }
                    $item->update(['unit_kerja' => $unit]);
                    $idDipakai[] = $item->id;
                } else {
                    if ($unit === '') {
                        continue;
                    }
                    $baru = $jurnal->items()->create(['unit_kerja' => $unit]);
                    $idDipakai[] = $baru->id;
                }
            }

            // Hapus item yang tidak dikirim lagi dari form
            $sisa = $jurnal->items()->whereNotIn('id', $idDipakai)->get();
            foreach ($sisa as $item) {
                if ($item->dokumentasi) {
                    Storage::disk('public')->delete($item->dokumentasi);
                }
                $item->delete();
            }
        });

        return back()->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroyJurnal(Jurnal $jurnal)
    {
        foreach ($jurnal->items as $item) {
            if ($item->dokumentasi) {
                Storage::disk('public')->delete($item->dokumentasi);
            }
        }
        $jurnal->items()->delete();
        $jurnal->delete();

        return back()->with('success', 'Jurnal berhasil dihapus.');
    }

    // ===================================================================
    // CATATAN KEGIATAN
    // ===================================================================
    public function catatan(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $approved = $request->get('approved', '');
        $kelas    = $request->get('kelas', '');
        $jurusan  = $request->get('jurusan', '');

        $catatan = CatatanKegiatan::query()
            ->with('user')
            ->when($q, fn ($query) => $query->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas,   fn ($query) => $query->whereHas('user', fn ($u) => $u->where('kelas', $kelas)))
            ->when($jurusan, fn ($query) => $query->whereHas('user', fn ($u) => $u->where('jurusan', $jurusan)))
            ->when($approved !== '', fn ($query) => $query->where('is_approved', $approved === '1'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $rekap = [
            'total'     => CatatanKegiatan::count(),
            'disetujui' => CatatanKegiatan::where('is_approved', true)->count(),
            'belum'     => CatatanKegiatan::where('is_approved', false)->count(),
        ];

        return view('admin.monitoring.catatan', array_merge(
            compact('catatan', 'q', 'approved', 'kelas', 'jurusan', 'rekap'),
            ['siswaList' => $this->siswaList()],
            $this->opsiFilter()
        ));
    }

    public function storeCatatan(Request $request)
    {
        $data = $request->validate([
            'user_id'              => ['required', 'exists:users,id'],
            'nama_pekerjaan'       => ['required', 'string', 'max:255'],
            'perencanaan_kegiatan' => ['nullable', 'string'],
            'pelaksanaan_kegiatan' => ['nullable', 'string'],
            'catatan_instruktur'   => ['nullable', 'string'],
        ]);
        $data['is_approved'] = $request->boolean('is_approved');

        CatatanKegiatan::create($data);

        return back()->with('success', 'Catatan kegiatan berhasil ditambahkan.');
    }

    public function updateCatatan(Request $request, CatatanKegiatan $catatan)
    {
        $data = $request->validate([
            'user_id'              => ['required', 'exists:users,id'],
            'nama_pekerjaan'       => ['required', 'string', 'max:255'],
            'perencanaan_kegiatan' => ['nullable', 'string'],
            'pelaksanaan_kegiatan' => ['nullable', 'string'],
            'catatan_instruktur'   => ['nullable', 'string'],
        ]);
        $data['is_approved'] = $request->boolean('is_approved');

        $catatan->update($data);

        return back()->with('success', 'Catatan kegiatan berhasil diperbarui.');
    }

    public function destroyCatatan(CatatanKegiatan $catatan)
    {
        $catatan->delete();

        return back()->with('success', 'Catatan kegiatan berhasil dihapus.');
    }

    // ===================================================================
    // ABSENSI
    // ===================================================================
    public function absensi(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $status  = $request->get('status', '');
        $tanggal = $request->get('tanggal', '');
        $kelas   = $request->get('kelas', '');
        $jurusan = $request->get('jurusan', '');

        $absensi = Absensi::query()
            ->with('siswa')
            ->when($q, fn ($query) => $query->whereHas('siswa', fn ($s) =>
                $s->where('name', 'like', "%{$q}%")->orWhere('nisn', 'like', "%{$q}%")))
            ->when($kelas,   fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('kelas', $kelas)))
            ->when($jurusan, fn ($query) => $query->whereHas('siswa', fn ($s) => $s->where('jurusan', $jurusan)))
            ->when($status,  fn ($query) => $query->where('status', $status))
            ->when($tanggal, fn ($query) => $query->whereDate('tanggal', $tanggal))
            ->orderByDesc('tanggal')
            ->paginate(15)
            ->withQueryString();

        $rekap = [
            'Hadir' => Absensi::where('status', 'Hadir')->count(),
            'Izin'  => Absensi::where('status', 'Izin')->count(),
            'Sakit' => Absensi::where('status', 'Sakit')->count(),
            'Alpha' => Absensi::where('status', 'Alpha')->count(),
        ];

        $tanggalDefault = $tanggal ?: date('Y-m-d');

        return view('admin.monitoring.absensi', array_merge(
            compact('absensi', 'q', 'status', 'tanggal', 'kelas', 'jurusan', 'rekap', 'tanggalDefault'),
            ['siswaList' => $this->siswaList()],
            $this->opsiFilter()
        ));
    }

    public function storeAbsensi(Request $request)
    {
        $data = $request->validate([
            'siswa_id'   => ['required', 'exists:users,id'],
            'tanggal'    => ['required', 'date'],
            'status'     => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
            'jam_masuk'  => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
        ]);

        $siswa = User::findOrFail($data['siswa_id']);

        Absensi::updateOrCreate(
            ['siswa_id' => $data['siswa_id'], 'tanggal' => $data['tanggal']],
            [
                'instruktur_id' => $siswa->instruktur_id ?? Auth::id(),
                'status'        => $data['status'],
                'jam_masuk'     => $data['jam_masuk'] ?? null,
                'jam_pulang'    => $data['jam_pulang'] ?? null,
            ]
        );

        return back()->with('success', 'Absensi berhasil disimpan.');
    }

    public function updateAbsensi(Request $request, Absensi $absensi)
    {
        $data = $request->validate([
            'siswa_id'   => ['required', 'exists:users,id'],
            'tanggal'    => ['required', 'date'],
            'status'     => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
            'jam_masuk'  => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
        ]);

        $absensi->update([
            'siswa_id'   => $data['siswa_id'],
            'tanggal'    => $data['tanggal'],
            'status'     => $data['status'],
            'jam_masuk'  => $data['jam_masuk'] ?? null,
            'jam_pulang' => $data['jam_pulang'] ?? null,
        ]);

        return back()->with('success', 'Absensi berhasil diperbarui.');
    }

    public function destroyAbsensi(Absensi $absensi)
    {
        $absensi->delete();

        return back()->with('success', 'Absensi berhasil dihapus.');
    }
}
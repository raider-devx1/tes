<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    /**
     * Normalisasi input jam ke format H:i:s (tanpa milidetik).
     * Menerima "H:i" atau "H:i:s"; hasil selalu "HH:MM:SS".
     */
    private function normalizeJam(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $value = substr(trim($value), 0, 8);
        $parts = explode(':', $value);

        $jam   = str_pad((string) (int) ($parts[0] ?? 0), 2, '0', STR_PAD_LEFT);
        $menit = str_pad((string) (int) ($parts[1] ?? 0), 2, '0', STR_PAD_LEFT);
        $detik = str_pad((string) (int) ($parts[2] ?? 0), 2, '0', STR_PAD_LEFT);

        return "{$jam}:{$menit}:{$detik}";
    }

    /**
     * Hitung status "jendela" absensi hari ini untuk seorang siswa.
     *
     * Jam masuk/pulang mengikuti JAM EFEKTIF siswa:
     *  - jam khusus industri (bila sudah disetujui guru), atau
     *  - jam global admin (tabel pengaturans) sebagai default.
     *
     * Durasi (batas menit) selalu global:
     *  - absensi_durasi_menit (default 30)
     */
    private function jendelaAbsensi(?User $siswa = null): array
    {
        $tz  = config('app.timezone', 'Asia/Makassar');
        $now = \Carbon\Carbon::now($tz);

        if ($siswa) {
            $jamMasuk  = $siswa->jamMasukEfektif();
            $jamPulang = $siswa->jamPulangEfektif();
        } else {
            $jamMasuk  = Pengaturan::ambil('absensi_jam_masuk', '08:00');
            $jamPulang = Pengaturan::ambil('absensi_jam_pulang', '16:00');
        }

        $durasi = (int) Pengaturan::ambil('absensi_durasi_menit', 30);
        if ($durasi <= 0) {
            $durasi = 30;
        }

        $tanggal     = $now->format('Y-m-d');
        $masukStart  = \Carbon\Carbon::parse($tanggal . ' ' . $jamMasuk, $tz);
        $masukEnd    = (clone $masukStart)->addMinutes($durasi);
        $pulangStart = \Carbon\Carbon::parse($tanggal . ' ' . $jamPulang, $tz);
        $pulangEnd   = (clone $pulangStart)->addMinutes($durasi);

        $fase    = 'tutup'; // masuk | pulang | tutup | bebas
        $terbuka = false;

        if ($now->betweenIncluded($masukStart, $masukEnd)) {
            $fase = 'masuk';
            $terbuka = true;
        } elseif ($now->betweenIncluded($pulangStart, $pulangEnd)) {
            $fase = 'pulang';
            $terbuka = true;
        }

        // Admin dapat MEMBUKA absensi secara global tanpa mengikuti jadwal jam.
        // Bila diaktifkan, absensi selalu terbuka (fase "bebas"); bila dimatikan,
        // absensi kembali mengikuti jadwal jam masuk/pulang di atas.
        // Buka-paksa global (semua siswa) ATAU buka-paksa per-siswa (kolom users.absensi_dibuka).
        $paksaGlobal = Pengaturan::ambil('absensi_paksa_buka', '0') === '1';
        $paksaSiswa  = $siswa ? (bool) $siswa->absensi_dibuka : false;
        $paksaBuka   = $paksaGlobal || $paksaSiswa;
        if ($paksaBuka && ! $terbuka) {
            $terbuka = true;
            $fase    = 'bebas';
        }

        return [
            'terbuka'      => $terbuka,
            'fase'         => $fase,
            'paksa'        => $paksaBuka,
            'durasi'       => $durasi,
            'jam_masuk'    => $masukStart->format('H:i'),
            'jam_pulang'   => $pulangStart->format('H:i'),
            'masuk_start'  => $masukStart,
            'masuk_end'    => $masukEnd,
            'pulang_start' => $pulangStart,
            'pulang_end'   => $pulangEnd,
            'now'          => $now,
        ];
    }

    /** Pesan kapan jendela absensi berikutnya dibuka. */
    private function pesanJadwal(array $jendela): string
    {
        $now = $jendela['now'];

        if ($now->lt($jendela['masuk_start'])) {
            return 'Absensi jam masuk dibuka pukul ' . $jendela['masuk_start']->format('H:i') . ' WITA.';
        }
        if ($now->lt($jendela['pulang_start'])) {
            return 'Absensi jam pulang dibuka pukul ' . $jendela['pulang_start']->format('H:i') . ' WITA.';
        }

        return 'Absensi berikutnya dibuka besok pukul ' . $jendela['masuk_start']->format('H:i') . ' WITA.';
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE: SISWA PKL (mengisi & melihat rekap kehadiran sendiri)
    |--------------------------------------------------------------------------
    */
    public function indexSiswa(Request $request)
    {
        $siswa = Auth::user();

        // Tandai otomatis Alpha untuk hari-hari yang jendela absensinya sudah
        // lewat tanpa absen (menggantikan scheduler/console).
        Absensi::sinkronkanAlpa($siswa);

        $query = Absensi::where('siswa_id', $siswa->id);

        if ($request->filled('bulan')) {
            $tanggal = \Carbon\Carbon::parse($request->bulan . '-01');
            $query->whereYear('tanggal', $tanggal->year)
                  ->whereMonth('tanggal', $tanggal->month);
        }

        // Rekap dihitung dari SELURUH data (bukan hanya halaman yang tampil),
        // sehingga tetap akurat meski daftar sudah dipaginate.
        $rekap = [
            'Hadir' => (clone $query)->where('status', 'Hadir')->count(),
            'Izin'  => (clone $query)->where('status', 'Izin')->count(),
            'Sakit' => (clone $query)->where('status', 'Sakit')->count(),
            'Alpha' => (clone $query)->where('status', 'Alpha')->count(),
        ];

        // Daftar absensi yang ditampilkan: dipaginate 15 baris per halaman.
        $absensis = $query->orderBy('tanggal', 'desc')
            ->paginate(15)
            ->withQueryString();

        $bulan = $request->bulan ?? date('Y-m');

        // Jendela absensi mengikuti jam EFEKTIF siswa ini.
        $jendela = $this->jendelaAbsensi($siswa);

        // Info jam untuk panel "Pengaturan Jam" milik siswa.
        $jamAdmin = [
            'masuk'  => Pengaturan::ambil('absensi_jam_masuk', '08:00'),
            'pulang' => Pengaturan::ambil('absensi_jam_pulang', '16:00'),
        ];

        // Absensi hari ini (untuk menentukan tampilan tombol).
        // Dicari langsung ke DB agar tetap ketemu walau tidak berada di halaman aktif.
        $absensiHariIni = Absensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $jendela['now']->format('Y-m-d'))
            ->first();

        return view('siswa.absensi.index', compact(
            'absensis', 'rekap', 'bulan', 'jendela', 'siswa', 'jamAdmin', 'absensiHariIni'
        ));
    }

    /**
     * Siswa melakukan absen hari ini melalui pop-up form.
     *
     * - Pilih status: Hadir (default) | Izin | Sakit.
     *   (Alpha TIDAK dapat dipilih manual; ditetapkan otomatis oleh sistem
     *    bila siswa tidak absen sampai batas waktu — lihat routes/console.php.)
     * - Foto bukti WAJIB:
     *     Hadir  -> foto bukti berada di tempat industri.
     *     Izin/Sakit -> foto bukti izin/sakit.
     * - Catatan OPSIONAL.
     *
     * Setelah tersimpan, absensi langsung berstatus "diajukan" ke guru.
     */
    public function storeSiswa(Request $request)
    {
        $siswa   = Auth::user();
        $jendela = $this->jendelaAbsensi($siswa);

        if (! $jendela['terbuka']) {
            return back()->with('error', 'Halaman absensi sedang tertutup. ' . $this->pesanJadwal($jendela));
        }

        $hariIni     = $jendela['now']->format('Y-m-d');
        $jamSekarang = $jendela['now']->format('H:i:s');

        $absensi = Absensi::firstOrNew([
            'siswa_id' => $siswa->id,
            'tanggal'  => $hariIni,
        ]);

        if ($absensi->exists && $absensi->status_validasi === 'disetujui') {
            return back()->with('error', 'Absensi hari ini sudah disetujui dan tidak dapat diubah.');
        }

        // Tahap "stempel pulang": sudah absen masuk (Hadir) & sedang fase pulang.
        $stempelPulang = $absensi->exists
            && $absensi->status === 'Hadir'
            && ! empty($absensi->jam_masuk)
            && in_array($jendela['fase'], ['pulang', 'bebas'], true)
            && $request->input('aksi') === 'pulang';

        if ($stempelPulang) {
            $absensi->jam_pulang = $this->normalizeJam($jamSekarang);
            $absensi->save();

            return back()->with('success', 'Jam pulang berhasil dicatat.');
        }

        // ---- Tahap absen utama: status + foto wajib + catatan opsional ----
        $status    = $request->input('status', 'Hadir');
        $labelFoto = $status === 'Hadir'
            ? 'Foto bukti berada di tempat industri wajib diunggah.'
            : 'Foto bukti izin/sakit wajib diunggah.';

        // Foto wajib bila belum ada foto tersimpan sebelumnya.
        $fotoRule = $absensi->foto_bukti ? 'nullable' : 'required';

        $validated = $request->validate([
            'status'             => ['required', Rule::in(['Hadir', 'Izin', 'Sakit'])],
            'catatan_instruktur' => ['nullable', 'string', 'max:1000'],
            'foto_bukti'         => [$fotoRule, 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ], [
            'foto_bukti.required' => $labelFoto,
            'foto_bukti.image'    => 'File harus berupa gambar.',
            'foto_bukti.mimes'    => 'Format foto harus jpeg, png, atau jpg.',
            'foto_bukti.max'      => 'Ukuran foto maksimal 2MB.',
        ]);

        if ($request->hasFile('foto_bukti')) {
            if ($absensi->foto_bukti) {
                Storage::disk('public')->delete($absensi->foto_bukti);
            }
            $absensi->foto_bukti = $request->file('foto_bukti')->store('bukti_fisik/absensi', 'public');
        }

        $absensi->status             = $validated['status'];
        $absensi->catatan_instruktur = $validated['catatan_instruktur'] ?? null;
        $absensi->status_validasi    = 'diajukan';

        if ($validated['status'] === 'Hadir') {
            if ($jendela['fase'] === 'masuk') {
                // Absen pada jendela MASUK.
                if (empty($absensi->jam_masuk)) {
                    $absensi->jam_masuk = $this->normalizeJam($jamSekarang);
                }
            } elseif ($jendela['fase'] === 'pulang') {
                // Absen pada jendela PULANG. Bila sebelumnya TIDAK absen masuk,
                // jam_masuk sengaja dibiarkan kosong sehingga otomatis ditandai
                // "Telat Masuk" (lihat accessor telat_masuk pada model Absensi).
                $absensi->jam_pulang = $this->normalizeJam($jamSekarang);
            } elseif ($jendela['fase'] === 'bebas') {
                // Absensi dibuka manual oleh admin (bebas waktu): absen pertama
                // mengisi jam masuk, absen berikutnya mengisi jam pulang.
                if (empty($absensi->jam_masuk)) {
                    $absensi->jam_masuk = $this->normalizeJam($jamSekarang);
                } else {
                    $absensi->jam_pulang = $this->normalizeJam($jamSekarang);
                }
            }
        } else {
            // Izin / Sakit tidak mencatat jam kerja.
            $absensi->jam_masuk  = null;
            $absensi->jam_pulang = null;
        }

        $absensi->save();

        return back()->with('success', 'Absensi berhasil diajukan ke Guru Pembimbing.');
    }

    /**
     * Siswa MENGAJUKAN jam masuk/pulang khusus (bila jam yang ditetapkan admin
     * tidak sesuai dengan template industri tempat PKL). Diajukan ke guru
     * pembimbing untuk divalidasi.
     */
    public function ajukanJamSiswa(Request $request)
    {
        $siswa = Auth::user();

        $validated = $request->validate([
            'jam_masuk_usulan'   => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'jam_pulang_usulan'  => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'catatan_jam_usulan' => ['nullable', 'string', 'max:500'],
        ], [
            'jam_masuk_usulan.required'  => 'Jam masuk usulan wajib diisi.',
            'jam_pulang_usulan.required' => 'Jam pulang usulan wajib diisi.',
            'jam_masuk_usulan.regex'     => 'Format jam masuk harus HH:MM.',
            'jam_pulang_usulan.regex'    => 'Format jam pulang harus HH:MM.',
        ]);

        $siswa->update([
            'jam_masuk_usulan'   => $this->normalizeJam($validated['jam_masuk_usulan']),
            'jam_pulang_usulan'  => $this->normalizeJam($validated['jam_pulang_usulan']),
            'catatan_jam_usulan' => $validated['catatan_jam_usulan'] ?? null,
            'status_jam_usulan'  => 'diajukan',
        ]);

        return back()->with('success', 'Usulan jam kerja industri berhasil diajukan ke guru pembimbing.');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE: GURU PEMBIMBING (validasi absensi & jam kerja)
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

    /**
     * Guru memvalidasi USULAN jam masuk/pulang dari siswa bimbingannya.
     * aksi = setuju  -> jam usulan diterapkan sebagai jam industri efektif.
     * aksi = tolak   -> usulan dibatalkan, siswa kembali memakai jam admin.
     */
    public function validasiJamByGuru(Request $request, $siswaId)
    {
        $siswa = User::where('id', $siswaId)->where('role', 'siswa_pkl')->firstOrFail();

        abort_unless(
            (int) $siswa->guru_id === (int) Auth::id(),
            403,
            'Akses ditolak: siswa ini bukan bimbingan Anda.'
        );

        $aksi = $request->input('aksi', 'setuju');

        if ($aksi === 'tolak') {
            $siswa->update([
                'status_jam_usulan'  => 'none',
                'jam_masuk_usulan'   => null,
                'jam_pulang_usulan'  => null,
                'catatan_jam_usulan' => null,
            ]);

            return back()->with('success', 'Usulan jam kerja siswa ditolak. Siswa kembali memakai jam dari admin.');
        }

        $siswa->update([
            'jam_masuk_industri'  => $siswa->jam_masuk_usulan,
            'jam_pulang_industri' => $siswa->jam_pulang_usulan,
            'status_jam_usulan'   => 'disetujui',
        ]);

        return back()->with('success', 'Usulan jam kerja disetujui dan diterapkan untuk siswa tersebut.');
    }

    /**
     * Guru mengubah SENDIRI jam masuk/pulang industri siswa bimbingannya
     * (tanpa harus menunggu usulan siswa).
     */
    public function updateJamByGuru(Request $request, $siswaId)
    {
        $siswa = User::where('id', $siswaId)->where('role', 'siswa_pkl')->firstOrFail();

        abort_unless(
            (int) $siswa->guru_id === (int) Auth::id(),
            403,
            'Akses ditolak: siswa ini bukan bimbingan Anda.'
        );

        $validated = $request->validate([
            'jam_masuk_industri'  => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'jam_pulang_industri' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
        ], [
            'jam_masuk_industri.required'  => 'Jam masuk wajib diisi.',
            'jam_pulang_industri.required' => 'Jam pulang wajib diisi.',
            'jam_masuk_industri.regex'     => 'Format jam masuk harus HH:MM.',
            'jam_pulang_industri.regex'    => 'Format jam pulang harus HH:MM.',
        ]);

        $siswa->update([
            'jam_masuk_industri'  => $this->normalizeJam($validated['jam_masuk_industri']),
            'jam_pulang_industri' => $this->normalizeJam($validated['jam_pulang_industri']),
            'status_jam_usulan'   => 'disetujui',
            'jam_masuk_usulan'    => null,
            'jam_pulang_usulan'   => null,
        ]);

        return back()->with('success', 'Jam kerja industri siswa berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE: SISWA — edit / hapus baris draft (mis. setelah ditolak guru)
    |--------------------------------------------------------------------------
    */
    public function updateSiswa(Request $request, $id)
    {
        $absensi = Absensi::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();

        if ($absensi->status_validasi !== 'draft') {
            return back()->with('error', 'Absensi yang sudah diajukan/disetujui tidak dapat diubah.');
        }

        $validated = $request->validate([
            'tanggal'    => ['required', 'date'],
            'status'     => ['required', Rule::in(['Hadir', 'Izin', 'Sakit', 'Alpha'])],
            'jam_masuk'  => ['nullable', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'jam_pulang' => ['nullable', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
        ], [
            'jam_masuk.regex'  => 'Format jam masuk harus HH:MM atau HH:MM:SS.',
            'jam_pulang.regex' => 'Format jam pulang harus HH:MM atau HH:MM:SS.',
        ]);

        $absensi->update([
            'tanggal'    => $validated['tanggal'],
            'status'     => $validated['status'],
            'jam_masuk'  => $this->normalizeJam($validated['jam_masuk'] ?? null),
            'jam_pulang' => $this->normalizeJam($validated['jam_pulang'] ?? null),
        ]);

        return back()->with('success', 'Absensi berhasil diperbarui.');
    }

    public function destroySiswa($id)
    {
        $absensi = Absensi::where('id', $id)->where('siswa_id', Auth::id())->firstOrFail();

        if ($absensi->status_validasi !== 'draft') {
            return back()->with('error', 'Absensi yang sudah diajukan/disetujui tidak dapat dihapus.');
        }

        if ($absensi->foto_bukti) {
            Storage::disk('public')->delete($absensi->foto_bukti);
        }

        $absensi->delete();

        return back()->with('success', 'Absensi berhasil dihapus.');
    }
}

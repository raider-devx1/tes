<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Pengaturan;
use App\Models\User;
use App\Support\ImageCompressor;
use App\Support\TandaTangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AbsensiController extends Controller
{
    /**
     * Batas ukuran berkas foto absensi yang boleh diunggah siswa (dalam KB).
     * 3072 KB = 3 MB. Dipakai pada rule validasi "max:" untuk foto absensi
     * (absen harian maupun ganti foto yang ditolak guru).
     */
    private const MAKS_UKURAN_FOTO_KB = 3072;

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

        // Jendela terjadwal per fase.
        $masukJadwal  = $now->betweenIncluded($masukStart, $masukEnd);
        $pulangJadwal = $now->betweenIncluded($pulangStart, $pulangEnd);

        // Admin dapat MEMBUKA absensi tanpa mengikuti jadwal, kini TERPISAH untuk
        // fase MASUK dan fase PULANG. Sumber: flag global (tabel pengaturans)
        // ATAU per-siswa (kolom users). Bila hanya satu fase yang dibuka-paksa,
        // fase lainnya tetap mengikuti jadwal jam.
        // Kompatibilitas mundur: flag lama absensi_paksa_buka / users.absensi_dibuka
        // dianggap membuka KEDUA fase (masuk & pulang).
        //
        // BATAS WAKTU: pembukaan manual boleh diberi tenggat, baik per-siswa
        // (kolom users.absensi_dibuka_sampai) maupun global (kunci pengaturan
        // absensi_paksa_buka_<fase>_sampai). Begitu tenggat lewat, flag yang
        // bersangkutan diabaikan sehingga absensi menutup sendiri tanpa perlu
        // menekan tombol "Tutup Absensi". Nilai kosong/null = tanpa batas waktu.
        $bukaSiswaAktif = $siswa ? ! $siswa->bukaManualKedaluwarsa() : false;
        $bukaSampai     = ($siswa && $bukaSiswaAktif) ? $siswa->absensi_dibuka_sampai : null;

        $legacyGlobal = Pengaturan::ambil('absensi_paksa_buka', '0') === '1';
        $legacySiswa  = $bukaSiswaAktif && (bool) $siswa->absensi_dibuka;

        // Tenggat buka-paksa global per fase (null = tanpa batas waktu).
        $paksaMasukAktif  = Pengaturan::paksaBukaAktif('masuk');
        $paksaPulangAktif = Pengaturan::paksaBukaAktif('pulang');

        $masukPaksa = $legacyGlobal || $legacySiswa
            || $paksaMasukAktif
            || ($bukaSiswaAktif && (bool) $siswa->absensi_dibuka_masuk);

        $pulangPaksa = $legacyGlobal || $legacySiswa
            || $paksaPulangAktif
            || ($bukaSiswaAktif && (bool) $siswa->absensi_dibuka_pulang);

        // Tenggat terdekat yang sedang menahan absensi tetap terbuka. Dipakai
        // hanya untuk info di layar siswa; null berarti tanpa batas waktu.
        $paksaSampai = collect([
            $paksaMasukAktif ? Pengaturan::tenggatPaksa('masuk') : null,
            $paksaPulangAktif ? Pengaturan::tenggatPaksa('pulang') : null,
        ])->filter()->min();

        $masukTerbuka  = $masukJadwal  || $masukPaksa;
        $pulangTerbuka = $pulangJadwal || $pulangPaksa;

        if ($masukTerbuka && $pulangTerbuka) {
            $fase = 'bebas';  $terbuka = true;
        } elseif ($masukTerbuka) {
            $fase = 'masuk';  $terbuka = true;
        } elseif ($pulangTerbuka) {
            $fase = 'pulang'; $terbuka = true;
        } else {
            $fase = 'tutup';  $terbuka = false;
        }

        $paksaBuka = $masukPaksa || $pulangPaksa;

        // TANGGAL MERAH (didaftarkan admin di Pengaturan > Tanggal Merah).
        // Pada hari itu absensi TIDAK PERLU diisi dan tidak pernah dihitung
        // Alpha, sehingga jendela absensi dipaksa TERTUTUP sepanjang hari.
        //
        // PENGECUALIAN: bila admin memang MEMBUKA absensi secara manual (buka
        // jam masuk dan/atau jam pulang, global maupun per-NISN), berarti
        // sekolah sengaja mengadakan kegiatan pada hari libur tersebut.
        // Pembukaan manual selalu menang atas aturan tanggal merah.
        $namaLibur  = HariLibur::namaLibur($tanggal);
        $liburAktif = $namaLibur !== null && ! $paksaBuka;

        if ($liburAktif) {
            $fase          = 'libur';
            $terbuka       = false;
            $masukTerbuka  = false;
            $pulangTerbuka = false;
        }

        return [
            'terbuka'      => $terbuka,
            'fase'         => $fase,
            'paksa'        => $paksaBuka,
            'libur'        => $liburAktif,
            'libur_nama'   => $namaLibur,
            'masuk_paksa'  => $masukPaksa,
            'pulang_paksa' => $pulangPaksa,
            'masuk_terbuka'  => $masukTerbuka,
            'pulang_terbuka' => $pulangTerbuka,
            'durasi'       => $durasi,
            'buka_sampai'  => $bukaSampai,
            'paksa_sampai' => $paksaSampai,
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

        // Tanggal merah: tidak ada jendela absensi yang perlu ditunggu.
        if (! empty($jendela['libur'])) {
            return 'Hari ini tanggal merah (' . $jendela['libur_nama']
                . '). Absensi tidak perlu diisi dan Anda tidak dihitung Alpha.';
        }

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

        if ($absensiHariIni) {
            $absensiHariIni->setRelation('siswa', $siswa);
        }

        // ---- FOTO DITOLAK GURU: wajib diganti sebelum absen pulang ----
        // Bisa saja yang ditolak adalah absensi hari SEBELUMNYA (guru baru
        // memeriksa keesokan harinya), jadi dicari yang paling baru ditolak
        // dan masih dalam batas waktu penggantian.
        $absensiDitolak = Absensi::where('siswa_id', $siswa->id)
            ->where('foto_ditolak', true)
            ->orderByDesc('tanggal')
            ->get()
            ->each(fn ($a) => $a->setRelation('siswa', $siswa))
            ->first(fn ($a) => $a->perlu_ganti_foto);

        $batasGantiFoto = $absensiDitolak?->batasGantiFoto();

        // Pilihan dropdown "Hari Awal" & "Hari Akhir" pada modal pengajuan
        // jam + hari kerja industri (Senin s.d. Minggu).
        $daftarHari = User::daftarHari();

        return view('siswa.absensi.index', compact(
            'absensis', 'rekap', 'bulan', 'jendela', 'siswa', 'jamAdmin', 'absensiHariIni',
            'absensiDitolak', 'batasGantiFoto', 'daftarHari'
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

        // TANGGAL MERAH: hari libur nasional / cuti bersama yang didaftarkan
        // admin. Absensi tidak perlu diisi dan hari itu tidak dihitung Alpha.
        // Aturan ini otomatis mengalah bila admin membuka absensi secara
        // manual (lihat jendelaAbsensi()), jadi di sini cukup memeriksa flag.
        if (! empty($jendela['libur'])) {
            return back()->with('error', 'Hari ini tanggal merah ('
                . $jendela['libur_nama'] . '). Absensi tidak perlu diisi dan Anda tidak dihitung Alpha.');
        }

        // JADWAL HARI KERJA: hari libur (Minggu, dan Sabtu bila jadwal siswa
        // hanya Senin-Jumat) normalnya tidak boleh diisi absensi. Hari itu
        // sengaja dibiarkan kosong dan juga tidak ditandai Alpha otomatis.
        //
        // PENGECUALIAN: bila ADMIN sendiri yang MEMBUKA absensi (buka jam masuk
        // dan/atau jam pulang, baik global "semua siswa" maupun per-NISN),
        // maka absensi TETAP BOLEH diisi walau hari Sabtu/Minggu. Admin dianggap
        // sengaja mengadakan kegiatan di hari libur tersebut.
        if (! $siswa->adalahHariKerja($jendela['now']) && ! $jendela['paksa']) {
            return back()->with('error', 'Hari ini bukan hari kerja absensi Anda (jadwal: '
                . $siswa->labelHariKerja() . '). Absensi tidak perlu diisi, kecuali admin membuka absensi.');
        }

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

        // ---- PALANG FOTO DITOLAK ----
        // Selama masih ada absensi yang fotonya DITOLAK guru dan belum diganti,
        // siswa tidak boleh melakukan absensi apa pun (termasuk ABSEN PULANG).
        // Yang perlu dilakukan hanyalah mengganti foto lewat tombol
        // "Ganti Foto Absensi" pada halaman absensi.
        $ditolak = Absensi::where('siswa_id', $siswa->id)
            ->where('foto_ditolak', true)
            ->orderByDesc('tanggal')
            ->first();

        if ($ditolak) {
            $ditolak->setRelation('siswa', $siswa);
            $batas = $ditolak->batasGantiFoto();

            return back()->with('error',
                'Foto absensi tanggal ' . $ditolak->tanggal->format('d/m/Y') . ' ditolak guru pembimbing. '
                . 'Anda belum bisa absen (termasuk absen pulang) sebelum mengganti foto tersebut. '
                . 'Gunakan tombol "Ganti Foto Absensi", batas waktu sampai '
                . ($batas ? $batas->format('d/m/Y H:i') . ' WITA.' : 'jendela jam pulang berakhir.')
            );
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

        // ---- Tahap absen utama: status + foto unggahan wajib + catatan opsional ----
        //
        // CATATAN PENTING (perubahan alur):
        // Pengambilan foto LANGSUNG DARI KAMERA sudah DIGANTI dengan UNGGAH FOTO.
        // Siswa mengunggah berkas foto lewat komponen <x-upload-foto-absensi />
        // pada field "foto_bukti" (form memakai enctype="multipart/form-data").
        //
        // Aturan berkas: gambar JPG/JPEG/PNG/WEBP, ukuran MAKSIMAL 3 MB
        // (self::MAKS_UKURAN_FOTO_KB). Setelah lolos validasi, foto tetap
        // diperkecil & dikompres otomatis oleh ImageCompressor::store().
        //
        // Foto HANYA diminta pada absen JAM MASUK, yaitu saat baris absensi
        // hari ini belum memiliki foto. Absen pulang tidak meminta foto lagi.
        $status    = $request->input('status', 'Hadir');
        $labelFoto = $status === 'Hadir'
            ? 'Foto wajah dengan latar belakang tempat industri wajib diunggah.'
            : 'Foto bukti izin/sakit wajib diunggah.';

        // Foto wajib bila belum ada foto tersimpan sebelumnya.
        $fotoRule = $absensi->foto_bukti ? 'nullable' : 'required';

        $request->validate([
            'status'             => ['required', Rule::in(['Hadir', 'Izin', 'Sakit'])],
            'catatan_instruktur' => ['nullable', 'string', 'max:1000'],
            'foto_bukti'         => [
                $fotoRule,
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:' . self::MAKS_UKURAN_FOTO_KB,
            ],
        ], [
            'foto_bukti.required' => $labelFoto,
            'foto_bukti.file'     => 'Foto gagal diunggah. Silakan pilih ulang berkas fotonya.',
            'foto_bukti.image'    => 'Berkas yang diunggah harus berupa gambar (JPG, JPEG, PNG, atau WEBP).',
            'foto_bukti.mimes'    => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto_bukti.max'      => 'Ukuran foto maksimal 3 MB. Silakan pilih foto lain yang ukurannya lebih kecil.',
        ]);

        $validated = [
            'status'             => $request->input('status'),
            'catatan_instruktur' => $request->input('catatan_instruktur'),
        ];

        if ($request->hasFile('foto_bukti')) {
            $pathFoto = ImageCompressor::store($request->file('foto_bukti'), 'bukti_fisik/absensi');

            if (! $pathFoto) {
                return back()->with('error', 'Foto gagal disimpan. Silakan unggah ulang foto lalu kirim kembali.');
            }

            if ($absensi->foto_bukti) {
                Storage::disk('public')->delete($absensi->foto_bukti);
            }

            $absensi->foto_bukti = $pathFoto;
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
     * Siswa MENGAJUKAN jam masuk/pulang + HARI KERJA khusus (bila jam/hari yang
     * ditetapkan admin tidak sesuai dengan industri tempat PKL). Jam dan hari
     * kerja diajukan BERSAMAAN dalam satu pengajuan, lalu divalidasi guru
     * pembimbing (atau admin).
     *
     * Hari kerja dipilih siswa lewat dua dropdown: hari awal & hari akhir
     * (Senin s.d. Minggu), disimpan sebagai "{hari_awal}_{hari_akhir}" pada
     * kolom users.hari_kerja_usulan sampai disetujui.
     */
    public function ajukanJamSiswa(Request $request)
    {
        $siswa = Auth::user();

        $pilihanHari = implode(',', array_keys(User::daftarHari()));

        $validated = $request->validate([
            'jam_masuk_usulan'   => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'jam_pulang_usulan'  => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'hari_awal_usulan'   => ['required', 'string', 'in:' . $pilihanHari],
            'hari_akhir_usulan'  => ['required', 'string', 'in:' . $pilihanHari],
            'catatan_jam_usulan' => ['nullable', 'string', 'max:500'],
        ], [
            'jam_masuk_usulan.required'  => 'Jam masuk usulan wajib diisi.',
            'jam_pulang_usulan.required' => 'Jam pulang usulan wajib diisi.',
            'jam_masuk_usulan.regex'     => 'Format jam masuk harus HH:MM.',
            'jam_pulang_usulan.regex'    => 'Format jam pulang harus HH:MM.',
            'hari_awal_usulan.required'  => 'Hari awal kerja wajib dipilih.',
            'hari_akhir_usulan.required' => 'Hari akhir kerja wajib dipilih.',
            'hari_awal_usulan.in'        => 'Pilihan hari awal tidak dikenali.',
            'hari_akhir_usulan.in'       => 'Pilihan hari akhir tidak dikenali.',
        ]);

        $hariKerjaUsulan = User::gabungHariKerja(
            $validated['hari_awal_usulan'],
            $validated['hari_akhir_usulan']
        );

        $siswa->update([
            'jam_masuk_usulan'   => $this->normalizeJam($validated['jam_masuk_usulan']),
            'jam_pulang_usulan'  => $this->normalizeJam($validated['jam_pulang_usulan']),
            'hari_kerja_usulan'  => $hariKerjaUsulan,
            'catatan_jam_usulan' => $validated['catatan_jam_usulan'] ?? null,
            'status_jam_usulan'  => 'diajukan',
        ]);

        return back()->with('success', 'Usulan jam kerja industri & hari kerja ('
            . User::labelJadwal($hariKerjaUsulan) . ') berhasil diajukan ke guru pembimbing.');
    }

    /**
     * SISWA MENGGANTI FOTO ABSENSI YANG DITOLAK GURU.
     *
     * Aturan penting:
     *  - SELURUH informasi absensi TETAP: tanggal, status (Hadir/Izin/Sakit),
     *    jam masuk, dan jam pulang TIDAK diubah sama sekali.
     *    Yang diperbarui HANYA fotonya.
     *  - Batas waktu mengganti foto: sampai jendela JAM PULANG berakhir
     *    (lihat Absensi::batasGantiFoto()).
     *  - Selama foto sudah diganti sebelum batas waktu, absensi TIDAK menjadi
     *    Alpha. Setelah diganti, absensi otomatis diajukan ulang ke guru.
     */
    public function gantiFotoSiswa(Request $request, $id)
    {
        $siswa = Auth::user();

        $absensi = Absensi::where('id', $id)
            ->where('siswa_id', $siswa->id)
            ->firstOrFail();

        $absensi->setRelation('siswa', $siswa);

        if (! $absensi->foto_ditolak) {
            return back()->with('error', 'Absensi ini tidak sedang ditolak, jadi tidak perlu mengganti foto.');
        }

        $batas = $absensi->batasGantiFoto();
        $now   = \Carbon\Carbon::now(config('app.timezone', 'Asia/Makassar'));

        if ($batas && $now->gt($batas)) {
            return back()->with('error',
                'Batas waktu mengganti foto sudah lewat (' . $batas->format('d/m/Y H:i') . ' WITA). '
                . 'Absensi ini otomatis ditandai Alpha. Silakan hubungi guru pembimbing Anda.'
            );
        }

        // Foto pengganti DIUNGGAH (bukan lagi hasil jepretan kamera langsung).
        // Aturan: gambar JPG/JPEG/PNG/WEBP, maksimal 3 MB.
        $request->validate([
            'foto_bukti' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:' . self::MAKS_UKURAN_FOTO_KB,
            ],
        ], [
            'foto_bukti.required' => 'Foto wajah dengan latar belakang tempat industri wajib diunggah.',
            'foto_bukti.file'     => 'Foto gagal diunggah. Silakan pilih ulang berkas fotonya.',
            'foto_bukti.image'    => 'Berkas yang diunggah harus berupa gambar (JPG, JPEG, PNG, atau WEBP).',
            'foto_bukti.mimes'    => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto_bukti.max'      => 'Ukuran foto maksimal 3 MB. Silakan pilih foto lain yang ukurannya lebih kecil.',
        ]);

        $pathFoto = ImageCompressor::store($request->file('foto_bukti'), 'bukti_fisik/absensi');

        if (! $pathFoto) {
            return back()->with('error', 'Foto gagal disimpan. Silakan unggah ulang foto lalu kirim kembali.');
        }

        // Foto lama dihapus supaya tidak menumpuk di penyimpanan hosting.
        if ($absensi->foto_bukti) {
            Storage::disk('public')->delete($absensi->foto_bukti);
        }

        // forceFill dipakai agar hanya kolom-kolom di bawah ini yang tersentuh.
        // Kolom status, tanggal, jam_masuk, dan jam_pulang sengaja TIDAK ikut.
        // catatan_penolakan dibiarkan sebagai riwayat alasan penolakan.
        $absensi->forceFill([
            'foto_bukti'      => $pathFoto,
            'foto_ditolak'    => false,
            'foto_diganti_at' => now(),
            'status_validasi' => 'diajukan',
        ])->save();

        return back()->with('success',
            'Foto absensi tanggal ' . $absensi->tanggal->format('d/m/Y') . ' berhasil diperbarui dan diajukan ulang '
            . 'ke guru pembimbing. Data jam absensi Anda tetap tersimpan dan tidak dihitung Alpha.'
        );
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
            // ---- MENOLAK FOTO ABSENSI ----
            // Guru wajib menuliskan alasan penolakan pada pop-up konfirmasi.
            //
            // PENTING: absensi TIDAK dihapus dan datanya TIDAK diubah.
            // Tanggal, status, jam masuk, dan jam pulang TETAP seperti semula.
            // Siswa hanya diminta MENGGANTI FOTO sampai jendela jam pulang
            // berakhir, dan tidak akan dihitung Alpha selama foto sudah diganti.
            $validated = $request->validate([
                'catatan_penolakan' => ['required', 'string', 'min:5', 'max:1000'],
            ], [
                'catatan_penolakan.required' => 'Catatan alasan penolakan wajib diisi agar siswa tahu apa yang harus diperbaiki.',
                'catatan_penolakan.min'      => 'Catatan penolakan minimal 5 karakter.',
                'catatan_penolakan.max'      => 'Catatan penolakan maksimal 1000 karakter.',
            ]);

            // Paraf lama (bila ada) ikut dihapus supaya tidak ada tanda tangan
            // menggantung pada data yang belum disetujui.
            TandaTangan::hapus($absensi->ttd_guru);

            // Foto lama SENGAJA tidak dihapus di sini: guru masih bisa melihat
            // foto yang ditolak sampai siswa menggantinya.
            $absensi->forceFill([
                'status_validasi'      => 'draft',
                'validated_by_guru_id' => null,
                'validated_at'         => null,
                'ttd_guru'             => null,
                'ttd_guru_nama'        => null,
                'ttd_guru_signed_at'   => null,

                'foto_ditolak'         => true,
                'catatan_penolakan'    => $validated['catatan_penolakan'],
                'foto_ditolak_at'      => now(),
                'foto_ditolak_by'      => Auth::id(),
                'foto_diganti_at'      => null,
            ])->save();

            $batas = $absensi->batasGantiFoto();

            return back()->with('success',
                'Absensi ditolak. Data absensi (status & jam) tetap tersimpan, siswa hanya diminta mengganti foto'
                . ($batas ? ' paling lambat ' . $batas->format('d/m/Y H:i') . ' WITA.' : '.')
            );
        }

        if ($aksi === 'batal') {
            // MEMBATALKAN VALIDASI yang sudah terlanjur dilakukan guru.
            // Absensi dikembalikan ke status "diajukan" (menunggu validasi), bukan draft,
            // supaya guru bisa langsung memvalidasi & menandatangani ulang tanpa perlu
            // menunggu siswa mengajukan lagi. Paraf lama dihapus dari penyimpanan.
            if ($absensi->status_validasi !== 'disetujui') {
                return back()->with('error', 'Absensi ini belum divalidasi, jadi tidak ada validasi yang bisa dibatalkan.');
            }

            TandaTangan::hapus($absensi->ttd_guru);

            $absensi->update([
                'status_validasi'      => 'diajukan',
                'validated_by_guru_id' => null,
                'validated_at'         => null,
                'ttd_guru'             => null,
                'ttd_guru_nama'        => null,
                'ttd_guru_signed_at'   => null,
            ]);

            return back()->with('success', 'Validasi dibatalkan. Paraf digital dihapus dan absensi kembali menunggu validasi.');
        }

        // MENYETUJUI: tanda tangan digital guru WAJIB ada.
        //
        // Guru punya DUA cara membubuhkan tanda tangan, dipilih di halaman absensi:
        //   sumber_ttd = 'tersimpan' -> pakai berkas yang sudah diunggah lewat tombol
        //                               "Tanda Tangan Saya" (kolom users.ttd_tersimpan)
        //   sumber_ttd = 'canvas'    -> menggores di kanvas, dikirim sebagai data URL PNG
        //
        // Nilai selain 'tersimpan' selalu dianggap 'canvas' supaya perilaku lama
        // tetap jalan walaupun form tidak mengirim field ini sama sekali.
        $sumberTtd = $request->input('sumber_ttd') === 'tersimpan' ? 'tersimpan' : 'canvas';
        $guru      = Auth::user();

        if ($sumberTtd === 'tersimpan') {
            if (! $guru || ! $guru->punyaTtdTersimpan()) {
                return back()->withErrors([
                    'ttd_guru' => 'Anda belum punya tanda tangan tersimpan. Unggah dulu lewat tombol "Tanda Tangan Saya" di halaman ini, atau tanda tangani langsung di kanvas.',
                ]);
            }

            // Berkasnya DISALIN, bukan ditautkan. Jadi kalau nanti guru mengganti
            // tanda tangan tersimpannya, absensi yang sudah tervalidasi tidak
            // ikut berubah dan arsip cetakannya tetap utuh.
            $path = TandaTangan::salin($guru->ttd_tersimpan, 'ttd/absensi/guru');

            if (! $path) {
                return back()
                    ->with('error', 'Tanda tangan tersimpan gagal dipakai. Coba unggah ulang tanda tangan Anda, atau tanda tangani di kanvas.')
                    ->withErrors(['ttd_guru' => 'Tanda tangan tersimpan tidak bisa dibaca.']);
            }
        } else {
            $validated = $request->validate([
                'ttd_guru' => ['required', 'string', function ($atribut, $nilai, $gagal) {
                    if (! TandaTangan::valid($nilai)) {
                        $gagal('Tanda tangan digital tidak terbaca. Mohon tanda tangani ulang pada kotak yang tersedia.');
                    }
                }],
            ], [
                'ttd_guru.required' => 'Tanda tangan digital wajib dibubuhkan sebelum absensi divalidasi. Atau pilih "Pakai tanda tangan tersimpan".',
            ]);

            $path = TandaTangan::simpan($validated['ttd_guru'], 'ttd/absensi/guru');

            if (! $path) {
                return back()
                    ->with('error', 'Tanda tangan digital gagal disimpan. Mohon ulangi tanda tangan lalu kirim kembali.')
                    ->withErrors(['ttd_guru' => 'Tanda tangan digital gagal disimpan.']);
            }
        }

        // Ganti paraf lama bila absensi ini pernah divalidasi lalu diajukan ulang.
        TandaTangan::hapus($absensi->ttd_guru);

        $absensi->update([
            'status_validasi'      => 'disetujui',
            'validated_by_guru_id' => Auth::id(),
            'validated_at'         => now(),
            'ttd_guru'             => $path,
            'ttd_guru_nama'        => Auth::user()->name ?? null,
            'ttd_guru_signed_at'   => now(),
        ]);

        return back()->with('success', $sumberTtd === 'tersimpan'
            ? 'Absensi berhasil divalidasi memakai tanda tangan tersimpan Anda.'
            : 'Absensi berhasil divalidasi dan ditandatangani secara digital.');
    }

    /**
     * Guru memvalidasi USULAN jam masuk/pulang + HARI KERJA dari siswa
     * bimbingannya (satu pengajuan berisi keduanya).
     * aksi = setuju  -> jam & hari usulan diterapkan sebagai jadwal efektif.
     * aksi = tolak   -> usulan dibatalkan, siswa kembali memakai jadwal admin.
     */
    public function validasiJamByGuru(Request $request, $siswaId)
    {
        $siswa = User::where('id', $siswaId)->siswa()->firstOrFail();

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
                // Usulan HARI kerja ikut dibatalkan; jadwal lama tetap berlaku.
                'hari_kerja_usulan'  => null,
            ]);

            return back()->with('success', 'Usulan jam & hari kerja siswa ditolak. Siswa kembali memakai jadwal sebelumnya.');
        }

        // Hari kerja hanya ditimpa bila siswa memang mengusulkannya.
        $hariUsulan = $siswa->punyaUsulanHariKerja()
            ? (string) $siswa->hari_kerja_usulan
            : null;

        $siswa->update([
            'jam_masuk_industri'  => $siswa->jam_masuk_usulan,
            'jam_pulang_industri' => $siswa->jam_pulang_usulan,
            'status_jam_usulan'   => 'disetujui',
            'hari_kerja'          => $hariUsulan ?? $siswa->hari_kerja,
            'hari_kerja_usulan'   => null,
        ]);

        return back()->with('success', $hariUsulan === null
            ? 'Usulan jam kerja disetujui dan diterapkan untuk siswa tersebut.'
            : 'Usulan jam & hari kerja disetujui. Jadwal siswa kini '
                . User::labelJadwal($hariUsulan) . ' (' . User::keteranganJadwal($hariUsulan) . ').');
    }

    /**
     * Guru mengubah SENDIRI jam masuk/pulang + hari kerja industri siswa
     * bimbingannya (tanpa harus menunggu usulan siswa).
     *
     * Hari kerja opsional: bila dropdown hari awal/akhir tidak dikirim, jadwal
     * hari kerja siswa dibiarkan seperti sebelumnya.
     */
    public function updateJamByGuru(Request $request, $siswaId)
    {
        $siswa = User::where('id', $siswaId)->siswa()->firstOrFail();

        abort_unless(
            (int) $siswa->guru_id === (int) Auth::id(),
            403,
            'Akses ditolak: siswa ini bukan bimbingan Anda.'
        );

        $pilihanHari = implode(',', array_keys(User::daftarHari()));

        $validated = $request->validate([
            'jam_masuk_industri'  => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'jam_pulang_industri' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'hari_awal'           => ['nullable', 'string', 'in:' . $pilihanHari],
            'hari_akhir'          => ['nullable', 'string', 'in:' . $pilihanHari],
        ], [
            'jam_masuk_industri.required'  => 'Jam masuk wajib diisi.',
            'jam_pulang_industri.required' => 'Jam pulang wajib diisi.',
            'jam_masuk_industri.regex'     => 'Format jam masuk harus HH:MM.',
            'jam_pulang_industri.regex'    => 'Format jam pulang harus HH:MM.',
            'hari_awal.in'                 => 'Pilihan hari awal tidak dikenali.',
            'hari_akhir.in'                => 'Pilihan hari akhir tidak dikenali.',
        ]);

        $hariKerja = User::gabungHariKerja(
            $validated['hari_awal'] ?? null,
            $validated['hari_akhir'] ?? null
        );

        $data = [
            'jam_masuk_industri'  => $this->normalizeJam($validated['jam_masuk_industri']),
            'jam_pulang_industri' => $this->normalizeJam($validated['jam_pulang_industri']),
            'status_jam_usulan'   => 'disetujui',
            'jam_masuk_usulan'    => null,
            'jam_pulang_usulan'   => null,
        ];

        if ($hariKerja !== null) {
            $data['hari_kerja']        = $hariKerja;
            $data['hari_kerja_usulan'] = null;   // usulan siswa selesai ditangani
        }

        $siswa->update($data);

        return back()->with('success', 'Jam kerja industri siswa berhasil diperbarui'
            . ($hariKerja !== null
                ? '. Hari kerja: ' . User::labelJadwal($hariKerja) . ' (' . User::keteranganJadwal($hariKerja) . ').'
                : '.'));
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

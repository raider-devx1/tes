<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\User;
use App\Support\ImageCompressor;
use App\Support\TandaTangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class NilaiController extends Controller
{
    /**
     * Hitung rata-rata akhir (0-100) dari 6 komponen penilaian guru.
     * Mengembalikan null bila belum semua komponen terisi.
     */
    private function hitungRataRata(Nilai $nilai): ?float
    {
        $daftarSkor = [
            $nilai->skor_soft_skill,
            $nilai->skor_hard_skill,
            $nilai->skor_pengembangan,
            $nilai->skor_kewirausahaan,
            $nilai->skor_laporan,
            $nilai->skor_presentasi,
        ];

        if (in_array(null, $daftarSkor, true)) {
            return null;
        }

        return round(array_sum($daftarSkor) / count($daftarSkor), 2);
    }

    /* ===================== SISWA PKL ===================== */
    public function indexSiswa()
    {
        $nilai = Nilai::where('user_id', Auth::id())
            ->with(['guru', 'user.perusahaan'])
            ->first();

        return view('siswa.nilai.index', compact('nilai'));
    }

    /* ===================== GURU PEMBIMBING ===================== */
    public function indexGuru(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $status = $request->get('status');

        $rekapQuery = User::siswaBerjalan()
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif');

        $totalSiswa = (clone $rekapQuery)->count();

        // Sudah dinilai LENGKAP = 6 komponen terisi semua
        $sudahDinilai = (clone $rekapQuery)
            ->whereHas('nilai', fn ($n) => $n
                ->whereNotNull('skor_soft_skill')
                ->whereNotNull('skor_hard_skill')
                ->whereNotNull('skor_pengembangan')
                ->whereNotNull('skor_kewirausahaan')
                ->whereNotNull('skor_laporan')
                ->whereNotNull('skor_presentasi'))
            ->count();

        $rekap = [
            'total'         => $totalSiswa,
            'sudah_dinilai' => $sudahDinilai,
            'belum_dinilai' => $totalSiswa - $sudahDinilai,
        ];

        $siswa = User::siswaBerjalan()
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->with('nilai')
            ->when($q, fn ($query) => $query->where(fn ($u) =>
                $u->where('name', 'like', "%{$q}%")
                  ->orWhere('nisn', 'like', "%{$q}%")))
            ->when($status === 'sudah', fn ($query) =>
                $query->whereHas('nilai', fn ($n) => $n->whereNotNull('skor_presentasi')))
            ->when($status === 'belum', fn ($query) =>
                $query->where(fn ($u) =>
                    $u->whereDoesntHave('nilai')
                      ->orWhereHas('nilai', fn ($n) => $n->whereNull('skor_presentasi'))))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('guru.nilai.index', compact('siswa', 'q', 'status', 'rekap'));
    }

    public function storeGuru(Request $request)
    {
        $siswa = User::where('id', $request->user_id)
            ->siswa()
            ->where('guru_id', Auth::id())
            ->where('status_pkl', 'aktif')
            ->firstOrFail();

        $nilai = Nilai::firstOrNew(['user_id' => $siswa->id]);

        // Foto lembar penilaian instruktur WAJIB ada sebelum penilaian boleh
        // disimpan. Kalau foto lama sudah tersimpan, guru tidak perlu
        // mengunggah ulang -- yang lama tetap dianggap memenuhi syarat.
        $adaFotoLama = (bool) $nilai->foto_lembar_instruktur;
        $aturanFoto  = $adaFotoLama ? 'nullable' : 'required';

        // Guru memilih "pakai tanda tangan tersimpan" padahal belum pernah
        // menyimpannya. Dihentikan di sini supaya pesan salahnya jelas.
        if ($request->input('sumber_ttd_pembimbing') === 'tersimpan'
            && ! optional(Auth::user())->punyaTtdTersimpan()) {
            return back()
                ->withInput()
                ->withErrors([
                    'sumber_ttd_pembimbing' => 'Anda belum punya tanda tangan tersimpan. Unggah dulu lewat tombol "Tanda Tangan Saya" di halaman Monitoring Absensi.',
                ]);
        }

        $request->validate([
            'skor_soft_skill'         => 'required|numeric|between:0,100',
            'deskripsi_soft_skill'    => 'required|string',
            'skor_hard_skill'         => 'required|numeric|between:0,100',
            'deskripsi_hard_skill'    => 'required|string',
            'skor_pengembangan'       => 'required|numeric|between:0,100',
            'deskripsi_pengembangan'  => 'required|string',
            'skor_kewirausahaan'      => 'required|numeric|between:0,100',
            'deskripsi_kewirausahaan' => 'required|string',
            'skor_laporan'            => 'required|numeric|between:0,100',
            'deskripsi_laporan'       => 'required|string',
            'skor_presentasi'         => 'required|numeric|between:0,100',
            'deskripsi_presentasi'    => 'required|string',
            'catatan_guru'            => 'nullable|string',
            'foto_lembar_instruktur'  => $aturanFoto . '|image|mimes:jpeg,png,jpg|max:3072',

            // Tanda tangan yang ditempel otomatis ke hasil cetak PDF.
            // Semuanya opsional supaya penilaian lama tetap bisa disimpan ulang.
            // Instruktur: tetap diunggah saat memberi nilai.
            'ttd_instruktur'          => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
            'hapus_ttd_instruktur'    => ['nullable', 'boolean'],

            // Guru pembimbing punya beberapa cara pengisian:
            //   tetap     -> biarkan yang sudah tersimpan pada penilaian ini
            //   tersimpan -> salin dari tanda tangan tersimpan milik guru
            //   unggah    -> pakai berkas baru yang dipilih sekarang
            //   hapus     -> kosongkan tanda tangannya
            'sumber_ttd_pembimbing'   => ['nullable', Rule::in(['tetap', 'tersimpan', 'unggah', 'hapus'])],
            'ttd_pembimbing'          => ['nullable', 'required_if:sumber_ttd_pembimbing,unggah', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],

            // Identitas yang dipakai pada hasil CETAK penilaian
            'label_identitas'         => ['nullable', Rule::in([Nilai::LABEL_NISN, Nilai::LABEL_NIS])],
            'nomor_identitas'         => ['nullable', 'string', 'max:30', 'regex:/^[0-9.\-\/ ]*$/'],
        ], [
            'nomor_identitas.max'   => 'Nomor NIS/NISN maksimal 30 karakter.',
            'nomor_identitas.regex' => 'Nomor NIS/NISN hanya boleh berisi angka, titik, strip, atau garis miring.',
            'foto_lembar_instruktur.required' => 'Foto lembar penilaian instruktur wajib diunggah. Penilaian tidak bisa disimpan tanpa lembar ini.',
            'foto_lembar_instruktur.image'    => 'File harus berupa gambar (JPG/JPEG/PNG).',
            'foto_lembar_instruktur.mimes'    => 'Format foto harus JPG, JPEG, atau PNG.',
            'foto_lembar_instruktur.max'      => 'Ukuran foto maksimal 3 MB.',
            'ttd_instruktur.image' => 'Tanda tangan instruktur harus berupa gambar (JPG/JPEG/PNG).',
            'ttd_instruktur.mimes' => 'Format tanda tangan instruktur harus JPG, JPEG, atau PNG.',
            'ttd_instruktur.max'   => 'Ukuran tanda tangan instruktur maksimal 3 MB.',
            'ttd_pembimbing.required_if' => 'Pilih berkas tanda tangan guru pembimbing, atau pilih opsi lain di bagian tanda tangan.',
            'ttd_pembimbing.image' => 'Tanda tangan pembimbing harus berupa gambar (JPG/JPEG/PNG).',
            'ttd_pembimbing.mimes' => 'Format tanda tangan pembimbing harus JPG, JPEG, atau PNG.',
            'ttd_pembimbing.max'   => 'Ukuran tanda tangan pembimbing maksimal 3 MB.',
        ]);

        $nilai->guru_id = Auth::id();

        $nilai->skor_soft_skill         = $request->skor_soft_skill;
        $nilai->deskripsi_soft_skill    = $request->deskripsi_soft_skill;
        $nilai->skor_hard_skill         = $request->skor_hard_skill;
        $nilai->deskripsi_hard_skill    = $request->deskripsi_hard_skill;
        $nilai->skor_pengembangan       = $request->skor_pengembangan;
        $nilai->deskripsi_pengembangan  = $request->deskripsi_pengembangan;
        $nilai->skor_kewirausahaan      = $request->skor_kewirausahaan;
        $nilai->deskripsi_kewirausahaan = $request->deskripsi_kewirausahaan;
        $nilai->skor_laporan            = $request->skor_laporan;
        $nilai->deskripsi_laporan       = $request->deskripsi_laporan;
        $nilai->skor_presentasi         = $request->skor_presentasi;
        $nilai->deskripsi_presentasi    = $request->deskripsi_presentasi;
        $nilai->catatan_guru            = $request->catatan_guru;

        // ---- Identitas untuk hasil cetak (NIS / NISN) ----
        // Bawaan tetap NISN supaya cetakan lama tidak berubah bila guru tidak
        // mengubah apa pun di bagian ini.
        $label = (string) $request->input('label_identitas', Nilai::LABEL_NISN);
        $nilai->label_identitas = $label === Nilai::LABEL_NIS ? Nilai::LABEL_NIS : Nilai::LABEL_NISN;

        $nomor = trim((string) $request->input('nomor_identitas', ''));
        // Kosongkan (null) bila guru tidak mengisi -> cetakan otomatis memakai
        // NISN bawaan milik siswa.
        $nilai->nomor_identitas = $nomor !== '' ? $nomor : null;

        // Simpan / ganti foto lembar penilaian instruktur
        if ($request->hasFile('foto_lembar_instruktur')) {
            if ($nilai->foto_lembar_instruktur && Storage::disk('public')->exists($nilai->foto_lembar_instruktur)) {
                Storage::disk('public')->delete($nilai->foto_lembar_instruktur);
            }
            $nilai->foto_lembar_instruktur = ImageCompressor::store($request->file('foto_lembar_instruktur'), 'nilai/lembar-instruktur');
        }

        // ---- Tanda tangan yang ditempel ke hasil cetak PDF ----
        // Instruktur = Pembimbing Dunia Kerja, tercetak di kolom KANAN.
        $this->terapkanTtd(
            $nilai,
            $request,
            'ttd_instruktur',
            'ttd/nilai/instruktur',
            $siswa->instruktur->name ?? null
        );

        // Pembimbing = guru yang sedang menilai, tercetak di kolom KIRI.
        // Boleh diambil dari tanda tangan tersimpan supaya tidak unggah ulang.
        $this->terapkanTtdPembimbing($nilai, $request);

        // Nilai akhir = rata-rata 6 komponen (0-100)
        $nilai->nilai_akhir   = $this->hitungRataRata($nilai);
        $nilai->nilai_guru    = $nilai->nilai_akhir;    // kompatibilitas kolom lama
        $nilai->nilai_laporan = $request->skor_laporan; // kompatibilitas kolom lama

        $nilai->save();

        return redirect()->route('guru.nilai.index')
            ->with('success', 'Penilaian PKL berhasil disimpan.');
    }

    /**
     * Simpan / ganti / hapus satu tanda tangan pada penilaian.
     *
     * Berkas lama selalu ikut dibuang supaya storage tidak menumpuk gambar
     * yatim setiap kali guru mengunggah ulang.
     *
     * @param  string       $kolom   'ttd_instruktur' atau 'ttd_pembimbing'
     * @param  string       $folder  Folder tujuan di disk 'public'
     * @param  string|null  $nama    Nama penanda tangan untuk arsip
     */
    private function terapkanTtd(
        Nilai $nilai,
        Request $request,
        string $kolom,
        string $folder,
        ?string $nama
    ): void {
        $kolomNama  = $kolom . '_nama';
        $kolomWaktu = $kolom . '_at';

        // 1) Guru mencentang "hapus" -> kosongkan tanda tangannya.
        if ($request->boolean('hapus_' . $kolom)) {
            TandaTangan::hapus($nilai->{$kolom});

            $nilai->{$kolom}      = null;
            $nilai->{$kolomNama}  = null;
            $nilai->{$kolomWaktu} = null;

            return;
        }

        // 2) Tidak ada berkas baru -> tanda tangan lama dibiarkan apa adanya.
        //    Ini penting: guru sering menyimpan ulang nilai tanpa mengunggah
        //    ulang tanda tangan.
        if (! $request->hasFile($kolom)) {
            return;
        }

        $path = TandaTangan::simpanUnggahan($request->file($kolom), $folder);

        // 3) Gagal diproses -> jangan sentuh data lama supaya tidak hilang.
        if ($path === null) {
            return;
        }

        TandaTangan::hapus($nilai->{$kolom});

        $nilai->{$kolom}      = $path;
        $nilai->{$kolomNama}  = $nama;
        $nilai->{$kolomWaktu} = now();
    }

    /**
     * Isi tanda tangan GURU PEMBIMBING pada penilaian.
     *
     * Berbeda dengan instruktur, guru pembimbing boleh memakai tanda tangan
     * yang sudah pernah ia simpan di halaman Monitoring Absensi sehingga
     * tidak perlu mengunggah berkas yang sama setiap kali menilai.
     *
     * Pilihan pada form (sumber_ttd_pembimbing):
     *   tetap     -> biarkan apa adanya (bawaan bila penilaian sudah punya)
     *   tersimpan -> SALIN dari tanda tangan tersimpan milik guru
     *   unggah    -> pakai berkas baru yang dipilih sekarang
     *   hapus     -> kosongkan
     *
     * Sengaja MENYALIN pada opsi "tersimpan": kalau guru nanti mengganti
     * tanda tangan tersimpannya, penilaian yang sudah disimpan tidak ikut
     * berubah sehingga arsip cetakan lama tetap utuh.
     */
    private function terapkanTtdPembimbing(Nilai $nilai, Request $request): void
    {
        $guru   = Auth::user();
        $sumber = (string) $request->input('sumber_ttd_pembimbing', 'tetap');

        // Guru terlanjur memilih berkas walau opsinya tidak diubah.
        // Berkas yang sudah dipilih selalu dimenangkan supaya tidak terasa
        // seperti unggahannya diabaikan diam-diam.
        if ($request->hasFile('ttd_pembimbing')) {
            $sumber = 'unggah';
        }

        // ---- Kosongkan ----
        if ($sumber === 'hapus') {
            TandaTangan::hapus($nilai->ttd_pembimbing);

            $nilai->ttd_pembimbing      = null;
            $nilai->ttd_pembimbing_nama = null;
            $nilai->ttd_pembimbing_at   = null;

            return;
        }

        // ---- Pakai tanda tangan tersimpan (disalin) ----
        if ($sumber === 'tersimpan') {
            $path = TandaTangan::salin($guru->ttd_tersimpan ?? null, 'ttd/nilai/pembimbing');

            // Berkas tersimpan hilang dari storage -> jangan rusak data lama.
            if ($path === null) {
                return;
            }

            $this->pasangTtdPembimbing($nilai, $path, $guru->name ?? null);

            return;
        }

        // ---- Unggah berkas baru ----
        if ($sumber === 'unggah') {
            $path = TandaTangan::simpanUnggahan($request->file('ttd_pembimbing'), 'ttd/nilai/pembimbing');

            // Gagal diproses -> biarkan tanda tangan lama supaya tidak hilang.
            if ($path === null) {
                return;
            }

            $this->pasangTtdPembimbing($nilai, $path, $guru->name ?? null);

            return;
        }

        // 'tetap' -> tidak ada yang diubah.
    }

    /** Pasang berkas tanda tangan pembimbing baru, buang yang lama. */
    private function pasangTtdPembimbing(Nilai $nilai, string $path, ?string $nama): void
    {
        TandaTangan::hapus($nilai->ttd_pembimbing);

        $nilai->ttd_pembimbing      = $path;
        $nilai->ttd_pembimbing_nama = $nama;
        $nilai->ttd_pembimbing_at   = now();
    }
}

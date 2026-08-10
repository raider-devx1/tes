<?php

namespace App\Http\Controllers;

use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * PENGATURAN > TANGGAL MERAH
 *
 * Admin mendaftarkan tanggal merah untuk satu tahun (libur nasional, cuti
 * bersama, libur sekolah). Akibatnya di seluruh sistem:
 *   - absensi tidak perlu diisi pada tanggal itu,
 *   - hari itu TIDAK PERNAH dihitung Alpha,
 *   - notifikasi pengingat (jurnal / catatan / absensi) tidak dimunculkan.
 */
class HariLiburController extends Controller
{
    /** Batas jumlah baris yang boleh diproses sekali pengisian massal. */
    private const MAKS_BARIS_MASSAL = 200;

    /** Zona waktu aplikasi (WITA secara bawaan). */
    private function tz(): string
    {
        return config('app.timezone', 'Asia/Makassar');
    }

    /** Pesan validasi berbahasa Indonesia. */
    private function pesanValidasi(): array
    {
        return [
            'nama.required'                  => 'Nama tanggal merah wajib diisi.',
            'nama.max'                       => 'Nama tanggal merah maksimal 120 karakter.',
            'tanggal_mulai.required'         => 'Tanggal wajib dipilih.',
            'tanggal_mulai.date'             => 'Tanggal yang dipilih tidak valid.',
            'tanggal_selesai.date'           => 'Tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            'keterangan.max'                 => 'Keterangan maksimal 500 karakter.',
        ];
    }

    /** Aturan validasi form satu tanggal merah. */
    private function aturan(): array
    {
        return [
            'nama'            => ['required', 'string', 'max:120'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan'      => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Rapikan hasil validasi sebelum disimpan.
     * Tanggal selesai yang sama dengan tanggal mulai disimpan NULL supaya
     * baris "satu hari" bentuknya konsisten.
     */
    private function rapikan(array $data): array
    {
        $mulai   = Carbon::parse($data['tanggal_mulai'])->format('Y-m-d');
        $selesai = blank($data['tanggal_selesai'] ?? null)
            ? null
            : Carbon::parse($data['tanggal_selesai'])->format('Y-m-d');

        if ($selesai !== null && $selesai <= $mulai) {
            $selesai = null;
        }

        return [
            'nama'            => trim($data['nama']),
            'tanggal_mulai'   => $mulai,
            'tanggal_selesai' => $selesai,
            'keterangan'      => blank($data['keterangan'] ?? null) ? null : trim($data['keterangan']),
            'aktif'           => true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN DAFTAR
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $hariIni       = Carbon::today($this->tz());
        $tahunSekarang = (int) $hariIni->year;

        // Pilihan tahun = tahun yang sudah punya data + tahun ini + tahun depan,
        // supaya admin bisa langsung menyusun kalender tahun berikutnya.
        $daftarTahun = HariLibur::tahunTersedia();

        foreach ([$tahunSekarang, $tahunSekarang + 1] as $tambahan) {
            if (! in_array($tambahan, $daftarTahun, true)) {
                $daftarTahun[] = $tambahan;
            }
        }

        sort($daftarTahun);

        $tahun = (int) $request->get('tahun', $tahunSekarang);

        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = $tahunSekarang;
        }

        try {
            $daftar = HariLibur::tahun($tahun)
                ->orderBy('tanggal_mulai')
                ->get();
        } catch (Throwable $e) {
            // Tabel belum dimigrasi di server: tampilkan halaman kosong dengan
            // peringatan, bukan halaman error.
            return view('admin.pengaturan.hari-libur', [
                'daftar'      => collect(),
                'daftarTahun' => [$tahunSekarang],
                'tahun'       => $tahunSekarang,
                'jumlahHari'  => 0,
                'jumlahAktif' => 0,
                'berikutnya'  => null,
                'hariIni'     => $hariIni,
                'belumSiap'   => true,
            ]);
        }

        $aktif = $daftar->where('aktif', true);

        // Libur aktif terdekat yang belum selesai (termasuk yang sedang berjalan).
        $berikutnya = HariLibur::where('aktif', true)
            ->whereRaw('COALESCE(tanggal_selesai, tanggal_mulai) >= ?', [$hariIni->format('Y-m-d')])
            ->orderBy('tanggal_mulai')
            ->first();

        return view('admin.pengaturan.hari-libur', [
            'daftar'      => $daftar,
            'daftarTahun' => $daftarTahun,
            'tahun'       => $tahun,
            'jumlahHari'  => (int) $aktif->sum(fn ($libur) => $libur->jumlah_hari),
            'jumlahAktif' => $aktif->count(),
            'berikutnya'  => $berikutnya,
            'hariIni'     => $hariIni,
            'belumSiap'   => false,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TAMBAH SATU TANGGAL MERAH
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $data = $this->rapikan(
            $request->validate($this->aturan(), $this->pesanValidasi())
        );

        $libur = HariLibur::create($data);

        // Bersihkan Alpha otomatis bila tanggalnya sudah terlewat.
        $dibersihkan = HariLibur::bersihkanAlpaOtomatis($libur->rentangTanggal());

        $pesan = 'Tanggal merah "' . $libur->nama . '" (' . $libur->label_tanggal . ') berhasil ditambahkan.';

        if ($dibersihkan > 0) {
            $pesan .= ' ' . $dibersihkan . ' baris Alpha otomatis pada tanggal tersebut ikut dibatalkan.';
        }

        return redirect()
            ->route('admin.hari-libur.index', ['tahun' => Carbon::parse($libur->tanggal_mulai)->year])
            ->with('success', $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | TAMBAH BANYAK TANGGAL SEKALIGUS
    |--------------------------------------------------------------------------
    | Menyusun kalender satu tahun lewat form satu-satu itu melelahkan, jadi
    | admin boleh menempelkan daftarnya sekaligus. Format tiap baris:
    |
    |   2026-01-01 = Tahun Baru
    |   2026-03-20 .. 2026-03-22 = Cuti Bersama Nyepi
    |   17/08/2026 = Hari Kemerdekaan RI
    |
    | Pemisah nama boleh "=", "|", ";" atau ",". Pemisah rentang boleh "..",
    | "s/d", "sampai", atau tanda hubung yang diapit spasi. Baris yang dimulai
    | tanda "#" dianggap komentar.
    */
    public function storeMassal(Request $request)
    {
        $request->validate([
            'daftar' => ['required', 'string', 'max:20000'],
        ], [
            'daftar.required' => 'Daftar tanggal merah masih kosong.',
            'daftar.max'      => 'Daftar terlalu panjang. Silakan bagi menjadi beberapa kali pengisian.',
        ]);

        $baris = preg_split('/\R/u', (string) $request->input('daftar')) ?: [];

        if (count($baris) > self::MAKS_BARIS_MASSAL) {
            return back()
                ->withInput()
                ->with('error', 'Maksimal ' . self::MAKS_BARIS_MASSAL . ' baris sekali pengisian. Silakan bagi daftarnya.');
        }

        // Kumpulan rentang yang sudah ada, supaya baris yang sama tidak dobel.
        try {
            $sudahAda = HariLibur::query()
                ->get(['tanggal_mulai', 'tanggal_selesai'])
                ->map(function ($libur) {
                    $mulai   = Carbon::parse((string) $libur->tanggal_mulai)->format('Y-m-d');
                    $selesai = blank($libur->tanggal_selesai)
                        ? ''
                        : Carbon::parse((string) $libur->tanggal_selesai)->format('Y-m-d');

                    return $mulai . '|' . $selesai;
                })
                ->all();
        } catch (Throwable $e) {
            return back()->with('error', 'Tabel tanggal merah belum tersedia. Jalankan migrasi database terlebih dahulu.');
        }

        $akanDibuat  = [];
        $barisGagal  = [];
        $barisDobel  = 0;
        $nomor       = 0;

        foreach ($baris as $satuBaris) {
            $nomor++;
            $isi = trim((string) $satuBaris);

            // Baris kosong & komentar dilewati tanpa dianggap gagal.
            if ($isi === '' || str_starts_with($isi, '#')) {
                continue;
            }

            $hasil = $this->pecahBaris($isi);

            if ($hasil === null) {
                $barisGagal[] = $nomor;
                continue;
            }

            $kunci = $hasil['tanggal_mulai'] . '|' . ($hasil['tanggal_selesai'] ?? '');

            if (in_array($kunci, $sudahAda, true)) {
                $barisDobel++;
                continue;
            }

            $sudahAda[]   = $kunci;
            $akanDibuat[] = $hasil;
        }

        if (empty($akanDibuat)) {
            $pesan = 'Tidak ada tanggal merah baru yang tersimpan.';

            if ($barisDobel > 0) {
                $pesan .= ' ' . $barisDobel . ' baris dilewati karena tanggalnya sudah terdaftar.';
            }

            if (! empty($barisGagal)) {
                $pesan .= ' Baris yang formatnya tidak dikenali: ' . implode(', ', $barisGagal) . '.';
            }

            return back()->withInput()->with('error', $pesan);
        }

        $tanggalTerdampak = [];

        DB::transaction(function () use ($akanDibuat, &$tanggalTerdampak) {
            foreach ($akanDibuat as $data) {
                $libur = HariLibur::create($data);

                $tanggalTerdampak = array_merge($tanggalTerdampak, $libur->rentangTanggal());
            }
        });

        $dibersihkan = HariLibur::bersihkanAlpaOtomatis($tanggalTerdampak);

        $pesan = count($akanDibuat) . ' tanggal merah berhasil ditambahkan.';

        if ($barisDobel > 0) {
            $pesan .= ' ' . $barisDobel . ' baris dilewati karena tanggalnya sudah terdaftar.';
        }

        if (! empty($barisGagal)) {
            $pesan .= ' Baris yang formatnya tidak dikenali dan dilewati: ' . implode(', ', $barisGagal) . '.';
        }

        if ($dibersihkan > 0) {
            $pesan .= ' ' . $dibersihkan . ' baris Alpha otomatis pada tanggal tersebut ikut dibatalkan.';
        }

        $tahun = (int) Carbon::parse($akanDibuat[0]['tanggal_mulai'])->year;

        return redirect()
            ->route('admin.hari-libur.index', ['tahun' => $tahun])
            ->with('success', $pesan);
    }

    /**
     * Pecah satu baris teks menjadi data tanggal merah.
     * Mengembalikan null bila formatnya tidak dikenali.
     */
    private function pecahBaris(string $baris): ?array
    {
        // Pisahkan sisi tanggal dan nama pada pemisah pertama.
        $bagian      = preg_split('/\s*[|=;,\t]\s*/u', $baris, 2);
        $sisiTanggal = trim($bagian[0] ?? '');
        $nama        = trim($bagian[1] ?? '');

        // Pisahkan rentang: "..", "s/d", "sampai", atau "-" yang diapit spasi.
        $sisiTanggal = preg_split(
            '/(?:\s*(?:\.\.+|s\/d|sampai|\x{2014}|\x{2013})\s*|\s+-\s+)/iu',
            $sisiTanggal,
            2
        );

        $mulai   = $this->bacaTanggal($sisiTanggal[0] ?? null);
        $selesai = $this->bacaTanggal($sisiTanggal[1] ?? null);

        if ($mulai === null) {
            return null;
        }

        if ($selesai !== null && $selesai <= $mulai) {
            $selesai = null;
        }

        if ($nama === '') {
            $nama = 'Tanggal Merah';
        }

        return [
            'nama'            => mb_substr($nama, 0, 120),
            'tanggal_mulai'   => $mulai,
            'tanggal_selesai' => $selesai,
            'keterangan'      => null,
            'aktif'           => true,
        ];
    }

    /**
     * Baca satu potongan teks tanggal.
     * Menerima "2026-08-17" dan "17/08/2026" (juga dengan titik atau hubung).
     * Mengembalikan 'Y-m-d' atau null bila bukan tanggal yang sah.
     */
    private function bacaTanggal(?string $teks): ?string
    {
        $teks = trim((string) $teks);

        if ($teks === '') {
            return null;
        }

        // Bentuk tahun di depan: 2026-8-17 atau 2026/08/17
        if (preg_match('/^(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})$/', $teks, $c)) {
            $tahun = (int) $c[1];
            $bulan = (int) $c[2];
            $hari  = (int) $c[3];
        // Bentuk hari di depan: 17/08/2026 atau 17-8-2026
        } elseif (preg_match('/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/', $teks, $c)) {
            $hari  = (int) $c[1];
            $bulan = (int) $c[2];
            $tahun = (int) $c[3];
        } else {
            return null;
        }

        if (! checkdate($bulan, $hari, $tahun) || $tahun < 2000 || $tahun > 2100) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $tahun, $bulan, $hari);
    }

    /*
    |--------------------------------------------------------------------------
    | UBAH
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, HariLibur $hariLibur)
    {
        $data = $this->rapikan(
            $request->validate($this->aturan(), $this->pesanValidasi())
        );

        // Status aktif tidak diubah dari form ini (ada tombol khusus).
        unset($data['aktif']);

        $hariLibur->update($data);

        $dibersihkan = $hariLibur->aktif
            ? HariLibur::bersihkanAlpaOtomatis($hariLibur->fresh()->rentangTanggal())
            : 0;

        $pesan = 'Tanggal merah "' . $hariLibur->nama . '" berhasil diperbarui.';

        if ($dibersihkan > 0) {
            $pesan .= ' ' . $dibersihkan . ' baris Alpha otomatis pada tanggal tersebut ikut dibatalkan.';
        }

        return redirect()
            ->route('admin.hari-libur.index', ['tahun' => Carbon::parse($hariLibur->tanggal_mulai)->year])
            ->with('success', $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | AKTIF / NONAKTIF
    |--------------------------------------------------------------------------
    | Menonaktifkan lebih aman daripada menghapus: datanya tetap tersimpan
    | sebagai catatan, tetapi aturannya berhenti berlaku.
    */
    public function toggle(HariLibur $hariLibur)
    {
        $hariLibur->update(['aktif' => ! $hariLibur->aktif]);

        if ($hariLibur->aktif) {
            $dibersihkan = HariLibur::bersihkanAlpaOtomatis($hariLibur->rentangTanggal());

            $pesan = 'Tanggal merah "' . $hariLibur->nama . '" diaktifkan kembali.';

            if ($dibersihkan > 0) {
                $pesan .= ' ' . $dibersihkan . ' baris Alpha otomatis pada tanggal tersebut ikut dibatalkan.';
            }
        } else {
            $pesan = 'Tanggal merah "' . $hariLibur->nama . '" dinonaktifkan. '
                . 'Absensi pada tanggal tersebut kembali mengikuti jadwal biasa.';
        }

        return back()->with('success', $pesan);
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS
    |--------------------------------------------------------------------------
    */
    public function destroy(HariLibur $hariLibur)
    {
        $nama  = $hariLibur->nama;
        $tahun = (int) Carbon::parse($hariLibur->tanggal_mulai)->year;

        $hariLibur->delete();

        return redirect()
            ->route('admin.hari-libur.index', ['tahun' => $tahun])
            ->with('success', 'Tanggal merah "' . $nama . '" berhasil dihapus.');
    }

    /** Hapus seluruh tanggal merah pada satu tahun sekaligus. */
    public function destroyTahun(Request $request)
    {
        $request->validate([
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
        ], [
            'tahun.required' => 'Tahun wajib dipilih.',
            'tahun.integer'  => 'Tahun tidak valid.',
        ]);

        $tahun  = (int) $request->input('tahun');
        $jumlah = HariLibur::tahun($tahun)->count();

        if ($jumlah === 0) {
            return back()->with('error', 'Tidak ada tanggal merah pada tahun ' . $tahun . '.');
        }

        HariLibur::tahun($tahun)->get()->each(fn ($libur) => $libur->delete());

        return redirect()
            ->route('admin.hari-libur.index', ['tahun' => $tahun])
            ->with('success', $jumlah . ' tanggal merah tahun ' . $tahun . ' berhasil dihapus.');
    }
}

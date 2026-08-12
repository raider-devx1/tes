@props([
    /** Nama input file yang dikirim ke server. */
    'name' => 'foto_bukti',
    /** Judul kecil di atas kotak foto. */
    'label' => 'Foto Bukti Kehadiran',
    /** Wajib diisi? Bila true, form tidak boleh dikirim sebelum foto dipilih. */
    'wajib' => true,
    /** Batas ukuran berkas yang boleh diunggah (MB). */
    'maksMb' => 3,
    /** Kompres otomatis di browser sebelum dikirim (hemat kuota siswa). */
    'kompres' => true,
    /** Sisi terpanjang hasil kompresi (px). */
    'maksDimensi' => 1600,
])

{{--
|--------------------------------------------------------------------------
| KOMPONEN UNGGAH FOTO ABSENSI (PENGGANTI KAMERA LANGSUNG)
|--------------------------------------------------------------------------
| PERUBAHAN ALUR:
| - Siswa TIDAK lagi dipaksa memotret lewat kamera (atribut capture dihapus).
| - Siswa menekan "Pilih Foto", lalu memilih berkas foto dari HP/komputer
|   (galeri, file manager, atau kamera bila HP menawarkan pilihan itu).
| - Berkas dikirim sebagai <input type="file"> biasa, sehingga form WAJIB
|   memakai enctype="multipart/form-data".
| - Batas ukuran berkas: {{ (int) $maksMb }} MB. Lebih dari itu langsung DITOLAK di browser,
|   dan tetap divalidasi ulang di server (rule: max:{{ (int) $maksMb * 1024 }} KB).
| - Foto <= batas tetap dikompres otomatis di browser (canvas) lalu dikompres
|   ulang di server oleh App\Support\ImageCompressor::store().
| - Peringatan ketentuan foto TETAP DITAMPILKAN seperti sebelumnya.
--}}

<div
    class="unggah-foto-absensi"
    data-upload-foto
    data-upload-maks-mb="{{ (float) $maksMb }}"
    data-upload-maks-dimensi="{{ (int) $maksDimensi }}"
    data-upload-kompres="{{ $kompres ? '1' : '0' }}"
    data-upload-wajib="{{ $wajib ? '1' : '0' }}"
>
    <label class="block text-sm font-semibold text-[#1f2430]">
        {{ $label }}
        @if ($wajib)
            <span class="text-[#cf202f]">*</span>
        @endif
    </label>

    {{-- ============ CATATAN PERINGATAN (TETAP ADA) ============ --}}
    <div class="mt-2 flex items-start gap-2.5 rounded-xl border border-[#f3b9be] bg-[#fdf2f3] px-3.5 py-3">
        <svg class="mt-0.5 h-5 w-5 flex-none text-[#cf202f]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
        </svg>
        <div class="text-[12px] leading-relaxed text-[#8f1520] sm:text-[13px]">
            <p class="font-bold">Perhatian sebelum mengunggah foto</p>
            <p class="mt-1">
                Foto <span class="font-semibold">wajib menampilkan wajah Anda</span> dengan
                <span class="font-semibold">latar belakang tempat industri</span> Anda.
            </p>
            <ul class="mt-1.5 list-disc space-y-0.5 pl-4">
                <li>Wajah terlihat jelas, tidak tertutup masker/helm, dan tidak gelap.</li>
                <li>Latar belakang menunjukkan lokasi industri (bukan di rumah/kamar/kendaraan).</li>
                <li>Gunakan foto hari ini, bukan foto lama atau gambar unduhan dari internet.</li>
                <li>Format <span class="font-semibold">JPG, JPEG, PNG, atau WEBP</span> dengan ukuran
                    <span class="font-semibold">maksimal {{ (int) $maksMb }} MB</span>.</li>
            </ul>
            <p class="mt-1.5">
                Foto yang tidak sesuai akan <span class="font-semibold">ditolak guru pembimbing</span>
                dan Anda harus mengunggah foto ulang.
            </p>
        </div>
    </div>

    {{-- ============ KOTAK PRATINJAU ============ --}}
    <div class="relative mt-3 overflow-hidden rounded-xl border-2 border-dashed border-[#d7dbe3] bg-[#0f1115]">
        <div class="aspect-[4/3] max-h-[38vh] w-full sm:max-h-[42vh]">
            {{-- Pratinjau foto yang dipilih --}}
            <img data-upload-preview alt="Pratinjau foto absensi" class="hidden h-full w-full object-cover">

            {{-- Keadaan awal --}}
            <div data-upload-kosong class="flex h-full w-full flex-col items-center justify-center gap-2 px-4 text-center">
                <svg class="h-10 w-10 text-white/45" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 16V4m0 0L8 8m4-4 4 4M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />
                </svg>
                <p class="text-xs font-semibold text-white/80">Belum ada foto</p>
                <p class="text-[11px] text-white/55">Tekan tombol di bawah untuk memilih foto dari perangkat Anda.</p>
            </div>

            {{-- Indikator sedang memproses --}}
            <div data-upload-sibuk class="absolute inset-0 hidden flex-col items-center justify-center gap-2 bg-black/70">
                <svg class="h-8 w-8 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4Z"></path>
                </svg>
                <p class="text-xs font-semibold text-white">Memproses foto...</p>
            </div>
        </div>

        <span data-upload-lencana class="absolute right-3 top-3 hidden items-center gap-1.5 rounded-full bg-[#05b169] px-2.5 py-1 text-[11px] font-semibold text-white">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
            </svg>
            Foto siap
        </span>
    </div>

    {{-- ============ INPUT FILE (INI YANG DIKIRIM KE SERVER) ============ --}}
    {{-- data-no-compress: kompresi ditangani komponen ini sendiri agar tidak
         diproses dua kali oleh handler global di resources/js/app.js --}}
    <input type="file"
           name="{{ $name }}"
           data-upload-file
           data-no-compress
           accept="image/jpeg,image/jpg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
           tabindex="-1"
           class="sr-only absolute h-px w-px opacity-0">

    {{-- ============ TOMBOL AKSI ============ --}}
    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
        <button type="button" data-upload-pilih
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:flex-1">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 16V4m0 0L8 8m4-4 4 4M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />
            </svg>
            <span data-upload-teks-pilih>Pilih Foto</span>
        </button>

        <button type="button" data-upload-hapus hidden
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-[#cf202f]/25 bg-white px-4 py-3 text-sm font-bold text-[#cf202f] transition hover:bg-[#cf202f]/5 sm:w-auto">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 7h12M9 7V5h6v2m-8 0 1 12a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1l1-12" />
            </svg>
            Hapus Foto
        </button>
    </div>

    <p data-upload-status class="mt-2 text-[12px] font-medium text-[#5b616e]">
        Format JPG, PNG, atau WEBP. Ukuran berkas maksimal {{ (int) $maksMb }} MB dan akan dikompres otomatis sebelum dikirim.
    </p>

    @error($name)
        <p class="mt-1 text-[12px] font-semibold text-[#cf202f]">{{ $message }}</p>
    @enderror
</div>

@once
    @push('scripts')
    <script>
    /* =====================================================================
     * UNGGAH FOTO ABSENSI (pengganti komponen kamera langsung)
     * ---------------------------------------------------------------------
     * - Memakai <input type="file"> biasa TANPA atribut capture, sehingga HP
     *   menampilkan pilihan galeri/berkas (dan kamera bila siswa mau).
     * - Menolak berkas non-gambar dan berkas melebihi batas MB.
     * - Foto yang lolos tetap dikompres di browser agar hemat kuota, lalu
     *   dimasukkan kembali ke input memakai DataTransfer.
     * - Semua event dipasang di level document (event delegation) supaya tetap
     *   bekerja walau komponen berada di dalam modal x-teleport milik Alpine.
     * ===================================================================== */
    (function () {
        if (window.__unggahFotoAbsensiSiap) return;
        window.__unggahFotoAbsensiSiap = true;

        var PESAN_WAJIB = 'Anda belum memilih foto. Tekan tombol "Pilih Foto" lalu pilih foto dari perangkat Anda.';

        function el(kotak, pemilih) {
            return kotak.querySelector(pemilih);
        }

        function angka(nilai, cadangan) {
            var n = parseFloat(nilai);
            return isNaN(n) ? cadangan : n;
        }

        function ukuran(bytes) {
            if (window.formatUkuran) return window.formatUkuran(bytes);
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
            if (bytes >= 1024) return Math.round(bytes / 1024) + ' KB';
            return bytes + ' B';
        }

        function maksByte(kotak) {
            return angka(kotak.getAttribute('data-upload-maks-mb'), 3) * 1024 * 1024;
        }

        function labelMaks(kotak) {
            return angka(kotak.getAttribute('data-upload-maks-mb'), 3) + ' MB';
        }

        function setStatus(kotak, teks, jenis) {
            var p = el(kotak, '[data-upload-status]');
            if (!p) return;
            p.textContent = teks;
            p.className = 'mt-2 text-[12px] font-medium ' + (
                jenis === 'error' ? 'text-[#cf202f]' :
                jenis === 'ok'    ? 'text-[#05b169]' :
                                    'text-[#5b616e]'
            );
        }

        function sibuk(kotak, aktif) {
            var lapis = el(kotak, '[data-upload-sibuk]');
            if (lapis) lapis.classList.toggle('hidden', !aktif);
            var tombol = el(kotak, '[data-upload-pilih]');
            if (tombol) tombol.disabled = !!aktif;
        }

        function peringatan(pesan, judul) {
            if (window.swalPeringatan) {
                window.swalPeringatan(pesan, judul || 'Foto Tidak Sesuai');
            } else {
                alert(pesan);
            }
        }

        function tampilkanHasil(kotak, berkas) {
            var pratinjau = el(kotak, '[data-upload-preview]');
            var kosong    = el(kotak, '[data-upload-kosong]');
            var lencana   = el(kotak, '[data-upload-lencana]');
            var tombolHps = el(kotak, '[data-upload-hapus]');
            var teks      = el(kotak, '[data-upload-teks-pilih]');

            if (pratinjau && pratinjau.dataset.objectUrl) {
                try { URL.revokeObjectURL(pratinjau.dataset.objectUrl); } catch (e) {}
                delete pratinjau.dataset.objectUrl;
            }

            if (berkas) {
                if (pratinjau) {
                    var tautan = URL.createObjectURL(berkas);
                    pratinjau.src = tautan;
                    pratinjau.dataset.objectUrl = tautan;
                    pratinjau.classList.remove('hidden');
                }
                if (kosong) kosong.classList.add('hidden');
                if (lencana) { lencana.classList.remove('hidden'); lencana.classList.add('flex'); }
                if (tombolHps) tombolHps.hidden = false;
                if (teks) teks.textContent = 'Ganti Foto';
            } else {
                if (pratinjau) { pratinjau.removeAttribute('src'); pratinjau.classList.add('hidden'); }
                if (kosong) kosong.classList.remove('hidden');
                if (lencana) { lencana.classList.add('hidden'); lencana.classList.remove('flex'); }
                if (tombolHps) tombolHps.hidden = true;
                if (teks) teks.textContent = 'Pilih Foto';
            }
        }

        function kosongkan(kotak, pesan, jenis) {
            var berkasInput = el(kotak, '[data-upload-file]');
            if (berkasInput) berkasInput.value = '';
            tampilkanHasil(kotak, null);
            setStatus(
                kotak,
                pesan || ('Format JPG, PNG, atau WEBP. Ukuran berkas maksimal ' + labelMaks(kotak) + ' dan akan dikompres otomatis sebelum dikirim.'),
                jenis || 'info'
            );
        }

        /* Menaruh kembali berkas hasil kompresi ke dalam <input type="file">. */
        function pasangBerkas(input, berkas) {
            try {
                var dt = new DataTransfer();
                dt.items.add(berkas);
                input.files = dt.files;
                return true;
            } catch (e) {
                return false; // Browser lama: pakai berkas asli apa adanya.
            }
        }

        function proses(kotak, input, berkas) {
            if (!berkas) return;

            var batas = maksByte(kotak);

            /* 1. Harus berupa gambar */
            var tipeOk = berkas.type
                ? berkas.type.indexOf('image/') === 0
                : /\.(jpe?g|png|webp)$/i.test(berkas.name || '');

            if (!tipeOk) {
                kosongkan(kotak, 'Berkas yang dipilih bukan gambar. Silakan pilih foto berformat JPG, PNG, atau WEBP.', 'error');
                peringatan('Berkas yang Anda pilih bukan gambar. Silakan pilih foto berformat JPG, PNG, atau WEBP.');
                return;
            }

            /* 2. Ukuran ASLI tidak boleh melebihi batas (mis. 3 MB) */
            if (berkas.size > batas) {
                kosongkan(
                    kotak,
                    'Ukuran foto ' + ukuran(berkas.size) + ' melebihi batas maksimal ' + labelMaks(kotak) + '. Silakan pilih foto lain.',
                    'error'
                );
                peringatan(
                    'Ukuran foto ' + ukuran(berkas.size) + ' melebihi batas maksimal ' + labelMaks(kotak) + '. '
                    + 'Silakan pilih foto lain yang ukurannya di bawah ' + labelMaks(kotak) + '.',
                    'Ukuran Foto Terlalu Besar'
                );
                return;
            }

            /* 3. Kompres (opsional) lalu tampilkan pratinjau */
            var bolehKompres = kotak.getAttribute('data-upload-kompres') === '1'
                && typeof window.kompresGambar === 'function';

            sibuk(kotak, true);
            setStatus(kotak, 'Memproses foto...', 'info');

            var janji = bolehKompres
                ? window.kompresGambar(berkas, { maxUkuran: angka(kotak.getAttribute('data-upload-maks-dimensi'), 1600) })
                : Promise.resolve(berkas);

            Promise.resolve(janji).catch(function () {
                return berkas; // Kompresi gagal -> pakai berkas asli.
            }).then(function (hasil) {
                var akhir = (hasil && hasil.size && hasil.size < berkas.size) ? hasil : berkas;

                if (akhir !== berkas) {
                    if (!pasangBerkas(input, akhir)) akhir = berkas;
                }

                tampilkanHasil(kotak, akhir);

                if (akhir.size < berkas.size) {
                    setStatus(
                        kotak,
                        'Foto siap dikirim. Ukuran ' + ukuran(berkas.size) + ' dikompres menjadi ' + ukuran(akhir.size) + '.',
                        'ok'
                    );
                } else {
                    setStatus(kotak, 'Foto siap dikirim. Ukuran ' + ukuran(akhir.size) + '.', 'ok');
                }

                kotak.dispatchEvent(new CustomEvent('foto-dipilih', {
                    bubbles: true,
                    detail: { nama: akhir.name, ukuran: akhir.size }
                }));
            }).catch(function () {
                kosongkan(kotak, 'Foto gagal diproses. Silakan pilih foto lain.', 'error');
            }).then(function () {
                sibuk(kotak, false);
            });
        }

        /* ---------------- Event: klik tombol ---------------- */
        document.addEventListener('click', function (ev) {
            var target = ev.target;
            if (!target || !target.closest) return;

            var tombolPilih = target.closest('[data-upload-pilih]');
            if (tombolPilih) {
                var kotakP = tombolPilih.closest('[data-upload-foto]');
                if (!kotakP) return;
                ev.preventDefault();
                var inputP = el(kotakP, '[data-upload-file]');
                if (inputP) {
                    inputP.value = '';  // agar memilih berkas yang sama tetap memicu change
                    inputP.click();
                }
                return;
            }

            var tombolHapus = target.closest('[data-upload-hapus]');
            if (tombolHapus) {
                var kotakH = tombolHapus.closest('[data-upload-foto]');
                if (!kotakH) return;
                ev.preventDefault();
                kosongkan(kotakH, 'Foto dihapus. Tekan "Pilih Foto" untuk memilih foto lain.', 'info');
            }
        });

        /* ---------------- Event: berkas dipilih ---------------- */
        document.addEventListener('change', function (ev) {
            var target = ev.target;
            if (!target || !target.matches || !target.matches('[data-upload-file]')) return;

            var kotak = target.closest('[data-upload-foto]');
            if (!kotak) return;

            var berkas = target.files && target.files[0];
            if (!berkas) return;   // siswa menekan batal di dialog pemilih berkas

            proses(kotak, target, berkas);
        });

        /* ---------------- Penjaga: form tidak boleh dikirim tanpa foto ---------------- */
        document.addEventListener('submit', function (ev) {
            var form = ev.target;
            if (!form || !form.querySelectorAll) return;

            var daftar = form.querySelectorAll('[data-upload-foto][data-upload-wajib="1"]');
            for (var i = 0; i < daftar.length; i++) {
                var berkasInput = el(daftar[i], '[data-upload-file]');
                if (!berkasInput || !berkasInput.files || !berkasInput.files.length) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    setStatus(daftar[i], PESAN_WAJIB, 'error');
                    peringatan(PESAN_WAJIB, 'Foto Belum Dipilih');
                    if (daftar[i].scrollIntoView) {
                        daftar[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }
            }
        }, true);

        /* ---------------- Fungsi global ---------------- */

        /* Mengosongkan foto pada satu komponen atau seluruh halaman. */
        window.unggahFotoAbsensiReset = function (akar) {
            var lingkup = akar || document;
            var semua = lingkup.querySelectorAll ? lingkup.querySelectorAll('[data-upload-foto]') : [];
            for (var i = 0; i < semua.length; i++) kosongkan(semua[i]);
        };

        /* Kompatibilitas dengan pemanggilan lama dari halaman absensi versi kamera. */
        window.kameraAbsensiTutupSemua = window.kameraAbsensiTutupSemua || function () {};
        window.kameraAbsensiReset = window.kameraAbsensiReset || window.unggahFotoAbsensiReset;
    })();
    </script>
    @endpush
@endonce

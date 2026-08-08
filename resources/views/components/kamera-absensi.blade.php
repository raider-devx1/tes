@props([
    /** Nama input tersembunyi yang dikirim ke server (berisi data URL base64). */
    'name' => 'foto_kamera',
    /** Judul kecil di atas kotak foto. */
    'label' => 'Foto Bukti Kehadiran',
    /** Wajib diisi? Bila true, form tidak boleh dikirim sebelum foto diambil. */
    'wajib' => true,
    /** Sisi terpanjang hasil kompresi (px). */
    'maks' => 1280,
    /** Kualitas JPEG awal hasil kompresi di browser (0.1 - 1.0). */
    'kualitas' => 0.7,
    /** Target ukuran maksimum hasil kompresi (KB). */
    'targetKb' => 500,
    /** true = buka kamera DEPAN (selfie). false = buka kamera BELAKANG. */
    'kameraDepan' => true,
    /**
     * Tolak foto yang usianya lebih tua dari sekian menit (anti foto lama).
     * Isi 0 untuk mematikan pemeriksaan ini. Default mati agar tidak
     * merepotkan siswa bila jam HP-nya tidak akurat.
     */
    'maksUsiaMenit' => 0,
])

{{--
|--------------------------------------------------------------------------
| KOMPONEN AMBIL FOTO - LANGSUNG BUKA KAMERA HP
|--------------------------------------------------------------------------
| CARA KERJA (versi baru):
| - Siswa menekan tombol "Ambil Foto", lalu APLIKASI KAMERA BAWAAN HP
|   LANGSUNG TERBUKA. Tidak ada langkah "aktifkan kamera" dan tidak ada
|   permintaan izin kamera dari browser, sehingga tidak merepotkan siswa.
| - Ini memakai <input type="file" accept="image/*" capture="..."> yang
|   disembunyikan; tombol hanya memicu klik pada input tersebut.
| - Atribut capture membuat HP langsung membuka kamera, BUKAN galeri.
| - Setelah difoto, gambar otomatis dikecilkan ke maksimum {{ $maks }}px dan
|   dikompres jadi JPEG di browser, lalu dikompres ulang di server
|   (App\Support\ImageCompressor::storeDataUrl).
| - Hasilnya disimpan di input tersembunyi sebagai data URL base64.
|
| KELEBIHAN dibanding getUserMedia: TIDAK wajib HTTPS, tidak ada pop up izin
| kamera, dan hasil foto memakai kualitas penuh kamera bawaan HP.
--}}

<div
    class="kamera-absensi"
    data-kamera
    data-kamera-maks="{{ (int) $maks }}"
    data-kamera-kualitas="{{ $kualitas }}"
    data-kamera-target-kb="{{ (int) $targetKb }}"
    data-kamera-wajib="{{ $wajib ? '1' : '0' }}"
    data-kamera-maks-usia="{{ (int) $maksUsiaMenit }}"
>
    <label class="block text-sm font-semibold text-[#1f2430]">
        {{ $label }}
        @if ($wajib)
            <span class="text-[#cf202f]">*</span>
        @endif
    </label>

    {{-- ============ CATATAN PERINGATAN (DI ATAS TOMBOL AMBIL FOTO) ============ --}}
    <div class="mt-2 flex items-start gap-2.5 rounded-xl border border-[#f3b9be] bg-[#fdf2f3] px-3.5 py-3">
        <svg class="mt-0.5 h-5 w-5 flex-none text-[#cf202f]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
        </svg>
        <div class="text-[12px] leading-relaxed text-[#8f1520] sm:text-[13px]">
            <p class="font-bold">Perhatian sebelum mengambil foto</p>
            <p class="mt-1">
                Foto <span class="font-semibold">wajib menampilkan wajah Anda</span> dengan
                <span class="font-semibold">latar belakang tempat industri</span> Anda.
            </p>
            <ul class="mt-1.5 list-disc space-y-0.5 pl-4">
                <li>Wajah terlihat jelas, tidak tertutup masker/helm, dan tidak gelap.</li>
                <li>Latar belakang menunjukkan lokasi industri (bukan di rumah/kamar/kendaraan).</li>
                <li>Foto diambil langsung saat ini juga, bukan foto lama.</li>
            </ul>
            <p class="mt-1.5">
                Foto yang tidak sesuai akan <span class="font-semibold">ditolak guru pembimbing</span>
                dan Anda harus mengambil foto ulang.
            </p>
        </div>
    </div>

    {{-- ============ KOTAK PRATINJAU ============ --}}
    {{-- Tinggi dibatasi di layar kecil supaya pop up absensi tidak kepanjangan. --}}
    <div class="relative mt-3 overflow-hidden rounded-xl border-2 border-dashed border-[#d7dbe3] bg-[#0f1115]">
        <div class="aspect-[4/3] max-h-[38vh] w-full sm:max-h-[42vh]">
            {{-- Hasil jepretan --}}
            <img data-kamera-preview alt="Pratinjau foto absensi" class="hidden h-full w-full object-cover">

            {{-- Keadaan awal --}}
            <div data-kamera-kosong class="flex h-full w-full flex-col items-center justify-center gap-2 px-4 text-center">
                <svg class="h-10 w-10 text-white/45" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 9a2 2 0 0 1 2-2h1.6a2 2 0 0 0 1.66-.89l.68-1.02A2 2 0 0 1 10.6 4h2.8a2 2 0 0 1 1.66.89l.68 1.02A2 2 0 0 0 17.4 7H19a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                    <circle cx="12" cy="13" r="3.4" />
                </svg>
                <p class="text-xs font-semibold text-white/80">Belum ada foto</p>
                <p class="text-[11px] text-white/55">Tekan tombol di bawah, kamera HP akan langsung terbuka.</p>
            </div>

            {{-- Indikator sedang memproses --}}
            <div data-kamera-sibuk class="absolute inset-0 hidden flex-col items-center justify-center gap-2 bg-black/70">
                <svg class="h-8 w-8 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4Z"></path>
                </svg>
                <p class="text-xs font-semibold text-white">Mengompres foto...</p>
            </div>
        </div>

        <span data-kamera-lencana class="absolute right-3 top-3 hidden items-center gap-1.5 rounded-full bg-[#05b169] px-2.5 py-1 text-[11px] font-semibold text-white">
            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
            </svg>
            Foto siap
        </span>

        <canvas data-kamera-canvas class="hidden"></canvas>
    </div>

    {{-- ============ INPUT KAMERA (TERSEMBUNYI) ============ --}}
    {{-- Atribut capture inilah yang membuat HP LANGSUNG membuka kamera. --}}
    <input type="file"
           data-kamera-file
           accept="image/*"
           capture="{{ $kameraDepan ? 'user' : 'environment' }}"
           tabindex="-1"
           class="sr-only absolute h-px w-px opacity-0">

    {{-- ============ TOMBOL AKSI ============ --}}
    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
        <button type="button" data-kamera-ambil
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:flex-1">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 9a2 2 0 0 1 2-2h1.6a2 2 0 0 0 1.66-.89l.68-1.02A2 2 0 0 1 10.6 4h2.8a2 2 0 0 1 1.66.89l.68 1.02A2 2 0 0 0 17.4 7H19a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                <circle cx="12" cy="13" r="3.4" />
            </svg>
            <span data-kamera-teks-ambil>Ambil Foto</span>
        </button>

        <button type="button" data-kamera-ulang hidden
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5 sm:w-auto">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 4v5h5M20 20v-5h-5M20 9A8 8 0 0 0 6.3 5.3L4 8m0 7a8 8 0 0 0 13.7 3.7L20 16" />
            </svg>
            Ulangi
        </button>
    </div>

    {{-- Petunjuk bila dibuka dari komputer (capture tidak berlaku di desktop) --}}
    <p data-kamera-desktop hidden
       class="mt-2 rounded-xl border border-[#f5b301]/60 bg-[#fff8e6] px-3 py-2 text-[12px] font-medium text-[#8a6100]">
        Anda membuka halaman ini dari komputer. Agar tombol langsung membuka kamera,
        silakan lakukan absensi melalui HP.
    </p>

    <p data-kamera-status class="mt-2 text-[12px] font-medium text-[#5b616e]">
        Foto akan otomatis dikompres sebelum dikirim, jadi kuota Anda tetap hemat.
    </p>

    {{-- Nilai yang benar-benar dikirim ke server (data URL base64) --}}
    <input type="hidden" name="{{ $name }}" data-kamera-input value="">

    @error($name)
        <p class="mt-1 text-[12px] font-semibold text-[#cf202f]">{{ $message }}</p>
    @enderror
</div>

@once
    @push('scripts')
    <script>
    /* =====================================================================
     * KAMERA ABSENSI - versi "langsung buka kamera HP"
     * ---------------------------------------------------------------------
     * Tidak memakai getUserMedia sama sekali, sehingga:
     *  - tidak ada pop up minta izin kamera dari browser,
     *  - tidak perlu tombol "aktifkan kamera",
     *  - tidak wajib HTTPS.
     * Semua event dipasang di level document (event delegation) supaya tetap
     * bekerja walau komponen berada di dalam modal x-teleport milik Alpine.
     * ===================================================================== */
    (function () {
        if (window.__kameraAbsensiSiap) return;
        window.__kameraAbsensiSiap = true;

        var PESAN_WAJIB = 'Anda belum mengambil foto. Tekan tombol "Ambil Foto" untuk membuka kamera HP Anda.';

        function angka(nilai, cadangan) {
            var n = parseFloat(nilai);
            return isNaN(n) ? cadangan : n;
        }

        function el(kotak, pemilih) {
            return kotak.querySelector(pemilih);
        }

        function setStatus(kotak, teks, jenis) {
            var p = el(kotak, '[data-kamera-status]');
            if (!p) return;
            p.textContent = teks;
            p.className = 'mt-2 text-[12px] font-medium ' + (
                jenis === 'error' ? 'text-[#cf202f]' :
                jenis === 'ok'    ? 'text-[#05b169]' :
                                    'text-[#5b616e]'
            );
        }

        function sibuk(kotak, aktif) {
            var lapis = el(kotak, '[data-kamera-sibuk]');
            if (lapis) lapis.classList.toggle('hidden', !aktif);
            var tombol = el(kotak, '[data-kamera-ambil]');
            if (tombol) tombol.disabled = !!aktif;
        }

        function perkiraanByte(dataUrl) {
            var koma = dataUrl.indexOf(',');
            if (koma < 0) return dataUrl.length;
            return Math.round((dataUrl.length - koma - 1) * 3 / 4);
        }

        /* Membaca berkas foto jadi objek yang bisa digambar ke canvas.
           createImageBitmap dipakai lebih dulu karena otomatis membetulkan
           orientasi EXIF (foto HP sering miring 90 derajat). */
        function bacaGambar(berkas) {
            if (typeof window.createImageBitmap === 'function') {
                try {
                    return window.createImageBitmap(berkas, { imageOrientation: 'from-image' })
                        .catch(function () { return bacaLewatImg(berkas); });
                } catch (e) {
                    return bacaLewatImg(berkas);
                }
            }
            return bacaLewatImg(berkas);
        }

        function bacaLewatImg(berkas) {
            return new Promise(function (selesai, gagal) {
                var pembaca = new FileReader();
                pembaca.onload = function () {
                    var gambar = new Image();
                    gambar.onload = function () { selesai(gambar); };
                    gambar.onerror = function () { gagal(new Error('Gambar tidak terbaca')); };
                    gambar.src = pembaca.result;
                };
                pembaca.onerror = function () { gagal(new Error('Berkas tidak terbaca')); };
                pembaca.readAsDataURL(berkas);
            });
        }

        function gambarKe(canvas, sumber, lebar, tinggi) {
            canvas.width = lebar;
            canvas.height = tinggi;
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, lebar, tinggi);
            ctx.drawImage(sumber, 0, 0, lebar, tinggi);
        }

        /* Mengecilkan dimensi lalu menurunkan kualitas sampai di bawah target. */
        function kompres(canvas, sumber, maks, kualitas, targetByte) {
            var w0 = sumber.width || sumber.naturalWidth;
            var h0 = sumber.height || sumber.naturalHeight;
            if (!w0 || !h0) throw new Error('Ukuran gambar tidak valid');

            var skala = Math.min(1, maks / Math.max(w0, h0));
            var w = Math.max(1, Math.round(w0 * skala));
            var h = Math.max(1, Math.round(h0 * skala));

            gambarKe(canvas, sumber, w, h);

            var q = kualitas;
            var hasil = canvas.toDataURL('image/jpeg', q);

            while (perkiraanByte(hasil) > targetByte && q > 0.35) {
                q = Math.max(0.35, q - 0.08);
                hasil = canvas.toDataURL('image/jpeg', q);
            }

            var putaran = 0;
            while (perkiraanByte(hasil) > targetByte && putaran < 4 && Math.max(w, h) > 640) {
                w = Math.max(1, Math.round(w * 0.85));
                h = Math.max(1, Math.round(h * 0.85));
                gambarKe(canvas, sumber, w, h);
                hasil = canvas.toDataURL('image/jpeg', q);
                putaran++;
            }

            return hasil;
        }

        function tampilkanHasil(kotak, dataUrl) {
            var pratinjau = el(kotak, '[data-kamera-preview]');
            var kosong    = el(kotak, '[data-kamera-kosong]');
            var lencana   = el(kotak, '[data-kamera-lencana]');
            var tombolUlg = el(kotak, '[data-kamera-ulang]');
            var teks      = el(kotak, '[data-kamera-teks-ambil]');
            var tersembunyi = el(kotak, '[data-kamera-input]');

            if (tersembunyi) tersembunyi.value = dataUrl || '';

            if (dataUrl) {
                if (pratinjau) { pratinjau.src = dataUrl; pratinjau.classList.remove('hidden'); }
                if (kosong) kosong.classList.add('hidden');
                if (lencana) { lencana.classList.remove('hidden'); lencana.classList.add('flex'); }
                if (tombolUlg) tombolUlg.hidden = false;
                if (teks) teks.textContent = 'Foto Ulang';
            } else {
                if (pratinjau) { pratinjau.removeAttribute('src'); pratinjau.classList.add('hidden'); }
                if (kosong) kosong.classList.remove('hidden');
                if (lencana) { lencana.classList.add('hidden'); lencana.classList.remove('flex'); }
                if (tombolUlg) tombolUlg.hidden = true;
                if (teks) teks.textContent = 'Ambil Foto';
            }
        }

        function proses(kotak, berkas) {
            if (!berkas) return;

            if (berkas.type && berkas.type.indexOf('image/') !== 0) {
                setStatus(kotak, 'Berkas yang dipilih bukan gambar. Silakan ambil foto ulang.', 'error');
                return;
            }

            /* Pemeriksaan foto lama (opsional, aktif bila data-kamera-maks-usia > 0) */
            var maksUsia = angka(kotak.getAttribute('data-kamera-maks-usia'), 0);
            if (maksUsia > 0 && berkas.lastModified) {
                var usiaMenit = (Date.now() - berkas.lastModified) / 60000;
                if (usiaMenit > maksUsia) {
                    setStatus(kotak, 'Foto ini bukan foto baru. Silakan ambil foto langsung saat ini juga.', 'error');
                    if (window.swalPeringatan) {
                        window.swalPeringatan('Foto yang Anda pilih bukan foto baru. Silakan ambil foto langsung memakai kamera.', 'Foto Tidak Valid');
                    }
                    return;
                }
            }

            var maks     = angka(kotak.getAttribute('data-kamera-maks'), 1280);
            var kualitas = angka(kotak.getAttribute('data-kamera-kualitas'), 0.7);
            var targetKb = angka(kotak.getAttribute('data-kamera-target-kb'), 500);
            var canvas   = el(kotak, '[data-kamera-canvas]');

            sibuk(kotak, true);
            setStatus(kotak, 'Memproses foto...', 'info');

            bacaGambar(berkas).then(function (sumber) {
                var hasil = kompres(canvas, sumber, maks, kualitas, targetKb * 1024);
                if (sumber.close) { try { sumber.close(); } catch (e) {} }

                tampilkanHasil(kotak, hasil);

                var kb    = Math.round(perkiraanByte(hasil) / 1024);
                var kbAsl = Math.round((berkas.size || 0) / 1024);
                setStatus(
                    kotak,
                    'Foto siap dikirim. Ukuran ' + kbAsl + ' KB dikompres menjadi ' + kb + ' KB.',
                    'ok'
                );

                kotak.dispatchEvent(new CustomEvent('kamera-terambil', {
                    bubbles: true,
                    detail: { dataUrl: hasil, ukuranKb: kb }
                }));
            }).catch(function () {
                setStatus(
                    kotak,
                    'Foto gagal diproses. Bila memakai iPhone, ubah Pengaturan > Kamera > Format menjadi "Paling Kompatibel", lalu coba lagi.',
                    'error'
                );
            }).then(function () {
                sibuk(kotak, false);
            });
        }

        /* ---------------- Event: klik tombol ---------------- */
        document.addEventListener('click', function (ev) {
            var target = ev.target;
            if (!target || !target.closest) return;

            var tombolAmbil = target.closest('[data-kamera-ambil]');
            if (tombolAmbil) {
                var kotakA = tombolAmbil.closest('[data-kamera]');
                if (!kotakA) return;
                ev.preventDefault();
                var berkasInput = el(kotakA, '[data-kamera-file]');
                if (berkasInput) {
                    berkasInput.value = '';   // agar foto yang sama tetap memicu change
                    berkasInput.click();      // <== KAMERA HP LANGSUNG TERBUKA
                }
                return;
            }

            var tombolUlang = target.closest('[data-kamera-ulang]');
            if (tombolUlang) {
                var kotakU = tombolUlang.closest('[data-kamera]');
                if (!kotakU) return;
                ev.preventDefault();
                tampilkanHasil(kotakU, '');
                setStatus(kotakU, 'Foto dihapus. Tekan "Ambil Foto" untuk memotret ulang.', 'info');
                var ulangInput = el(kotakU, '[data-kamera-file]');
                if (ulangInput) {
                    ulangInput.value = '';
                    ulangInput.click();
                }
            }
        });

        /* ---------------- Event: foto selesai diambil ---------------- */
        document.addEventListener('change', function (ev) {
            var target = ev.target;
            if (!target || !target.matches || !target.matches('[data-kamera-file]')) return;

            var kotak = target.closest('[data-kamera]');
            if (!kotak) return;

            var berkas = target.files && target.files[0];
            if (!berkas) return;   // siswa menekan batal di aplikasi kamera

            proses(kotak, berkas);
        });

        /* ---------------- Penjaga: form tidak boleh dikirim tanpa foto ---------------- */
        document.addEventListener('submit', function (ev) {
            var form = ev.target;
            if (!form || !form.querySelectorAll) return;

            var daftar = form.querySelectorAll('[data-kamera][data-kamera-wajib="1"]');
            for (var i = 0; i < daftar.length; i++) {
                var tersembunyi = el(daftar[i], '[data-kamera-input]');
                if (!tersembunyi || !tersembunyi.value) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    setStatus(daftar[i], PESAN_WAJIB, 'error');
                    if (window.swalPeringatan) {
                        window.swalPeringatan(PESAN_WAJIB, 'Foto Belum Diambil');
                    } else {
                        alert(PESAN_WAJIB);
                    }
                    if (daftar[i].scrollIntoView) {
                        daftar[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }
            }
        }, true);

        /* ---------------- Petunjuk khusus pengguna desktop ---------------- */
        function tandaiDesktop() {
            var mobile = /Android|iPhone|iPad|iPod|Opera Mini|IEMobile|Mobile/i.test(navigator.userAgent);
            if (mobile) return;
            var semua = document.querySelectorAll('[data-kamera-desktop]');
            for (var i = 0; i < semua.length; i++) semua[i].hidden = false;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', tandaiDesktop);
        } else {
            tandaiDesktop();
        }
        document.addEventListener('alpine:initialized', tandaiDesktop);

        /* ---------------- Fungsi global (kompatibilitas) ---------------- */

        /* Dulu dipakai untuk mematikan stream getUserMedia. Sekarang tidak ada
           stream yang perlu dimatikan, jadi sengaja dibiarkan kosong supaya
           pemanggilan lama di halaman absensi tidak error. */
        window.kameraAbsensiTutupSemua = function () {};

        /* Mengosongkan foto pada satu komponen atau seluruh halaman. */
        window.kameraAbsensiReset = function (akar) {
            var lingkup = akar || document;
            var semua = lingkup.querySelectorAll ? lingkup.querySelectorAll('[data-kamera]') : [];
            for (var i = 0; i < semua.length; i++) {
                tampilkanHasil(semua[i], '');
                var berkasInput = el(semua[i], '[data-kamera-file]');
                if (berkasInput) berkasInput.value = '';
                setStatus(semua[i], 'Foto akan otomatis dikompres sebelum dikirim, jadi kuota Anda tetap hemat.', 'info');
            }
        };
    })();
    </script>
    @endpush
@endonce

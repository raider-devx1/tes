<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Daftar Hadir PKL Saya</h2>
        </div>
    </x-slot>

    <style>
        [x-cloak]{display:none!important;}
        .abs-desktop{ display:none; }
        .abs-mobile { display:block; }
        @media (min-width:1024px){
            .abs-desktop{ display:block; }
            .abs-mobile { display:none; }
        }
    </style>

    @php
        $statusUsulan = $siswa->status_jam_usulan ?? 'none';
        $jamMasukEfektif  = $siswa->jamMasukEfektif();
        $jamPulangEfektif = $siswa->jamPulangEfektif();
        $pakaiKhusus = $siswa->pakaiJamKhusus();
    @endphp

    <div class="py-8 md:py-12 bg-white min-h-screen"
         x-data="absensiPage({
            jamMasuk: '{{ $jendela['jam_masuk'] }}',
            jamPulang: '{{ $jendela['jam_pulang'] }}',
            durasi: {{ (int) $jendela['durasi'] }},
            nowJam: '{{ $jendela['now']->format('H:i') }}',
            faseServer: '{{ $jendela['fase'] }}',
            terbukaServer: {{ $jendela['terbuka'] ? 'true' : 'false' }},
            paksa: {{ ($jendela['paksa'] ?? false) ? 'true' : 'false' }},
            libur: {{ ($jendela['libur'] ?? false) ? 'true' : 'false' }},
            masukPaksa: {{ ($jendela['masuk_paksa'] ?? false) ? 'true' : 'false' }},
            pulangPaksa: {{ ($jendela['pulang_paksa'] ?? false) ? 'true' : 'false' }},
            sudahHadir: {{ ($absensiHariIni && $absensiHariIni->status === 'Hadir' && $absensiHariIni->jam_masuk) ? 'true' : 'false' }},
            sudahPulang: {{ ($absensiHariIni && $absensiHariIni->jam_pulang) ? 'true' : 'false' }},
            fotoDitolak: {{ ($absensiDitolak ?? null) ? 'true' : 'false' }}
         })"
         x-init="mulai()"
         x-on:keydown.escape.window="openAbsen=false; openJam=false; openGantiFoto=false">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12">

            <div class="mb-6">
                <a href="{{ route('siswa.dashboard') }}"
                   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- ============ PERINGATAN: FOTO ABSENSI DITOLAK GURU ============ --}}
            @if(!empty($absensiDitolak))
                <div class="mb-6 overflow-hidden rounded-2xl border-2 border-[#cf202f] bg-[#fdf2f3] shadow-sm">
                    <div class="flex items-center gap-2 bg-[#cf202f] px-4 py-2.5 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                        </svg>
                        <span class="text-sm font-bold uppercase tracking-wide">Absensi Anda Ditolak &mdash; Mohon Ganti Foto</span>
                    </div>

                    <div class="grid gap-4 p-4 sm:p-5 md:grid-cols-[1fr_auto] md:items-center">
                        <div class="space-y-2 text-sm text-black">
                            <p>
                                Absensi tanggal
                                <span class="font-bold">{{ \Carbon\Carbon::parse($absensiDitolak->tanggal)->translatedFormat('l, d F Y') }}</span>
                                ditolak oleh guru pembimbing karena foto tidak sesuai.
                                <span class="font-bold">Mohon lakukan absensi ulang dengan mengganti foto Anda.</span>
                            </p>

                            @if(filled($absensiDitolak->catatan_penolakan))
                                <div class="rounded-xl border-2 border-[#cf202f]/25 bg-white px-3 py-2.5">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-[#cf202f]">Catatan Guru</p>
                                    <p class="mt-0.5 whitespace-pre-line text-sm font-medium text-black">{{ $absensiDitolak->catatan_penolakan }}</p>
                                </div>
                            @endif

                            <ul class="list-disc space-y-1 pl-5 text-[13px] font-medium text-[#5b616e]">
                                <li>Data absensi Anda <span class="font-bold text-black">TETAP TERSIMPAN</span> (status, jam masuk, dan jam pulang tidak berubah).</li>
                                <li>Anda <span class="font-bold text-black">TIDAK akan dihitung Alpha</span> selama foto sudah diganti sebelum batas waktu.</li>
                                @if(!empty($batasGantiFoto))
                                    <li>Batas waktu ganti foto: <span class="font-bold text-[#cf202f]">{{ $batasGantiFoto->translatedFormat('l, d F Y') }} pukul {{ $batasGantiFoto->format('H:i') }} WITA</span> (sampai jam pulang berakhir).</li>
                                @endif
                                <li><span class="font-bold text-black">Absen pulang terkunci</span> sampai Anda mengganti foto.</li>
                            </ul>
                        </div>

                        <div class="flex flex-col gap-2 md:w-56">
                            @if($absensiDitolak->foto_bukti)
                                <img src="{{ asset('storage/'.$absensiDitolak->foto_bukti) }}" alt="Foto absensi yang ditolak"
                                     class="h-32 w-full rounded-xl border-2 border-[#cf202f]/30 object-cover">
                            @endif
                            <button type="button" @click="openGantiFoto=true"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#cf202f] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#a81824]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 0 1 2-2h1.6a2 2 0 0 0 1.66-.89l.68-1.02A2 2 0 0 1 10.6 4h2.8a2 2 0 0 1 1.66.89l.68 1.02A2 2 0 0 0 17.4 7H19a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                                    <circle cx="12" cy="13" r="3.4" />
                                </svg>
                                Ganti Foto Sekarang
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ===== MODAL GANTI FOTO ABSENSI YANG DITOLAK ===== --}}
                <template x-teleport="body">
                    {{-- Responsif: lembar bawah (bottom sheet) di HP, dialog tengah di laptop --}}
                    <div x-show="openGantiFoto" x-cloak class="fixed inset-0 z-[55] flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true">
                        <div class="absolute inset-0 bg-black/50" @click="openGantiFoto=false"></div>
                        <div class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:max-h-[88vh] sm:max-w-lg sm:rounded-2xl">

                            {{-- Kepala (tetap terlihat saat isi digulir) --}}
                            <div class="flex-none border-b border-[#e6e9ef] px-4 pb-3 pt-3 sm:px-6 sm:pt-5">
                                <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-[#d7dbe3] sm:hidden"></div>
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-base font-bold text-black sm:text-lg">Ganti Foto Absensi</h3>
                                        <p class="mt-0.5 text-[11px] font-medium text-[#5b616e] sm:text-xs">
                                            Tanggal {{ \Carbon\Carbon::parse($absensiDitolak->tanggal)->format('d/m/Y') }} &mdash;
                                            hanya foto yang diperbarui, jam absensi Anda tetap.
                                        </p>
                                    </div>
                                    <button type="button" @click="openGantiFoto=false" aria-label="Tutup"
                                            class="-mr-1 flex h-8 w-8 flex-none items-center justify-center rounded-lg text-xl leading-none text-[#5b616e] transition hover:bg-black/5 hover:text-black">&times;</button>
                                </div>
                            </div>

                            {{-- enctype WAJIB: foto dikirim sebagai berkas unggahan (bukan lagi data URL kamera) --}}
                            <form method="POST" action="{{ route('siswa.absensi.ganti-foto', $absensiDitolak->id) }}" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col">
                                @csrf

                                {{-- Isi yang bisa digulir --}}
                                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">

                                @if(filled($absensiDitolak->catatan_penolakan))
                                    <div class="rounded-xl border-2 border-[#cf202f]/25 bg-[#fdf2f3] px-3 py-2.5">
                                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#cf202f]">Alasan Penolakan Guru</p>
                                        <p class="mt-0.5 whitespace-pre-line text-sm font-medium text-black">{{ $absensiDitolak->catatan_penolakan }}</p>
                                    </div>
                                @endif

                                {{-- Unggah foto pengganti (maks 3 MB). Peringatan ketentuan foto ada di dalam komponen. --}}
                                <x-upload-foto-absensi name="foto_bukti" label="Foto Pengganti (Unggah Foto)" :wajib="true" :maks-mb="3" />

                                @if(!empty($batasGantiFoto))
                                    <p class="text-[11px] font-medium text-[#5b616e]">
                                        Batas waktu: <span class="font-bold text-[#cf202f]">{{ $batasGantiFoto->format('d/m/Y H:i') }} WITA</span>.
                                        Lewat dari itu, absensi otomatis ditandai Alpha.
                                    </p>
                                @endif

                                </div>

                                {{-- Kaki: tombol selalu terlihat, menumpuk di HP --}}
                                <div class="flex-none border-t border-[#e6e9ef] bg-white px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] sm:px-6 sm:pb-3">
                                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                        <button type="button" @click="openGantiFoto=false"
                                                class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5 sm:w-auto sm:py-2">Batal</button>
                                        <button type="submit"
                                                class="w-full rounded-xl bg-[#05b169] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#049a5b] sm:w-auto sm:py-2">Kirim Foto Baru</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
            @endif

            {{-- ===================== REKAP ===================== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="rounded-2xl border-2 border-[#05b169]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-xl sm:text-2xl md:text-3xl font-black text-[#05b169]">{{ $rekap['Hadir'] ?? 0 }}</p>
                    <p class="text-xs sm:text-sm font-bold text-black mt-1">Hadir</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-xl sm:text-2xl md:text-3xl font-black text-[#0047d6]">{{ $rekap['Izin'] ?? 0 }}</p>
                    <p class="text-xs sm:text-sm font-bold text-black mt-1">Izin</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-xl sm:text-2xl md:text-3xl font-black text-[#d98200]">{{ $rekap['Sakit'] ?? 0 }}</p>
                    <p class="text-xs sm:text-sm font-bold text-black mt-1">Sakit</p>
                </div>
                <div class="rounded-2xl border-2 border-[#cf202f]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-xl sm:text-2xl md:text-3xl font-black text-[#cf202f]">{{ $rekap['Alpha'] ?? 0 }}</p>
                    <p class="text-xs sm:text-sm font-bold text-black mt-1">Alpha</p>
                </div>
            </div>

            {{-- ============== PENGATURAN JAM & HARI KERJA (USULAN KE GURU) ============== --}}
            <div class="mb-6 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Jam &amp; Hari Kerja Industri</h3>
                        <p class="text-xs font-medium text-[#5b616e] mt-0.5">
                            Jam dari admin: <span class="font-bold text-black">{{ substr($jamAdmin['masuk'],0,5) }} – {{ substr($jamAdmin['pulang'],0,5) }} WITA</span>.
                            Bila jam atau hari kerja tidak sesuai template industri Anda, ajukan jam &amp; hari kerja khusus ke guru pembimbing.
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-bold">
                            <span class="rounded-full bg-[#0047d6]/10 px-3 py-1 text-[#0047d6]">Jam berlaku: {{ substr($jamMasukEfektif,0,5) }} – {{ substr($jamPulangEfektif,0,5) }}</span>
                            <span class="rounded-full bg-[#0047d6]/10 px-3 py-1 text-[#0047d6]">Hari kerja: {{ $siswa->labelHariKerja() }}</span>
                            @if($siswa->pakaiHariKerjaKhusus())
                                <span class="rounded-full bg-[#05b169]/15 px-3 py-1 text-[#05b169]">Hari kerja khusus disetujui</span>
                            @endif
                            @if($statusUsulan === 'diajukan')
                                <span class="rounded-full bg-[#d98200]/15 px-3 py-1 text-[#d98200]">Usulan menunggu validasi guru</span>
                            @elseif($statusUsulan === 'disetujui' && $pakaiKhusus)
                                <span class="rounded-full bg-[#05b169]/15 px-3 py-1 text-[#05b169]">Jam khusus disetujui</span>
                            @else
                                <span class="rounded-full bg-[#5b616e]/15 px-3 py-1 text-[#5b616e]">Mengikuti jam admin</span>
                            @endif
                        </div>
                        @if($statusUsulan === 'diajukan')
                            <p class="mt-2 text-xs font-medium text-[#5b616e]">Diajukan: {{ substr($siswa->jam_masuk_usulan,0,5) }} – {{ substr($siswa->jam_pulang_usulan,0,5) }}
                                · Hari: {{ $siswa->punyaUsulanHariKerja() ? $siswa->labelHariKerjaUsulan() : 'tidak diubah' }}
                                @if($siswa->catatan_jam_usulan) · “{{ $siswa->catatan_jam_usulan }}” @endif</p>
                        @endif
                    </div>
                    <div>
                        <button type="button" @click="openJam=true"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pengaturan Jam &amp; Hari
                        </button>
                    </div>
                </div>

                {{-- MODAL: Ajukan Jam & Hari Kerja Khusus (satu pengajuan) --}}
                <template x-teleport="body">
                    {{-- Responsif: lembar bawah (bottom sheet) di HP, dialog tengah di laptop --}}
                    <div x-show="openJam" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true">
                        <div class="absolute inset-0 bg-black/50" @click="openJam=false"></div>
                        <div class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:max-h-[88vh] sm:max-w-lg sm:rounded-2xl">

                            <div class="flex-none border-b border-[#e6e9ef] px-4 pb-3 pt-3 sm:px-6 sm:pt-5">
                                <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-[#d7dbe3] sm:hidden"></div>
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-base font-bold text-black sm:text-lg">Ajukan Jam &amp; Hari Kerja Industri</h3>
                                    <button type="button" @click="openJam=false" aria-label="Tutup"
                                            class="-mr-1 flex h-8 w-8 flex-none items-center justify-center rounded-lg text-xl leading-none text-[#5b616e] transition hover:bg-black/5 hover:text-black">&times;</button>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('siswa.absensi.jam.ajukan') }}" class="flex min-h-0 flex-1 flex-col">
                                @csrf
                                @method('PUT')

                                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">
                                @php
                                    // Pilihan dropdown hari: Senin s.d. Minggu.
                                    // Nilai awal mengikuti usulan yang masih menunggu validasi;
                                    // bila belum ada, memakai hari kerja yang sedang berlaku.
                                    $pilihanHariUsulan = $daftarHari ?? \App\Models\User::daftarHari();
                                    $hariAwalTerpilih   = $siswa->hariAwalUsulan()  ?? $siswa->hariAwalEfektif();
                                    $hariAkhirTerpilih  = $siswa->hariAkhirUsulan() ?? $siswa->hariAkhirEfektif();
                                @endphp
                                <p class="text-xs font-medium text-[#5b616e]">Usulan jam <b>dan</b> hari kerja dikirim sekaligus ke guru pembimbing untuk divalidasi. Selama menunggu, Anda tetap memakai jam &amp; hari kerja yang berlaku sekarang.</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Masuk</label>
                                        <x-jam-picker name="jam_masuk_usulan" :value="substr($siswa->jam_masuk_usulan ?? $jamMasukEfektif,0,5)" required />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Pulang</label>
                                        <x-jam-picker name="jam_pulang_usulan" :value="substr($siswa->jam_pulang_usulan ?? $jamPulangEfektif,0,5)" required />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Hari Awal</label>
                                        <select name="hari_awal_usulan" required
                                                class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-bold text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                            @foreach($pilihanHariUsulan as $kunciHari => $labelHari)
                                                <option value="{{ $kunciHari }}" @selected($hariAwalTerpilih === $kunciHari)>{{ $labelHari }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Hari Akhir</label>
                                        <select name="hari_akhir_usulan" required
                                                class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-bold text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                            @foreach($pilihanHariUsulan as $kunciHari => $labelHari)
                                                <option value="{{ $kunciHari }}" @selected($hariAkhirTerpilih === $kunciHari)>{{ $labelHari }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <p class="rounded-xl bg-[#0047d6]/5 px-3 py-2 text-xs font-medium text-[#5b616e]">
                                    Hari kerja berlaku sekarang: <span class="font-bold text-black">{{ $siswa->labelHariKerja() }}</span>.
                                    Hari di luar rentang yang Anda pilih akan dilewati — tidak perlu absen dan tidak dihitung Alpha.
                                </p>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Catatan (opsional)</label>
                                    <textarea name="catatan_jam_usulan" rows="2" placeholder="Contoh: jam kerja industri 07:30 - 15:30"
                                              class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">{{ $siswa->catatan_jam_usulan }}</textarea>
                                </div>
                                </div>

                                <div class="flex-none border-t border-[#e6e9ef] bg-white px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] sm:px-6 sm:pb-3">
                                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                        <button type="button" @click="openJam=false" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5 sm:w-auto sm:py-2">Batal</button>
                                        <button type="submit" class="w-full rounded-xl bg-[#0047d6] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa] sm:w-auto sm:py-2">Ajukan Jam &amp; Hari ke Guru</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ===================== MENU ABSENSI ===================== --}}
            <div class="mb-6 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm">
                <div class="mb-4">
                    <h3 class="text-lg font-bold tracking-tight text-black">Menu Absensi</h3>
                    <p class="text-xs font-medium text-[#5b616e]">Halaman absensi hanya dibuka <span class="font-bold">{{ (int) $jendela['durasi'] }} menit</span> saat jam masuk (pukul {{ substr($jamMasukEfektif,0,5) }}) lalu tertutup sampai jam pulang (pukul {{ substr($jamPulangEfektif,0,5) }}). Jika tidak absen sampai batas waktu, status otomatis <span class="font-bold text-[#cf202f]">Alpha</span>.</p>
                </div>

                @if (! empty($jendela['libur']))
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3">
                        <div class="flex items-start gap-2 text-sm font-bold text-[#cf202f]">
                            <span class="mt-1.5 inline-block h-2.5 w-2.5 shrink-0 rounded-full bg-[#cf202f]"></span>
                            <span>Hari ini tanggal merah: {{ $jendela['libur_nama'] }}</span>
                        </div>
                        <p class="mt-1 pl-4 text-xs font-semibold text-[#cf202f]">
                            Absensi tidak perlu diisi dan hari ini tidak akan dihitung Alpha.
                        </p>
                    </div>
                @endif

                <div class="mb-4 rounded-xl border-2 px-4 py-3" :class="terbuka ? 'border-[#05b169] bg-[#05b169]/10' : 'border-[#cf202f] bg-[#cf202f]/10'">
                    <div class="flex items-center gap-2 text-sm font-bold" :class="terbuka ? 'text-[#05b169]' : 'text-[#cf202f]'">
                        <span class="inline-block h-2.5 w-2.5 rounded-full" :class="terbuka ? 'bg-[#05b169]' : 'bg-[#cf202f]'"></span>
                        <span x-text="statusLabel"></span>
                    </div>
                    <p class="mt-1 text-xs font-semibold" :class="terbuka ? 'text-[#05b169]' : 'text-[#cf202f]'" x-text="countdownLabel"></p>
                </div>

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <form method="GET" action="{{ route('siswa.absensi.index') }}" class="flex flex-wrap items-center gap-2">
                        <input type="month" name="bulan" value="{{ request('bulan') }}"
                               class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <button type="submit" class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Filter</button>
                        @if(request('bulan'))
                            <a href="{{ route('siswa.absensi.index') }}" class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        @endif
                    </form>

                    <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                        {{-- Tombol Absen (buka form) --}}
                        <button type="button" @click="if (fotoDitolak) { window.swalPeringatan('Foto absensi Anda ditolak guru pembimbing. Ganti foto terlebih dahulu.', 'Ganti Foto Dulu'); return; } if (terbuka) openAbsen=true" :disabled="!terbuka || fotoDitolak"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-sm transition"
                                :class="(terbuka && !fotoDitolak) ? 'bg-[#05b169] hover:bg-[#049a5b] cursor-pointer' : 'bg-[#5b616e]/40 cursor-not-allowed'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span x-text="fotoDitolak ? 'Ganti Foto Dulu' : (terbuka ? 'Absen Sekarang' : 'Absensi Tertutup')"></span>
                        </button>

                        {{-- Tombol Absen Pulang cepat (jika sudah hadir & fase pulang).
                             DISEMBUNYIKAN selama masih ada foto yang ditolak guru. --}}
                        <form method="POST" action="{{ route('siswa.absensi.store') }}" x-show="terbuka && fase==='pulang' && sudahHadir && !sudahPulang && !fotoDitolak" x-cloak>
                            @csrf
                            <input type="hidden" name="aksi" value="pulang">
                            <input type="hidden" name="status" value="Hadir">
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                Absen Pulang
                            </button>
                        </form>

                        {{-- Absen pulang TERKUNCI: wajib ganti foto lebih dulu. --}}
                        <button type="button" x-show="fotoDitolak && sudahHadir && !sudahPulang" x-cloak
                                @click="openGantiFoto=true"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#5b616e]/40 px-6 py-3 text-sm font-bold text-white shadow-sm cursor-not-allowed"
                                title="Ganti foto yang ditolak terlebih dahulu">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75M6.75 10.5h10.5a2.25 2.25 0 0 1 2.25 2.25v6A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75v-6a2.25 2.25 0 0 1 2.25-2.25Z"/></svg>
                            Absen Pulang Terkunci
                        </button>

                        <a href="{{ route('cetak.absensi', request()->only('bulan')) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:opacity-90" style="background-color:#cf202f;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Cetak Rekap PDF
                        </a>
                    </div>
                </div>

                {{-- ===== MODAL ABSEN (status + foto wajib + catatan opsional) ===== --}}
                <template x-teleport="body">
                    {{-- Responsif: lembar bawah (bottom sheet) di HP, dialog tengah di laptop --}}
                    <div x-show="openAbsen" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true">
                        <div class="absolute inset-0 bg-black/50" @click="openAbsen=false"></div>
                        <div class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:max-h-[88vh] sm:max-w-lg sm:rounded-2xl">

                            <div class="flex-none border-b border-[#e6e9ef] px-4 pb-3 pt-3 sm:px-6 sm:pt-5">
                                <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-[#d7dbe3] sm:hidden"></div>
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="text-base font-bold text-black sm:text-lg">Form Absensi Hari Ini</h3>
                                    <button type="button" @click="openAbsen=false" aria-label="Tutup"
                                            class="-mr-1 flex h-8 w-8 flex-none items-center justify-center rounded-lg text-xl leading-none text-[#5b616e] transition hover:bg-black/5 hover:text-black">&times;</button>
                                </div>
                            </div>

                            {{-- enctype WAJIB: foto dikirim sebagai berkas unggahan (bukan lagi data URL kamera) --}}
                            <form method="POST" action="{{ route('siswa.absensi.store') }}" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col"
                                  @submit="if (!terbuka) { $event.preventDefault(); window.swalPeringatan('Halaman absensi sudah tertutup.'); }">
                                @csrf

                                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Kehadiran</label>
                                    <select name="status" x-model="status"
                                            class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                        <option value="Hadir">Hadir</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Izin">Izin</option>
                                    </select>
                                    <p class="mt-1 text-[11px] font-medium text-[#5b616e]">Alpha ditetapkan otomatis oleh sistem bila Anda tidak absen sampai batas waktu.</p>
                                </div>

                                <template x-if="status==='Hadir'">
                                    <div class="rounded-xl border-2 border-[#05b169]/25 bg-[#05b169]/5 px-3 py-2.5 text-xs font-medium text-[#5b616e]">
                                        Jam <span class="font-bold text-black" x-text="fase==='pulang' ? 'pulang' : 'masuk'"></span> akan dicatat otomatis: <span class="font-bold text-black" x-text="nowJam"></span> WITA.
                                    </div>
                                </template>

                                {{-- ===== UNGGAH FOTO BUKTI KEHADIRAN (MAKS 3 MB) ===== --}}
                                {{-- Kamera langsung sudah diganti dengan unggah foto dari perangkat.
                                     Peringatan ketentuan foto TETAP ditampilkan di dalam komponen. --}}
                                @if(($absensiHariIni && $absensiHariIni->foto_bukti))
                                    <div class="rounded-xl border-2 border-[#05b169]/25 bg-[#05b169]/5 px-3 py-2.5 text-xs font-medium text-[#5b616e]">
                                        Foto absen masuk Anda hari ini sudah tersimpan, jadi tidak perlu mengunggah foto lagi.
                                    </div>
                                @else
                                    <x-upload-foto-absensi name="foto_bukti" label="Foto Bukti Kehadiran (Unggah Foto)" :wajib="true" :maks-mb="3" />
                                @endif

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Catatan (opsional)</label>
                                    <textarea name="catatan_instruktur" rows="2" placeholder="Catatan tambahan (opsional)"
                                              class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                                </div>

                                </div>

                                <div class="flex-none border-t border-[#e6e9ef] bg-white px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] sm:px-6 sm:pb-3">
                                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                        <button type="button" @click="openAbsen=false" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5 sm:w-auto sm:py-2">Batal</button>
                                        <button type="submit" class="w-full rounded-xl bg-[#05b169] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#049a5b] sm:w-auto sm:py-2">Kirim Absensi</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ===================== RIWAYAT ===================== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">
                <div class="mb-6"><h3 class="text-lg font-bold tracking-tight text-black">Riwayat Kehadiran Saya</h3></div>

                @php
                    // Daftar id absensi yang fotonya sedang ditolak (untuk badge riwayat).
                    $idDitolak = ($absensiDitolak ?? null) ? [$absensiDitolak->id] : [];

                    $badgeStatus = fn($s) => match ($s) {
                        'Hadir' => 'bg-[#05b169] text-white',
                        'Izin'  => 'bg-[#0047d6] text-white',
                        'Sakit' => 'bg-[#d98200] text-white',
                        'Alpha' => 'bg-[#cf202f] text-white',
                        default => 'bg-[#5b616e] text-white',
                    };
                    $svData = fn($sv) => match ($sv) {
                        'disetujui' => ['bg-[#05b169] text-white', 'Tervalidasi'],
                        'diajukan'  => ['bg-[#d98200] text-white', 'Menunggu Validasi'],
                        default     => ['bg-[#5b616e]/15 text-[#5b616e]', 'Draft'],
                    };
                @endphp

                {{-- DESKTOP --}}
                <div class="abs-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[880px] text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center font-bold w-12">No</th>
                                <th class="px-4 py-3 text-left font-bold w-40">Tanggal</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Masuk</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Pulang</th>
                                <th class="px-4 py-3 text-center font-bold w-48">Validasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($absensis as $a)
                                @php
                                    [$svBadge, $svLabel] = $svData($a->status_validasi ?? 'draft');
                                    $jamMasuk  = $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '';
                                    $jamPulang = $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '';
                                    $tglLabel  = \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y');
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $tglLabel }}</td>
                                    <td class="px-4 py-3 text-center"><span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badgeStatus($a->status) }}">{{ $a->status }}</span>@if($a->telat_masuk)<span class="mt-1 block text-[10px] font-bold text-[#d98200]">Telat Masuk</span>@endif</td>
                                    <td class="px-4 py-3 text-center font-medium text-black">{{ $jamMasuk ?: '-' }}</td>
                                    <td class="px-4 py-3 text-center font-medium text-black">{{ $jamPulang ?: '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}">{{ $svLabel }}</span>
                                        @if($a->foto_bukti)
                                            <div class="mt-1"><a href="{{ asset('storage/'.$a->foto_bukti) }}" download target="_blank" rel="noopener" class="text-xs font-bold text-[#0047d6] underline hover:text-[#0038aa]">Lihat Bukti</a></div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-sm font-medium text-[#5b616e]">Belum ada data absensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE --}}
                <div class="abs-mobile space-y-3">
                    @forelse ($absensis as $a)
                        @php
                            [$svBadge, $svLabel] = $svData($a->status_validasi ?? 'draft');
                            $jamMasuk  = $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-';
                            $jamPulang = $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '-';
                            $tglLabel  = \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y');
                        @endphp
                        <div class="rounded-xl border-2 border-[#0047d6]/15 p-4">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-black">{{ $tglLabel }}</p>
                                <div class="text-right"><span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badgeStatus($a->status) }}">{{ $a->status }}</span>@if($a->telat_masuk)<span class="mt-1 block text-[10px] font-bold text-[#d98200]">Telat Masuk</span>@endif</div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2 text-xs font-medium text-[#5b616e]">
                                <p>Masuk: <span class="font-bold text-black">{{ $jamMasuk }}</span></p>
                                <p>Pulang: <span class="font-bold text-black">{{ $jamPulang }}</span></p>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}">{{ $svLabel }}</span>
                                @if($a->foto_bukti)<a href="{{ asset('storage/'.$a->foto_bukti) }}" download target="_blank" rel="noopener" class="text-xs font-bold text-[#0047d6] underline">Lihat Bukti</a>@endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border-2 border-[#0047d6]/15 p-6 text-center text-sm font-medium text-[#5b616e]">Belum ada data absensi.</div>
                    @endforelse
                </div>

                @if (method_exists($absensis, 'links'))
                    <div class="mt-4">{{ $absensis->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function absensiPage(cfg) {
            const toMin = (hhmm) => {
                const [h, m] = String(hhmm).split(':').map(Number);
                return (h || 0) * 60 + (m || 0);
            };
            return {
                jamMasuk: cfg.jamMasuk,
                jamPulang: cfg.jamPulang,
                durasi: cfg.durasi,
                nowJam: cfg.nowJam,
                fase: cfg.faseServer,
                terbuka: cfg.terbukaServer,
                paksa: cfg.paksa,
                masukPaksa: cfg.masukPaksa,
                pulangPaksa: cfg.pulangPaksa,
                // TANGGAL MERAH: absensi tidak perlu diisi. Server sudah
                // menutup jendelanya; flag ini menahan perhitungan ulang di
                // browser supaya tidak membuka kembali saat jam masuk tiba.
                libur: !!cfg.libur,
                sudahHadir: cfg.sudahHadir,
                sudahPulang: cfg.sudahPulang,
                // Ada foto absensi yang DITOLAK guru & belum diganti.
                // Selama true: tombol absen & absen pulang dikunci.
                fotoDitolak: !!cfg.fotoDitolak,
                status: 'Hadir',
                openAbsen: false,
                openGantiFoto: false,
                openJam: false,
                nowMin: 0,
                statusLabel: '',
                countdownLabel: '',
                get labelFoto() {
                    return this.status === 'Hadir'
                        ? 'Foto wajah dengan latar tempat industri'
                        : 'Foto bukti izin/sakit';
                },
                hitung() {
                    const m = toMin(this.jamMasuk), p = toMin(this.jamPulang), d = this.durasi;
                    const n = this.nowMin;
                    // TANGGAL MERAH: jendela tetap tertutup sepanjang hari,
                    // jadi perhitungan jam di bawah tidak boleh dijalankan.
                    if (this.libur) {
                        this.fase = 'libur';
                        this.terbuka = false;
                        this.nowJam = String(Math.floor(n / 60)).padStart(2, '0') + ':' + String(n % 60).padStart(2, '0');
                        this.statusLabel = 'Hari ini TANGGAL MERAH';
                        this.countdownLabel = 'Absensi tidak perlu diisi dan Anda tidak dihitung Alpha.';
                        return;
                    }
                    let fase = 'tutup', terbuka = false;
                    if (n >= m && n <= m + d) { fase = 'masuk'; terbuka = true; }
                    else if (n >= p && n <= p + d) { fase = 'pulang'; terbuka = true; }
                    this.fase = fase; this.terbuka = terbuka;
                    const hhmm = String(Math.floor(n / 60)).padStart(2, '0') + ':' + String(n % 60).padStart(2, '0');
                    this.nowJam = hhmm;
                    // Buka-paksa TERPISAH untuk masuk / pulang. Fase yang tidak
                    // dibuka-paksa tetap mengikuti jadwal jam.
                    if (this.masukPaksa || this.pulangPaksa) {
                        const masukOpen  = (n >= m && n <= m + d) || this.masukPaksa;
                        const pulangOpen = (n >= p && n <= p + d) || this.pulangPaksa;
                        if (masukOpen && pulangOpen) {
                            this.terbuka = true;
                            this.fase = this.sudahHadir ? 'pulang' : 'masuk';
                        } else if (masukOpen) {
                            this.terbuka = true; this.fase = 'masuk';
                        } else if (pulangOpen) {
                            this.terbuka = true; this.fase = 'pulang';
                        } else {
                            this.terbuka = false; this.fase = 'tutup';
                        }
                        this.statusLabel = this.terbuka ? 'Absensi DIBUKA oleh admin' : 'Absensi TERTUTUP';
                        this.countdownLabel = this.terbuka
                            ? ('Dibuka oleh admin (bebas waktu) — fase ' + (this.fase === 'masuk' ? 'masuk' : 'pulang') + '.')
                            : 'Belum ada fase yang dibuka.';
                        return;
                    }
                    if (terbuka) {
                        const sisa = (fase === 'masuk' ? (m + d) : (p + d)) - n;
                        this.statusLabel = 'Absensi TERBUKA (fase ' + (fase === 'masuk' ? 'masuk' : 'pulang') + ')';
                        this.countdownLabel = 'Sisa waktu ± ' + sisa + ' menit.';
                    } else {
                        this.statusLabel = 'Absensi TERTUTUP';
                        if (n < m) this.countdownLabel = 'Absen masuk dibuka pukul ' + this.jamMasuk + ' WITA.';
                        else if (n < p) this.countdownLabel = 'Absen pulang dibuka pukul ' + this.jamPulang + ' WITA.';
                        else this.countdownLabel = 'Absensi berikutnya dibuka besok pukul ' + this.jamMasuk + ' WITA.';
                    }
                },
                mulai() {
                    this.nowMin = toMin(this.nowJam);
                    this.hitung();
                    setInterval(() => { this.nowMin = (this.nowMin + 1) % (24 * 60); this.hitung(); }, 60000);
                },
            };
        }
    </script>
</x-app-layout>

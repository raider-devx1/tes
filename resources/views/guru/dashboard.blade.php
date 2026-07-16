<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Dashboard Guru Pembimbing</h2>
    </x-slot>

    {{--
        Strategi responsif full-width:
        - Kontainer TIDAK dibatasi max-w-* → konten mengisi penuh lebar layar secara fleksibel.
        - Lebar dijaga rapi lewat padding kiri-kanan bertingkat:
          HP: px-4 · tablet: px-6 · laptop: px-8 · PC: px-12 · layar besar: px-16
        - Grid menu menambah kolom otomatis: 1 (HP) → 2 (md) → 3 (lg) → 4 (2xl)
    --}}
    <div class="py-6 md:py-8 lg:py-10 bg-slate-50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 space-y-6 lg:space-y-8">

            @include('partials.notifikasi')

            {{-- ===== BARIS ATAS: SAPAAN + STATISTIK (full width, 2 kolom di layar lebar) ===== --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-6">

                {{-- Sapaan (mengisi 2/3 layar di PC) --}}
                <div class="xl:col-span-2 rounded-2xl bg-[#0047d6] p-6 sm:p-8 lg:p-10 text-white shadow-sm flex flex-col justify-center">
                    <p class="text-sm font-semibold text-white/80">Ruang Guru Pembimbing</p>
                    <h3 class="mt-1 text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Halo, {{ auth()->user()->name }} 👋
                    </h3>
                    <p class="mt-2 text-white/85 font-medium lg:text-lg max-w-2xl">
                        Selamat datang di pusat monitoring PKL. Pilih menu untuk memantau kegiatan siswa bimbingan Anda.
                    </p>
                </div>

                {{-- Panel statistik (mengisi 1/3 layar di PC, 2x2 di HP) --}}
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    <div class="rounded-2xl bg-white border border-slate-100 p-4 lg:p-5 shadow-sm flex flex-col justify-center">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Total Bimbingan</p>
                        <p class="mt-1 text-3xl lg:text-4xl font-extrabold text-[#0047d6]">{{ $siswaBimbingan ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-100 p-4 lg:p-5 shadow-sm flex flex-col justify-center">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Aktif PKL</p>
                        <p class="mt-1 text-3xl lg:text-4xl font-extrabold text-emerald-600">{{ $siswaAktif ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-100 p-4 lg:p-5 shadow-sm flex flex-col justify-center">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Belum Mulai</p>
                        <p class="mt-1 text-3xl lg:text-4xl font-extrabold text-[#8B5E34]">{{ $siswaBelum ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl bg-white border border-slate-100 p-4 lg:p-5 shadow-sm flex flex-col justify-center">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Selesai</p>
                        <p class="mt-1 text-3xl lg:text-4xl font-extrabold text-slate-700">{{ $siswaSelesai ?? 0 }}</p>
                    </div>
                </div>

            </div>

            {{-- ===== TOMBOL UTAMA (full width) ===== --}}
            <a href="{{ route('guru.siswa.index') }}"
               class="group flex items-center gap-4 rounded-2xl bg-[#8B5E34] px-5 py-5 lg:px-8 lg:py-6 text-white shadow-md active:scale-[0.99] hover:brightness-105 transition">
                <span class="flex h-14 w-14 lg:h-16 lg:w-16 flex-shrink-0 items-center justify-center rounded-xl bg-white/20">
                    <svg class="h-7 w-7 lg:h-8 lg:w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h4 class="text-lg sm:text-xl lg:text-2xl font-bold">Daftar Siswa &amp; Monitoring</h4>
                    <p class="text-sm lg:text-base text-white/85 leading-snug">Lihat semua siswa bimbingan dan mulai pantau kegiatan mereka.</p>
                </div>
                <svg class="h-6 w-6 lg:h-7 lg:w-7 flex-shrink-0 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>

            {{-- ===== GRUP 1: MONITORING HARIAN ===== --}}
            <div>
                <h4 class="px-1 mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">Monitoring Harian</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3 sm:gap-4">

                    {{-- Jurnal --}}
                    <a href="{{ route('guru.monitoring.jurnal') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#0047d6]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Jurnal Siswa</h3>
                            <p class="text-sm text-slate-500 leading-snug">Baca &amp; setujui jurnal harian siswa.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-slate-300 group-hover:text-[#0047d6] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                    {{-- Absensi --}}
                    <a href="{{ route('guru.monitoring.absensi') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#8B5E34]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#8B5E34]/10 text-[#8B5E34]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Absensi Siswa</h3>
                            <p class="text-sm text-slate-500 leading-snug">Cek kehadiran: hadir, izin, sakit, alpha.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-slate-300 group-hover:text-[#8B5E34] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                    {{-- Catatan Kegiatan --}}
                    <a href="{{ route('guru.catatan.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#0047d6]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Catatan Kegiatan</h3>
                            <p class="text-sm text-slate-500 leading-snug">Baca refleksi &amp; catatan yang ditulis siswa.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-slate-300 group-hover:text-[#0047d6] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                </div>
            </div>

            {{-- ===== GRUP 2: PENILAIAN & LAPORAN ===== --}}
            <div>
                <h4 class="px-1 mb-3 text-sm font-bold uppercase tracking-wide text-slate-400">Penilaian &amp; Laporan</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3 sm:gap-4">

                    {{-- Observasi --}}
                    <a href="{{ route('guru.observasi.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#8B5E34]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#8B5E34]/10 text-[#8B5E34]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Lembar Observasi</h3>
                            <p class="text-sm text-slate-500 leading-snug">Catat kunjungan, masalah, dan solusinya.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-slate-300 group-hover:text-[#8B5E34] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                    {{-- Rekap Nilai --}}
                    <a href="{{ route('guru.nilai.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#0047d6]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Rekap Nilai Siswa</h3>
                            <p class="text-sm text-slate-500 leading-snug">Lihat &amp; unduh nilai perkembangan siswa.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-slate-300 group-hover:text-[#0047d6] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                    {{-- Dokumen --}}
                    <a href="{{ route('guru.dokumen.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#8B5E34]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#8B5E34]/10 text-[#8B5E34]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Dokumen Siswa</h3>
                            <p class="text-sm text-slate-500 leading-snug">Surat tugas, surat penerimaan, &amp; laporan PKL.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-slate-300 group-hover:text-[#8B5E34] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                </div>
            </div>

            {{-- ===== BANTUAN SINGKAT ===== --}}
            <div class="rounded-2xl border border-[#8B5E34]/20 bg-[#8B5E34]/5 px-5 py-4 text-sm lg:text-base text-[#5b4a35]">
                <span class="font-semibold">Petunjuk:</span> Ketuk salah satu menu di atas untuk membukanya. Tanda
                <span class="font-bold">›</span> berarti menu akan membuka halaman baru. Untuk kembali, gunakan tombol kembali di HP atau browser Anda.
            </div>

        </div>
    </div>
</x-app-layout>
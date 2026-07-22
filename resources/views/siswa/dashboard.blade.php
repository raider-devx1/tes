<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Dashboard Siswa PKL</h2>
    </x-slot>

    {{--
        Strategi responsif full-width (360px → 1920px):
        - Kontainer memakai w-full + max-w-[1920px] mx-auto
          → konten mengisi PENUH lebar layar kiri-kanan, lalu berhenti melebar
            saat mencapai 1920px agar tetap rapi di monitor besar.
        - Padding bertingkat menjaga jarak tepi tetcukup di semua ukuran:
          HP: px-4 · tablet: px-6 · laptop: px-8 · PC: px-12 · layar besar: px-16
        - Grid menu menambah kolom otomatis: 1 (HP) → 2 (md) → 3 (lg) → 4 (2xl)
    --}}
    <div class="py-6 md:py-8 lg:py-10 bg-slate-50 min-h-screen">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 space-y-6 lg:space-y-8">

            @include('partials.notifikasi')

            {{-- ===== BARIS ATAS: SAPAAN + STATISTIK (full width, 2 kolom di layar lebar) ===== --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-6">

                {{-- Petunjuk untuk siswa (mengisi 2/3 layar di PC) --}}
                <div class="xl:col-span-2 rounded-2xl bg-[#0047d6] p-6 sm:p-8 lg:p-10 text-white shadow-sm flex flex-col justify-center">
                    <p class="text-sm font-semibold text-white/80">Petunjuk Penggunaan</p>
                    <h3 class="mt-1 text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">
                        Cara Menggunakan Dashboard
                    </h3>
                    <ul class="mt-3 space-y-1.5 text-white/85 font-medium lg:text-lg max-w-2xl list-disc list-inside">
                        <li>Ketuk salah satu menu di bawah untuk membukanya.</li>
                        <li>Tanda <span class="font-bold">&rsaquo;</span> menandakan menu akan membuka halaman baru.</li>
                        <li>Isi <span class="font-semibold">Jurnal Kegiatan Harian</span> setiap hari sebagai bukti aktivitas magang.</li>
                       
                    </ul>
                </div>

              

            </div>

            {{-- ===== GRUP 1: AKTIVITAS HARIAN ===== --}}
            <div>
                <h4 class="px-1 mb-3 text-sm font-bold uppercase tracking-wide text-[#8B5E34]">Aktivitas</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3 sm:gap-4">

                    {{-- Jurnal Kegiatan Harian --}}
                    <a href="{{ route('siswa.jurnal.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#0047d6]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Jurnal Kegiatan Harian</h3>
                            <p class="text-sm text-slate-500 leading-snug">Isi jurnal aktivitas harian Anda dan lihat feedback dari instruktur.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-[#8B5E34] group-hover:text-[#0047d6] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                    {{-- Absensi --}}
                    <a href="{{ route('siswa.absensi.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#0047d6]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Absensi Kehadiran</h3>
                            <p class="text-sm text-slate-500 leading-snug">Lihat rekap kehadiran: hadir, izin, sakit, alpha.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-[#8B5E34] group-hover:text-[#0047d6] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                    {{-- Catatan Kegiatan --}}
                    <a href="{{ route('siswa.catatan.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#8B5E34]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Catatan Kegiatan</h3>
                            <p class="text-sm text-slate-500 leading-snug">Isi refleksi, perencanaan, &amp; pelaksanaan kegiatan.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-[#8B5E34] group-hover:text-[#8B5E34] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                    {{-- Lembar Observasi --}}
                    <a href="{{ route('siswa.observasi.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#0047d6]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Lembar Observasi PKL</h3>
                            <p class="text-sm text-slate-500 leading-snug">Lihat hasil observasi &amp; monitoring pembimbing.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-[#8B5E34] group-hover:text-[#0047d6] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                </div>
            </div>

            {{-- ===== GRUP 2: PENILAIAN & LAPORAN ===== --}}
            <div>
                <h4 class="px-1 mb-3 text-sm font-bold uppercase tracking-wide text-[#8B5E34]">Penilaian &amp; Laporan</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3 sm:gap-4">

                    {{-- Lihat Nilai --}}
                    <a href="{{ route('siswa.nilai.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#0047d6]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Lihat Nilai PKL</h3>
                            <p class="text-sm text-slate-500 leading-snug">Pantau akumulasi capaian nilai dari instruktur.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-[#8B5E34] group-hover:text-[#0047d6] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                    {{-- Dokumen & Penilaian Akhir --}}
                    <a href="{{ route('siswa.dokumen.index') }}"
                       class="group flex items-center gap-4 rounded-2xl bg-white border border-slate-100 px-4 py-4 sm:py-5 shadow-sm active:scale-[0.99] hover:border-[#8B5E34]/40 hover:shadow-md transition">
                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base sm:text-lg font-bold text-slate-900">Dokumen &amp; Penilaian Akhir</h3>
                            <p class="text-sm text-slate-500 leading-snug">Unggah laporan akhir &amp; lihat rekap nilai industri.</p>
                        </div>
                        <svg class="h-6 w-6 flex-shrink-0 text-[#8B5E34] group-hover:text-[#8B5E34] transition" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>

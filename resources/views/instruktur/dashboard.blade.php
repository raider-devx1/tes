<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Dashboard Instruktur</h2>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @include('partials.notifikasi')

            {{-- ===== BANNER SELAMAT DATANG ===== --}}
            <div class="rounded-2xl bg-[#0047d6] p-6 sm:p-8 text-white shadow-sm">
                <p class="text-sm font-semibold text-white/80">Ruang Instruktur Industri</p>
                <h3 class="mt-2 text-2xl md:text-3xl font-bold tracking-tight">
                    Selamat Datang, {{ Auth::user()->name }}!
                </h3>
                <p class="mt-3 max-w-xl font-medium text-white/85">Kelola absensi dan validasi kegiatan siswa bimbingan Anda.</p>
            </div>

            <!-- ===== KARTU STATISTIK ===== -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Siswa Aktif</p>
                    <p class="mt-2 text-4xl font-bold text-[#05b169]">{{ $siswaAktif }}</p>
                    <p class="mt-1 text-sm font-medium text-[#5b616e]">Sedang menjalani PKL.</p>
                </div> 
               
            </div>

            {{-- ===== MENU UTAMA NAVIGASI ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <a href="{{ route('instruktur.siswa.index') }}"
                   class="group md:col-span-3 block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 sm:p-8 transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/20">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h5 class="text-xl sm:text-2xl font-bold tracking-tight text-black group-hover:text-[#0047d6]">Ruang Monitoring &amp; Daftar Siswa</h5>
                            <p class="mt-2 max-w-3xl text-sm font-medium text-[#5b616e]">Lihat seluruh siswa bimbingan industri dalam bentuk tabel lengkap dengan pencarian &amp; filter, lalu langsung menuju validasi jurnal, persetujuan catatan, persetujuan observasi, input absensi, atau lembar penilaian PKL.</p>
                        </div>
                        <span class="text-2xl font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                </a>

                <a href="{{ route('instruktur.jurnal.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/20">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-bold text-black group-hover:text-[#0047d6]">Validasi Jurnal</h5>
                        <span class="font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Periksa, beri catatan, dan setujui jurnal harian siswa.</p>
                </a>

                <a href="{{ route('instruktur.catatan.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/20">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-bold text-black group-hover:text-[#0047d6]">Persetujuan Catatan</h5>
                        <span class="font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Berikan catatan instruktur dan persetujuan pada kegiatan siswa.</p>
                </a>

                <a href="{{ route('instruktur.observasi.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/20">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-bold text-black group-hover:text-[#0047d6]">Persetujuan Observasi</h5>
                        <span class="font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Tinjau dan setujui lembar observasi yang diajukan oleh guru pembimbing.</p>
                </a>

                <a href="{{ route('instruktur.absensi.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/20">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-bold text-black group-hover:text-[#0047d6]">Input Absensi</h5>
                        <span class="font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Kelola kehadiran harian (jam masuk/pulang) siswa.</p>
                </a>

                <a href="{{ route('instruktur.nilai.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/20">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-bold text-black group-hover:text-[#0047d6]">Lembar Penilaian PKL</h5>
                        <span class="font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Input nilai evaluasi kompetensi perkembangan hard-skill &amp; soft-skill siswa bimbingan.</p>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
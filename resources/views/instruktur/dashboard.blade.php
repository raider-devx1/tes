<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">Dashboard Instruktur</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ===== HERO ===== --}}
            <div class="rounded-3xl bg-[#0a0b0d] p-8 md:p-12 text-white">
                <p class="text-sm font-medium text-[#a8acb3]">Ruang Instruktur Industri</p>
                <h3 class="mt-2 text-3xl md:text-4xl font-normal tracking-tight">
                    Selamat Datang, {{ auth()->user()->name }}!
                </h3>
                <p class="mt-3 max-w-xl text-[#a8acb3]">Kelola absensi dan validasi kegiatan siswa bimbingan Anda.</p>

               
            </div>

            {{-- ===== KARTU STATUS SISWA BIMBINGAN ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-[#dee1e6] bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Siswa Aktif</p>
                    <p class="mt-2 text-4xl font-bold text-[#05b169]">{{ $siswaAktif }}</p>
                    <p class="mt-1 text-sm text-[#5b616e]">Sedang menjalani PKL.</p>
                </div>
                <div class="rounded-2xl border border-[#dee1e6] bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Siswa Belum</p>
                    <p class="mt-2 text-4xl font-bold text-[#f4b000]">{{ $siswaBelum }}</p>
                    <p class="mt-1 text-sm text-[#5b616e]">Belum memulai PKL.</p>
                </div>
                <div class="rounded-2xl border border-[#dee1e6] bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Siswa Selesai</p>
                    <p class="mt-2 text-4xl font-bold text-[#0052ff]">{{ $siswaSelesai }}</p>
                    <p class="mt-1 text-sm text-[#5b616e]">Telah menyelesaikan PKL.</p>
                </div>
            </div>

            {{-- ===== MENU NAVIGASI ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <a href="{{ route('instruktur.siswa.index') }}"
                   class="group md:col-span-3 block rounded-3xl border border-[#dee1e6] bg-white p-8 transition hover:border-[#0052ff]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h5 class="text-2xl font-semibold tracking-tight text-[#0a0b0d]">Ruang Monitoring &amp; Daftar Siswa</h5>
                            <p class="mt-2 max-w-3xl text-sm text-[#5b616e]">Lihat seluruh siswa bimbingan industri dalam bentuk tabel lengkap dengan pencarian &amp; filter, lalu langsung menuju validasi jurnal, persetujuan catatan, persetujuan observasi, input absensi, atau lembar penilaian PKL.</p>
                        </div>
                        <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                </a>

                <a href="{{ route('instruktur.jurnal.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-semibold text-[#0a0b0d]">Validasi Jurnal</h5>
                        <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm text-[#5b616e]">Periksa, beri catatan, dan setujui jurnal harian siswa.</p>
                </a>

                <a href="{{ route('instruktur.catatan.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-semibold text-[#0a0b0d]">Persetujuan Catatan</h5>
                        <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm text-[#5b616e]">Berikan catatan instruktur dan persetujuan pada kegiatan siswa.</p>
                </a>

                <a href="{{ route('instruktur.observasi.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-semibold text-[#0a0b0d]">Persetujuan Observasi</h5>
                        <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm text-[#5b616e]">Tinjau dan setujui lembar observasi yang diajukan oleh guru pembimbing.</p>
                </a>

                <a href="{{ route('instruktur.absensi.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-semibold text-[#0a0b0d]">Input Absensi</h5>
                        <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm text-[#5b616e]">Kelola kehadiran harian (jam masuk/pulang) siswa.</p>
                </a>

                <a href="{{ route('instruktur.nilai.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-semibold text-[#0a0b0d]">Lembar Penilaian PKL</h5>
                        <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                    <p class="mt-2 text-sm text-[#5b616e]">Input nilai evaluasi kompetensi perkembangan hard-skill &amp; soft-skill siswa bimbingan.</p>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
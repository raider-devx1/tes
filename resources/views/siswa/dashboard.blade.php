<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Dashboard Siswa PKL</h2>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @include('partials.notifikasi')

            {{-- ===== BANNER SELAMAT DATANG ===== --}}
            <div class="rounded-2xl bg-[#0047d6] p-6 sm:p-8 text-white shadow-sm">
                <h3 class="text-xl sm:text-2xl font-bold tracking-tight">Selamat Datang, {{ Auth::user()->name }}!</h3>
                <p class="mt-2 text-sm font-medium text-white/85">Pilih menu di bawah ini untuk mengelola aktivitas magang industri Anda.</p>
            </div>

            {{-- ===== STATISTIK RINGKASAN ===== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm">
                  <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Total Jurnal Diisi</p>
                    <h4 class="mt-2 text-4xl font-bold text-[#0052ff]">{{ $jumlahJurnal }}</h4>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm">
                     <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Jurnal Disetujui Instruktur</p>
                    <h4 class="mt-2 text-4xl font-bold text-[#05b169]">{{ $jurnalDisetujui }}</h4>
                </div>
            </div>

            {{-- ===== MENU UTAMA NAVIGASI ===== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('siswa.jurnal.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/25">
                    <h5 class="text-lg font-bold tracking-tight text-black group-hover:text-[#0047d6]">Jurnal Kegiatan Harian</h5>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Isi jurnal aktivitas harian Anda di sini dan lihat feedback dari instruktur.</p>
                </a>

                <a href="{{ route('siswa.absensi.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/25">
                    <h5 class="text-lg font-bold tracking-tight text-black group-hover:text-[#0047d6]">Absensi Kehadiran</h5>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Lihat rekap kehadiran Anda (Hadir, Izin, Sakit, Alpha) selama PKL.</p>
                </a>

                <a href="{{ route('siswa.dokumen.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/25">
                    <h5 class="text-lg font-bold tracking-tight text-black group-hover:text-[#0047d6]">Dokumen &amp; Penilaian Akhir</h5>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Unggah laporan akhir PKL Anda dan lihat rekap nilai dari industri.</p>
                </a>

                <a href="{{ route('siswa.catatan.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/25">
                    <h5 class="text-lg font-bold tracking-tight text-black group-hover:text-[#0047d6]">Catatan Kegiatan</h5>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Isi refleksi, perencanaan, dan pelaksanaan kegiatan harian.</p>
                </a>

                <a href="{{ route('siswa.observasi.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/25">
                    <h5 class="text-lg font-bold tracking-tight text-black group-hover:text-[#0047d6]">Lembar Observasi PKL</h5>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Lihat hasil observasi dan monitoring dari Guru Pembimbing.</p>
                </a>

                <a href="{{ route('siswa.nilai.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/25">
                    <h5 class="text-lg font-bold tracking-tight text-black group-hover:text-[#0047d6]">Lihat Nilai PKL</h5>
                    <p class="mt-2 text-sm font-medium text-[#5b616e]">Pantau akumulasi capaian hasil nilai kelulusan praktikum dari instruktur industri.</p>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
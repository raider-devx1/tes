<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Dashboard Guru Pembimbing</h2>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @include('partials.notifikasi')

            <!-- ===== HERO / BANNER ===== -->
            <div class="rounded-2xl bg-[#0047d6] p-8 md:p-12 text-white shadow-sm">
                <p class="text-sm font-semibold text-white/80">Ruang Guru Pembimbing</p>
                <h3 class="mt-2 text-3xl md:text-4xl font-bold tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h3>
                <p class="mt-3 max-w-xl font-medium text-white/85">Pusat monitoring dan observasi kegiatan siswa bimbingan Anda.</p>
            </div>

            <!-- ===== KARTU STATISTIK ===== -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Siswa Aktif</p>
                    <p class="mt-2 text-4xl font-bold text-[#05b169]">{{ $siswaAktif }}</p>
                    <p class="mt-1 text-sm font-medium text-[#5b616e]">Sedang menjalani PKL.</p>
                </div> 
               
            </div>

            <!-- ===== MENU UTAMA ===== -->
            <div class="grid grid-cols-1 gap-4">

                <a href="{{ route('guru.siswa.index') }}"
                   class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 md:p-8 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h5 class="text-xl md:text-2xl font-bold tracking-tight text-black">Ruang Monitoring &amp; Daftar Siswa</h5>
                            <p class="mt-2 max-w-3xl text-sm font-medium text-[#5b616e]">Klik di sini untuk melihat daftar siswa, membaca aktivitas jurnal mereka, mengecek riwayat absensi industri, serta menginput Lembar Observasi Kunjungan.</p>
                        </div>
                        <span class="text-2xl font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                </a>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="{{ route('guru.monitoring.jurnal') }}"
                       class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-bold text-black">Jurnal Siswa</h5>
                            <span class="text-xl font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm font-medium text-[#5b616e]">Pantau seluruh jurnal harian siswa bimbingan beserta status persetujuannya (disetujui / menunggu / revisi).</p>
                    </a>

                    <a href="{{ route('guru.monitoring.absensi') }}"
                       class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-bold text-black">Absensi Siswa</h5>
                            <span class="text-xl font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm font-medium text-[#5b616e]">Lihat rekap kehadiran siswa bimbingan di industri: Hadir, Izin, Sakit, dan Alpha.</p>
                    </a>

                    <a href="{{ route('guru.catatan.index') }}"
                       class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-bold text-black">Catatan Kegiatan Siswa</h5>
                            <span class="text-xl font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm font-medium text-[#5b616e]">Pantau refleksi dan catatan kegiatan yang ditulis oleh siswa bimbingan.</p>
                    </a>

                    <a href="{{ route('guru.observasi.index') }}"
                       class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-bold text-black">Lembar Observasi</h5>
                            <span class="text-xl font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm font-medium text-[#5b616e]">Monitor perkembangan siswa, catat permasalahan, and berikan solusi pemecahan masalah.</p>
                    </a>

                    <a href="{{ route('guru.nilai.index') }}"
                       class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-bold text-black">Rekap Nilai Siswa</h5>
                            <span class="text-xl font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm font-medium text-[#5b616e]">Pantau dan unduh rekapitulasi perolehan nilai perkembangan siswa bimbingan.</p>
                    </a>

                    <a href="{{ route('guru.dokumen.index') }}"
                       class="group block rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-bold text-black">Dokumen Siswa</h5>
                            <span class="text-xl font-bold text-[#0047d6] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm font-medium text-[#5b616e]">Lihat &amp; unduh Surat Tugas, Surat Penerimaan Industri, dan Laporan PKL siswa bimbingan Anda sesuai hak akses.</p>
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">Dashboard Guru Pembimbing</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ===== HERO ===== --}}
            <div class="rounded-3xl bg-[#0a0b0d] p-8 md:p-12 text-white">
                <p class="text-sm font-medium text-[#a8acb3]">Ruang Guru Pembimbing</p>
                <h3 class="mt-2 text-3xl md:text-4xl font-normal tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h3>
                <p class="mt-3 max-w-xl text-[#a8acb3]">Pusat monitoring dan observasi kegiatan siswa bimbingan Anda.</p>

                
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
            <div class="grid grid-cols-1 gap-4">

                <a href="{{ route('guru.siswa.index') }}"
                   class="group block rounded-3xl border border-[#dee1e6] bg-white p-8 transition hover:border-[#0052ff]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h5 class="text-2xl font-semibold tracking-tight text-[#0a0b0d]">Ruang Monitoring &amp; Daftar Siswa</h5>
                            <p class="mt-2 max-w-3xl text-sm text-[#5b616e]">Klik di sini untuk melihat daftar siswa, membaca aktivitas jurnal mereka, mengecek riwayat absensi industri, serta menginput Lembar Observasi Kunjungan.</p>
                        </div>
                        <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                    </div>
                </a>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('guru.monitoring.jurnal') }}"
                       class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-semibold text-[#0a0b0d]">Jurnal Siswa</h5>
                            <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm text-[#5b616e]">Pantau seluruh jurnal harian siswa bimbingan beserta status persetujuannya (disetujui / menunggu / revisi).</p>
                    </a>

                    <a href="{{ route('guru.monitoring.absensi') }}"
                       class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-semibold text-[#0a0b0d]">Absensi Siswa</h5>
                            <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm text-[#5b616e]">Lihat rekap kehadiran siswa bimbingan di industri: Hadir, Izin, Sakit, dan Alpha.</p>
                    </a>

                    <a href="{{ route('guru.catatan.index') }}"
                       class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-semibold text-[#0a0b0d]">Catatan Kegiatan Siswa</h5>
                            <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm text-[#5b616e]">Pantau refleksi dan catatan kegiatan yang ditulis oleh siswa bimbingan.</p>
                    </a>

                    <a href="{{ route('guru.observasi.index') }}"
                       class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-semibold text-[#0a0b0d]">Lembar Observasi</h5>
                            <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm text-[#5b616e]">Monitor perkembangan siswa, catat permasalahan, dan berikan solusi pemecahan masalah.</p>
                    </a>

                    <a href="{{ route('guru.nilai.index') }}"
                       class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-semibold text-[#0a0b0d]">Rekap Nilai Siswa</h5>
                            <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm text-[#5b616e]">Pantau dan unduh rekapitulasi perolehan nilai perkembangan siswa bimbingan.</p>
                    </a>

                    <a href="{{ route('guru.dokumen.index') }}"
                       class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff]">
                        <div class="flex items-center justify-between">
                            <h5 class="text-lg font-semibold text-[#0a0b0d]">Dokumen Siswa</h5>
                            <span class="text-[#0052ff] transition group-hover:translate-x-1">&rarr;</span>
                        </div>
                        <p class="mt-2 text-sm text-[#5b616e]">Lihat &amp; unduh Surat Tugas, Surat Penerimaan Industri, dan Laporan PKL siswa bimbingan Anda sesuai hak akses.</p>
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">Dashboard Siswa PKL</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ===== HERO ===== --}}
            <div class="rounded-3xl bg-[#0a0b0d] p-8 text-white">
                <h3 class="text-2xl font-semibold tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h3>
                <p class="mt-2 text-sm text-[#a8acb3]">Pilih menu di bawah ini untuk mengelola aktivitas magang industri Anda.</p>
            </div>

            {{-- ===== KARTU STATISTIK ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-[#dee1e6] bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Total Jurnal Diisi</p>
                    <h4 class="mt-2 text-4xl font-bold text-[#0052ff]">{{ $jumlahJurnal }}</h4>
                </div>
                <div class="rounded-2xl border border-[#dee1e6] bg-white p-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Jurnal Disetujui Instruktur</p>
                    <h4 class="mt-2 text-4xl font-bold text-[#05b169]">{{ $jurnalDisetujui }}</h4>
                </div>
            </div>

            {{-- ===== MENU NAVIGASI ===== --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('siswa.jurnal.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff] hover:bg-[#f7f7f7]">
                    <h5 class="text-lg font-semibold tracking-tight text-[#0a0b0d] group-hover:text-[#0052ff]">Jurnal Kegiatan Harian</h5>
                    <p class="mt-2 text-sm text-[#5b616e]">Buka lini masa aktivitas harian Anda di sini dan lihat feedback dari instruktur.</p>
                </a>
                
                <a href="{{ route('siswa.dokumen.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff] hover:bg-[#f7f7f7]">
                    <h5 class="text-lg font-semibold tracking-tight text-[#0a0b0d] group-hover:text-[#0052ff]">Dokumen &amp; Penilaian Akhir</h5>
                    <p class="mt-2 text-sm text-[#5b616e]">Unggah laporan akhir PKL Anda dan lihat rekap nilai dari industri.</p>
                </a>
                
                <a href="{{ route('siswa.catatan.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff] hover:bg-[#f7f7f7]">
                    <h5 class="text-lg font-semibold tracking-tight text-[#0a0b0d] group-hover:text-[#0052ff]">Catatan Kegiatan</h5>
                    <p class="mt-2 text-sm text-[#5b616e]">Isi refleksi, perencanaan, dan pelaksanaan kegiatan harian.</p>
                </a>
                
                <a href="{{ route('siswa.observasi.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff] hover:bg-[#f7f7f7]">
                    <h5 class="text-lg font-semibold tracking-tight text-[#0a0b0d] group-hover:text-[#0052ff]">Lembar Observasi PKL</h5>
                    <p class="mt-2 text-sm text-[#5b616e]">Lihat hasil observasi dan monitoring dari Guru Pembimbing.</p>
                </a>
                
                <a href="{{ route('siswa.nilai.index') }}"
                   class="group block rounded-2xl border border-[#dee1e6] bg-white p-6 transition hover:border-[#0052ff] hover:bg-[#f7f7f7]">
                    <h5 class="text-lg font-semibold tracking-tight text-[#0a0b0d] group-hover:text-[#0052ff]">Lihat Nilai PKL</h5>
                    <p class="mt-2 text-sm text-[#5b616e]">Pantau akumulasi capaian hasil nilai kelulusan praktikum dari instruktur industri.</p>
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
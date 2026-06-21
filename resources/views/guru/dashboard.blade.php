<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard Guru Pembimbing') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}!</h3>
                <p class="text-gray-600 mb-6">Pusat monitoring dan observasi kegiatan siswa bimbingan Anda.</p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8 w-full md:w-1/3">
                    <p class="text-sm text-blue-600 font-bold uppercase">Jumlah Siswa Bimbingan</p>
                    <h4 class="text-4xl font-bold text-blue-800">{{ $siswaBimbingan }}</h4>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <a href="{{ route('guru.siswa.index') }}"
                        class="block p-8 bg-white border-2 border-gray-200 rounded-xl shadow-sm hover:border-indigo-500 hover:shadow-md transition">
                        <h5 class="text-2xl font-bold tracking-tight text-gray-900 mb-2">📊 Ruang Monitoring & Daftar
                            Siswa</h5>
                        <p class="font-normal text-gray-600">Klik di sini untuk melihat daftar siswa, membaca aktivitas
                            jurnal mereka, mengecek riwayat absensi industri, serta menginput Lembar Observasi
                            Kunjungan.</p>
                    </a>
                    <!-- Card Catatan Kegiatan Siswa -->
                    <a href="{{ route('guru.catatan.index') }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Catatan Kegiatan Siswa</h5>
                        <p class="font-normal text-gray-700">Pantau refleksi dan catatan kegiatan yang ditulis oleh
                            siswa bimbingan.</p>
                    </a>
                    <a href="{{ route('guru.observasi.index') }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Lembar Observasi</h5>
                        <p class="font-normal text-gray-700">Monitor perkembangan siswa, catat permasalahan, dan berikan
                            solusi pemecahan masalah.</p>
                    </a>
                    <a href="{{ route('guru.nilai.index') }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100 transition duration-200">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Rekap Nilai Siswa</h5>
                        <p class="font-normal text-gray-700">Pantau dan unduh rekapitulasi perolehan nilai perkembangan
                            siswa bimbingan.</p>
                    </a>

                    <a href="{{ route('guru.dokumen.index') }}"
   class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100 transition duration-200">
    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">📁 Dokumen Siswa</h5>
    <p class="font-normal text-gray-700">Lihat & unduh Surat Tugas, Surat Penerimaan Industri, dan Laporan PKL siswa bimbingan Anda sesuai hak akses.</p>
</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
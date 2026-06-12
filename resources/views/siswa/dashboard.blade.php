<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard Siswa PKL') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}!</h3>
                <p class="text-gray-600 mb-6">Pilih menu di bawah ini untuk mengelola aktivitas magang industri Anda.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-600 font-bold uppercase">Total Jurnal Diisi</p>
                        <h4 class="text-4xl font-bold text-blue-800">{{ $jumlahJurnal }}</h4>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-600 font-bold uppercase">Jurnal Disetujui Instruktur</p>
                        <h4 class="text-4xl font-bold text-green-800">{{ $jurnalDisetujui }}</h4>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <a href="{{ route('siswa.jurnal.index') }}"
                        class="block p-8 bg-white border-2 border-gray-200 rounded-xl shadow-sm hover:border-indigo-500 hover:shadow-md transition text-center">
                        <h5 class="text-xl font-bold tracking-tight text-gray-900">📝 Jurnal Kegiatan Harian</h5>
                        <p class="font-normal text-gray-600 mt-2">Isi jurnal aktivitas harian Anda di sini dan lihat
                            feedback dari instruktur.</p>
                    </a>
                    <a href="{{ route('siswa.dokumen.index') }}"
                        class="block p-8 bg-white border-2 border-gray-200 rounded-xl shadow-sm hover:border-indigo-500 hover:shadow-md transition text-center">
                        <h5 class="text-xl font-bold tracking-tight text-gray-900">📁 Dokumen & Penilaian Akhir</h5>
                        <p class="font-normal text-gray-600 mt-2">Unggah laporan akhir PKL Anda dan lihat rekap nilai
                            dari industri.</p>
                    </a>
                    <!-- Card Catatan Kegiatan -->
                    <a href="{{ route('siswa.catatan.index') }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Catatan Kegiatan</h5>
                        <p class="font-normal text-gray-700">Isi refleksi, perencanaan, dan pelaksanaan kegiatan harian.
                        </p>
                    </a>
                    <a href="{{ route('siswa.observasi.index') }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Lembar Observasi PKL</h5>
                        <p class="font-normal text-gray-700">Lihat hasil observasi dan monitoring dari Guru Pembimbing.
                        </p>
                    </a>
                    <a href="{{ route('siswa.nilai.index') }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100 transition duration-200">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Lihat Nilai PKL</h5>
                        <p class="font-normal text-gray-700">Pantau akumulasi capaian hasil nilai kelulusan praktikum
                            dari instruktur industri.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
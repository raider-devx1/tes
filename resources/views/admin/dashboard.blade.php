<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard Admin / Koordinator PKL') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-2">Selamat Datang, Koordinator!</h3>
                <p class="text-gray-600 mb-6">Ringkasan statistik pengguna aplikasi LMS PKL SMK N 1 Majene.</p>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <p class="text-xs text-blue-600 font-bold uppercase">Total Siswa PKL</p>
                        <h4 class="text-4xl font-bold text-blue-800">{{ $jumlahSiswa }}</h4>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <p class="text-xs text-green-600 font-bold uppercase">Guru Pembimbing</p>
                        <h4 class="text-4xl font-bold text-green-800">{{ $jumlahGuru }}</h4>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <p class="text-xs text-purple-600 font-bold uppercase">Instruktur Industri</p>
                        <h4 class="text-4xl font-bold text-purple-800">{{ $jumlahInstruktur }}</h4>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                        <p class="text-xs text-orange-600 font-bold uppercase">Tempat Industri / DUDI</p>
                        <h4 class="text-4xl font-bold text-orange-800">{{ $jumlahPerusahaan }}</h4>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <a href="{{ route('admin.siswa.index') }}" class="block p-8 bg-white border-2 border-gray-200 rounded-xl shadow-sm hover:border-indigo-500 hover:shadow-md transition">
                        <h5 class="text-2xl font-bold tracking-tight text-gray-900 mb-2">⚙️ Kelola Pemetaan (Mapping) Siswa</h5>
                        <p class="font-normal text-gray-600">Klik di sini untuk mengatur penempatan siswa. Hubungkan siswa dengan tempat industrinya, tentukan instruktur yang bertugas, dan pilih guru pembimbingnya.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard Instruktur Industri') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-2">Selamat Datang, {{ Auth::user()->name }}!</h3>
                <p class="text-gray-600 mb-6">Kelola absensi dan validasi kegiatan siswa bimbingan Anda.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <p class="text-sm text-indigo-600 font-bold uppercase">Siswa Bimbingan Industri</p>
                        <h4 class="text-4xl font-bold text-indigo-800">{{ $siswaBimbingan }}</h4>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-600 font-bold uppercase">Jurnal Menunggu Validasi</p>
                        <h4 class="text-4xl font-bold text-yellow-800">{{ $jurnalPending }}</h4>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="{{ route('instruktur.siswa.index') }}"
    class="md:col-span-3 block p-6 bg-white border-2 border-gray-200 rounded-xl hover:border-indigo-500 hover:shadow-md transition duration-200">
    <h5 class="text-2xl font-bold tracking-tight text-gray-900 mb-2">📊 Ruang Monitoring & Daftar Siswa</h5>
    <p class="font-normal text-gray-600">Lihat seluruh siswa bimbingan industri dalam bentuk tabel lengkap dengan pencarian & filter, lalu langsung menuju validasi jurnal, persetujuan catatan, persetujuan observasi, input absensi, atau lembar penilaian PKL.</p>
</a>

                    <a href="{{ route('instruktur.jurnal.index') }}"
                        class="block p-6 bg-white border-2 border-gray-200 rounded-xl hover:border-indigo-500 transition text-center">
                        <h5 class="text-lg font-bold text-gray-900 mb-2">✅ Validasi Jurnal</h5>
                        <p class="text-sm text-gray-600">Periksa, beri catatan, dan setujui jurnal harian siswa.</p>
                    </a>
                    <!-- Card Persetujuan Catatan -->
                    <a href="{{ route('instruktur.catatan.index') }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Persetujuan Catatan</h5>
                        <p class="font-normal text-gray-700">Berikan catatan instruktur dan persetujuan pada kegiatan
                            siswa.</p>
                    </a>

                    <a href="{{ route('instruktur.observasi.index') }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Persetujuan Observasi</h5>
                        <p class="font-normal text-gray-700">Tinjau dan setujui lembar observasi yang diajukan oleh guru
                            pembimbing.</p>
                    </a>
                    <a href="{{ route('instruktur.absensi.index') }}"
                        class="block p-6 bg-white border-2 border-gray-200 rounded-xl hover:border-indigo-500 transition text-center">
                        <h5 class="text-lg font-bold text-gray-900 mb-2">📅 Input Absensi</h5>
                        <p class="text-sm text-gray-600">Kelola kehadiran harian (jam masuk/pulang) siswa.</p>
                    </a>
                    <a href="{{ route('instruktur.nilai.index') }}"
                        class="block p-6 bg-white rounded-lg border border-gray-200 shadow-md hover:bg-gray-100 transition duration-200">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Lembar Penilaian PKL</h5>
                        <p class="font-normal text-gray-700">Input nilai evaluasi kompetensi perkembangan hard-skill &
                            soft-skill siswa bimbingan.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
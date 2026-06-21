<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Siswa Bimbingan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h3 class="text-lg font-bold mb-4">Siswa PKL Anda</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-3">Nama Siswa</th>
                                <th class="border p-3">Kelas & Jurusan</th>
                                <th class="border p-3">Tempat Industri</th>
                                <th class="border p-3 text-center">Aksi Monitoring</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswas as $siswa)
                                <tr class="hover:bg-gray-50">
                                    <td class="border p-3 font-bold">{{ $siswa->name }}</td>
                                    <td class="border p-3">{{ $siswa->kelas }} - {{ $siswa->jurusan }}</td>
                                    <td class="border p-3">{{ $siswa->perusahaan->nama_perusahaan ?? 'Belum Di-mapping' }}</td>
                                    <td class="border p-3">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('guru.monitoring.jurnal', ['siswa_id' => $siswa->id]) }}"
                                               class="bg-blue-500 hover:bg-blue-700 text-white text-xs py-1 px-3 rounded shadow">
                                                Monitoring Jurnal
                                            </a>
                                            <a href="{{ route('guru.monitoring.absensi', ['siswa_id' => $siswa->id]) }}"
                                               class="bg-green-500 hover:bg-green-700 text-white text-xs py-1 px-3 rounded shadow">
                                                Monitoring Absensi
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="border p-4 text-center text-gray-500">Anda belum memiliki siswa bimbingan. Hubungi Admin.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
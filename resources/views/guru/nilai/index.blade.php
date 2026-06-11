<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Monitoring Capaian Nilai Siswa Bimbingan') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 border">Siswa</th>
                                <th class="px-4 py-3 border text-center">Soft Skill</th>
                                <th class="px-4 py-3 border text-center">Hard Skill</th>
                                <th class="px-4 py-3 border text-center">Pengembangan Hard</th>
                                <th class="px-4 py-3 border text-center">Kewirausahaan</th>
                                <th class="px-4 py-3 border text-center bg-blue-50 text-blue-900">Total Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($nilaiSiswa as $item)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-4 font-bold text-gray-900 border">{{ $item->user->name }}</td>
                                    <td class="px-4 py-4 text-center border">{{ $item->soft_skill }}</td>
                                    <td class="px-4 py-4 text-center border">{{ $item->hard_skill }}</td>
                                    <td class="px-4 py-4 text-center border">{{ $item->pengembangan_hard_skill }}</td>
                                    <td class="px-4 py-4 text-center border">{{ $item->kewirausahaan }}</td>
                                    <td class="px-4 py-4 text-center font-black text-blue-700 bg-blue-50/50 border">{{ $item->rata_rata }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada siswa bimbingan yang selesai dinilai oleh instruktur.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
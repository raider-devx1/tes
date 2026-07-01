<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Catatan Kegiatan Siswa Bimbingan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 border">Nama Siswa</th>
                                <th class="px-4 py-3 border">Pekerjaan</th>
                                <th class="px-4 py-3 border">Perencanaan</th>
                                <th class="px-4 py-3 border">Hasil/Pelaksanaan</th>
                                <th class="px-4 py-3 border">Catatan Instruktur</th>
                                <th class="px-4 py-3 border text-center">Status</th>
                                <th class="px-4 py-3 border text-center">Cetak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($catatan as $item)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 border font-medium text-gray-900">{{ $item->user->name ?? '-' }}</td>
                                    <td class="px-4 py-3 border">{{ $item->nama_pekerjaan }}</td>
                                    <td class="px-4 py-3 border">{{ $item->perencanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 border">{{ $item->pelaksanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 border">{{ $item->catatan_instruktur ?? '-' }}</td>
                                    <td class="px-4 py-3 border text-center">
                                        @if($item->is_approved)
                                            <span class="text-green-600 font-bold">Disetujui</span>
                                        @else
                                            <span class="text-yellow-600 font-bold">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 border text-center">
                                        <a href="{{ route('cetak.catatan', $item->user_id) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-2 rounded transition">PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-4 text-center text-gray-400 italic">Belum ada catatan kegiatan dari siswa bimbingan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {!! $catatan->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
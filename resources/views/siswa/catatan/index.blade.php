<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Catatan Kegiatan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex justify-between items-center mb-6">
                        <a href="{{ route('siswa.catatan.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            + Tambah Catatan
                        </a>
                        <a href="{{ route('cetak.catatan') }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            Cetak PDF
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 border">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 border">Tanggal</th>
                                    <th class="px-4 py-3 border">Nama Pekerjaan</th>
                                    <th class="px-4 py-3 border">Perencanaan</th>
                                    <th class="px-4 py-3 border">Pelaksanaan / Hasil</th>
                                    <th class="px-4 py-3 border">Catatan Instruktur</th>
                                    <th class="px-4 py-3 border text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($catatan as $item)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 border whitespace-nowrap">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-3 border font-medium text-gray-900">{{ $item->nama_pekerjaan }}</td>
                                        <td class="px-4 py-3 border">{{ $item->perencanaan_kegiatan }}</td>
                                        <td class="px-4 py-3 border">{{ $item->pelaksanaan_kegiatan }}</td>
                                        <td class="px-4 py-3 border">{{ $item->catatan_instruktur ?? '-' }}</td>
                                        <td class="px-4 py-3 border text-center">
                                            @if($item->is_approved)
                                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Disetujui</span>
                                            @else
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Menunggu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-gray-400 italic">Belum ada catatan kegiatan.</td>
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
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Persetujuan Catatan Kegiatan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if(session('success'))
                    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 border w-1/6">Nama Siswa</th>
                                <th class="px-4 py-3 border w-1/6">Pekerjaan</th>
                                <th class="px-4 py-3 border w-1/5">Perencanaan</th>
                                <th class="px-4 py-3 border w-1/5">Hasil</th>
                                <th class="px-4 py-3 border w-1/4 text-center">Aksi / Catatan Instruktur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($catatan as $item)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 border font-medium text-gray-900">{{ $item->user->name ?? '-' }}</td>
                                    <td class="px-4 py-3 border">{{ $item->nama_pekerjaan }}</td>
                                    <td class="px-4 py-3 border">{{ $item->perencanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 border">{{ $item->pelaksanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 border">
                                        @if($item->is_approved)
                                            <div class="text-green-700 bg-green-50 p-2 rounded text-xs mb-1 font-bold text-center">Telah Disetujui</div>
                                            <p class="text-xs text-gray-700"><strong>Catatan:</strong> {{ $item->catatan_instruktur ?? '-' }}</p>
                                        @else
                                            <form action="{{ route('instruktur.catatan.approve', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <textarea name="catatan_instruktur" rows="2" placeholder="Masukkan catatan / evaluasi..." class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 mb-2"></textarea>
                                                <button type="submit" class="w-full bg-blue-600 text-white text-xs px-3 py-1.5 rounded hover:bg-blue-700 transition">
                                                    Setujui & Simpan
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('cetak.catatan', $item->user_id) }}" target="_blank" class="inline-block w-full text-center mt-2 bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded transition">Cetak PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-400 italic">Belum ada catatan dari siswa.</td>
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
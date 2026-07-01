<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Catatan Kegiatan Siswa Bimbingan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- ===== FILTER PENCARIAN ===== --}}
                <form method="GET" action="{{ route('guru.catatan.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Cari (Nama / NISN)
                            </label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="w-full md:w-56">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select name="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Status --</option>
                                <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                                <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                                Cari
                            </button>
                            <a href="{{ route('guru.catatan.index') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-md transition inline-block text-center">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL ===== --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 border text-center w-12">No</th>
                                <th class="px-4 py-3 border">Nama Siswa</th>
                                <th class="px-4 py-3 border">NISN</th>
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
                                <tr class="bg-white border-b hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 border text-center">{{ $catatan->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 border font-medium text-gray-900">{{ $item->user->name ?? '-' }}</td>
                                    <td class="px-4 py-3 border text-gray-600">{{ $item->user->nisn ?? '-' }}</td>
                                    <td class="px-4 py-3 border text-gray-600">{{ $item->nama_pekerjaan }}</td>
                                    <td class="px-4 py-3 border text-gray-600">{{ $item->perencanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 border text-gray-600">{{ $item->pelaksanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 border text-gray-600">{{ $item->catatan_instruktur ?? '-' }}</td>
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
                                    <td colspan="9" class="px-4 py-4 text-center text-gray-400 italic">
                                        Tidak ada catatan yang cocok / belum ada catatan dari siswa bimbingan.
                                    </td>
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
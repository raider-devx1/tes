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

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('instruktur.catatan.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="w-full md:w-56">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select name="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Status --</option>
                                <option value="disetujui" @selected(request('status') === 'disetujui')>Sudah Disetujui</option>
                                <option value="belum"     @selected(request('status') === 'belum')>Belum (Menunggu)</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                                Cari
                            </button>
                            <a href="{{ route('instruktur.catatan.index') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-md transition inline-block text-center">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL CATATAN ===== --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 border text-center w-12">No</th>
                                <th class="px-4 py-3 border">Nama Siswa</th>
                                <th class="px-4 py-3 border">NISN</th>
                                <th class="px-4 py-3 border w-1/6">Pekerjaan</th>
                                <th class="px-4 py-3 border w-1/5">Perencanaan</th>
                                <th class="px-4 py-3 border w-1/5">Hasil</th>
                                <th class="px-4 py-3 border w-1/4 text-center">Aksi / Catatan Instruktur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($catatan as $item)
                                <tr class="bg-white border-b hover:bg-gray-50 align-top transition">
                                    <td class="px-4 py-3 border text-center text-gray-500">{{ $catatan->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 border font-medium text-gray-900">{{ $item->user->name ?? '-' }}</td>
                                    <td class="px-4 py-3 border whitespace-nowrap text-gray-600">{{ $item->user->nisn ?? '-' }}</td>
                                    <td class="px-4 py-3 border text-gray-600">{{ $item->nama_pekerjaan }}</td>
                                    <td class="px-4 py-3 border text-gray-600">{{ $item->perencanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 border text-gray-600">{{ $item->pelaksanaan_kegiatan }}</td>
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
                                    <td colspan="7" class="px-4 py-4 text-center text-gray-400 italic">Belum ada catatan dari siswa.</td>
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
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Persetujuan Lembar Observasi
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
                <form method="GET" action="{{ route('instruktur.observasi.index') }}" class="mb-6">
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
                            <a href="{{ route('instruktur.observasi.index') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-md transition inline-block text-center">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL OBSERVASI ===== --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 border text-center w-12">No</th>
                                <th class="px-4 py-3 border">Tanggal</th>
                                <th class="px-4 py-3 border">Nama Siswa</th>
                                <th class="px-4 py-3 border">NISN</th>
                                <th class="px-4 py-3 border">Permasalahan</th>
                                <th class="px-4 py-3 border">Solusi Pemecahan</th>
                                <th class="px-4 py-3 border text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($observasi as $item)
                                <tr class="bg-white border-b align-top transition hover:bg-gray-50">
                                    <td class="px-4 py-3 border text-center text-gray-500">
                                        {{ $observasi->firstItem() + $loop->index }}
                                    </td>
                                    <td class="px-4 py-3 border whitespace-nowrap text-gray-600">
                                        {{ \Carbon\Carbon::parse($item->hari_tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 border font-medium text-gray-900">
                                        {{ $item->user->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 border whitespace-nowrap text-gray-600">
                                        {{ $item->user->nisn ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 border text-gray-600">
                                        {{ $item->permasalahan }}
                                    </td>
                                    <td class="px-4 py-3 border text-gray-600">
                                        {{ $item->solusi }}
                                    </td>
                                    <td class="px-4 py-3 border text-center">
                                        @if($item->is_approved)
                                            <span class="text-green-600 font-bold">Disetujui</span>
                                        @else
                                            <form action="{{ route('instruktur.observasi.approve', $item->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded hover:bg-blue-700 transition shadow-sm">
                                                    Setujui
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-4 text-center text-gray-400 italic">Belum ada observasi untuk disetujui.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {!! $observasi->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
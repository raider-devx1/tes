<x-app-layout>
    <div class="max-w-6xl mx-auto py-6 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Lembar Observasi</h1>
            <a href="{{ route('guru.observasi.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                + Tambah Observasi
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter: pencarian nama/NISN + dropdown status --}}
        <form method="GET" class="mb-4 flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NISN..."
                   class="w-full sm:w-64 rounded-lg border-gray-200 text-sm focus:border-blue-600 focus:ring-blue-600">
            <select name="status" class="rounded-lg border-gray-200 text-sm focus:border-blue-600 focus:ring-blue-600">
                <option value="">Semua Status</option>
                <option value="1" @selected(request('status') === '1')>Disetujui</option>
                <option value="0" @selected(request('status') === '0')>Menunggu</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">Cari</button>
            @if(request('q') || request()->filled('status'))
                <a href="{{ route('guru.observasi.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition inline-block text-center">Reset</a>
            @endif
        </form>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Siswa</th>
                        <th class="px-4 py-3 text-left">NISN</th>
                        <th class="px-4 py-3 text-left">Pekerjaan/Projek</th>
                        <th class="px-4 py-3 text-left">Permasalahan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Cetak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($observasi as $obs)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-center text-gray-500">
                                {{ $observasi->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ \Illuminate\Support\Carbon::parse($obs->hari_tanggal)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $obs->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $obs->user->nisn ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $obs->pekerjaan_projek ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $obs->permasalahan }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($obs->is_approved)
                                    <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 font-medium">Disetujui</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700 font-medium">Menunggu</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('cetak.observasi', $obs->user_id) }}" target="_blank"
                                   class="text-blue-600 hover:underline">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-400 italic">
                                Belum ada data observasi.
                            </td>
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
</x-app-layout>
<x-app-layout title="Riwayat Aktivitas">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Riwayat Aktivitas</h2>
            <p class="text-sm text-gray-500">Seluruh aktivitas yang terjadi di sistem (siapa melakukan apa & kapan).</p>
        </div>

        {{-- ===== FILTER TANGGAL ===== --}}
        <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700 transition">Filter</button>
                <a href="{{ route('admin.riwayat.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition inline-block text-center">Reset</a>
            </div>
        </form>

        {{-- ===== TABEL ===== --}}
        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NISN</th>
                            <th class="px-4 py-3">NIP</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                            <th class="px-4 py-3">Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            @php $u = $log->user; @endphp
                            <tr class="hover:bg-blue-50/40 align-top transition">
                                <td class="px-4 py-3 text-center text-gray-500">{{ $logs->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ optional($u)->name ?? 'Pengguna dihapus' }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ optional($u)->nisn ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ optional($u)->nip ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ optional($u)->email ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400 italic">Belum ada riwayat aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== PAGINATION ===== --}}
        <div>
            {!! $logs->links() !!}
        </div>
    </div>
</x-app-layout>
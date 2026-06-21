<x-app-layout title="Monitoring Jurnal">
    <div class="max-w-7xl mx-auto space-y-6 py-8 sm:px-6 lg:px-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Monitoring Jurnal Siswa</h2>
            <p class="text-sm text-gray-500">Pantau jurnal kegiatan siswa bimbingan Anda (hanya-baca).</p>
        </div>

        {{-- Rekap --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Total Jurnal</p>
                <p class="text-2xl font-bold text-gray-700">{{ $rekap['total'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Disetujui</p>
                <p class="text-2xl font-bold text-green-600">{{ $rekap['disetujui'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-amber-500">{{ $rekap['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Revisi</p>
                <p class="text-2xl font-bold text-red-500">{{ $rekap['revisi'] ?? 0 }}</p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-gray-500 mb-1">Siswa</label>
                <select name="siswa_id" class="w-full rounded-lg border-gray-200 text-sm">
                    <option value="">Semua Siswa</option>
                    @foreach ($siswas as $s)
                        <option value="{{ $s->id }}" {{ request('siswa_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-200 text-sm">
                    <option value="">Semua</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="revisi" {{ request('status') === 'revisi' ? 'selected' : '' }}>Revisi</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
            <a href="{{ route('guru.monitoring.jurnal') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
        </form>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Unit Kerja</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($jurnals as $j)
                            @php
                                $badge = match($j->status_persetujuan) {
                                    'disetujui' => 'bg-green-50 text-green-700',
                                    'pending'   => 'bg-amber-50 text-amber-700',
                                    'revisi'    => 'bg-red-50 text-red-600',
                                    default     => 'bg-gray-50 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($j->hari_tanggal)->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $j->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $j->unit_kerja ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ \Illuminate\Support\Str::limit($j->deskripsi_pekerjaan, 60) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ ucfirst($j->status_persetujuan) }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cetak.jurnal', $j->siswa_id) }}" target="_blank" class="text-blue-600 hover:underline text-xs">PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada data jurnal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {!! $jurnals->links() !!}
        </div>
    </div>
</x-app-layout>
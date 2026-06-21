<x-app-layout title="Monitoring Absensi">
    <div class="max-w-7xl mx-auto space-y-6 py-8 sm:px-6 lg:px-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Monitoring Absensi Siswa</h2>
            <p class="text-sm text-gray-500">Pantau kehadiran siswa bimbingan Anda (hanya-baca).</p>
        </div>

        {{-- Rekap --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Hadir</p>
                <p class="text-2xl font-bold text-green-600">{{ $rekap['Hadir'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Izin</p>
                <p class="text-2xl font-bold text-blue-600">{{ $rekap['Izin'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Sakit</p>
                <p class="text-2xl font-bold text-amber-500">{{ $rekap['Sakit'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Alpha</p>
                <p class="text-2xl font-bold text-red-500">{{ $rekap['Alpha'] ?? 0 }}</p>
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
                    <option value="Hadir" {{ request('status') === 'Hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="Izin" {{ request('status') === 'Izin' ? 'selected' : '' }}>Izin</option>
                    <option value="Sakit" {{ request('status') === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="Alpha" {{ request('status') === 'Alpha' ? 'selected' : '' }}>Alpha</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="rounded-lg border-gray-200 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
            <a href="{{ route('guru.monitoring.absensi') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
        </form>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Jam Masuk</th>
                            <th class="px-4 py-3 text-center">Jam Pulang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($absensi as $a)
                            @php
                                $badge = match($a->status) {
                                    'Hadir' => 'bg-green-50 text-green-700',
                                    'Izin'  => 'bg-blue-50 text-blue-700',
                                    'Sakit' => 'bg-amber-50 text-amber-700',
                                    'Alpha' => 'bg-red-50 text-red-600',
                                    default => 'bg-gray-50 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $a->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $a->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">{{ $a->jam_masuk ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $a->jam_pulang ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {!! $absensi->links() !!}
        </div>
    </div>
</x-app-layout>
<x-app-layout title="Monitoring Absensi">
    <div class="max-w-7xl mx-auto space-y-6 py-12 sm:px-6 lg:px-8">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-[#0a0b0d]">Monitoring Absensi Siswa</h2>
                <p class="text-sm text-[#5b616e] mt-1">Pantau kehadiran siswa bimbingan Anda (hanya-baca).</p>
            </div>
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6] shrink-0">
                &larr; Kembali
            </button>
        </div>

        {{-- ===== KARTU STATISTIK ===== --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="rounded-2xl border border-[#dee1e6] bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Hadir</p>
                <p class="mt-1 text-2xl font-bold text-[#05b169]">{{ $rekap['Hadir'] }}</p>
            </div>
            <div class="rounded-2xl border border-[#dee1e6] bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Izin</p>
                <p class="mt-1 text-2xl font-bold text-[#0052ff]">{{ $rekap['Izin'] }}</p>
            </div>
            <div class="rounded-2xl border border-[#dee1e6] bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Sakit</p>
                <p class="mt-1 text-2xl font-bold text-[#f4b000]">{{ $rekap['Sakit'] }}</p>
            </div>
            <div class="rounded-2xl border border-[#dee1e6] bg-white p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-[#7c828a]">Alpha</p>
                <p class="mt-1 text-2xl font-bold text-[#cf202f]">{{ $rekap['Alpha'] }}</p>
            </div>
        </div>

        {{-- ===== KARTU FILTER ===== --}}
        <form method="GET" action="{{ route('guru.monitoring.absensi') }}" class="rounded-2xl border border-[#dee1e6] bg-white p-5 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Cari (Nama / NISN)</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                       class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status</label>
                <select name="status" class="rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                    <option value="">Semua</option>
                    <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                    <option value="Izin" @selected(request('status') === 'Izin')>Izin</option>
                    <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                    <option value="Alpha" @selected(request('status') === 'Alpha')>Alpha</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                       class="rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
            </div>
            <button type="submit"
                    class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">Filter</button>
            <a href="{{ route('guru.monitoring.absensi') }}"
               class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Reset</a>
        </form>

        {{-- ===== KARTU TABEL ===== --}}
        <div class="rounded-2xl border border-[#dee1e6] bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                            <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                            <th class="px-4 py-3 font-semibold">Tanggal</th>
                            <th class="px-4 py-3 font-semibold">Nama</th>
                            <th class="px-4 py-3 font-semibold">NISN</th>
                            <th class="px-4 py-3 text-center font-semibold">Status</th>
                            <th class="px-4 py-3 text-center font-semibold">Jam Masuk</th>
                            <th class="px-4 py-3 text-center font-semibold">Jam Pulang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#eef0f3]">
                        @forelse ($absensi as $a)
                            @php
                                $badge = match($a->status) {
                                    'Hadir' => 'bg-[#05b169]/10 text-[#05b169]',
                                    'Izin'  => 'bg-[#0052ff]/10 text-[#0052ff]',
                                    'Sakit' => 'bg-[#f4b000]/10 text-[#f4b000]',
                                    'Alpha' => 'bg-[#cf202f]/10 text-[#cf202f]',
                                    default => 'bg-[#eef0f3] text-[#5b616e]',
                                };
                            @endphp
                            <tr class="align-top transition hover:bg-[#f7f7f7]">
                                <td class="px-4 py-3 text-center text-[#7c828a]">{{ $absensi->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">
                                    {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-[#0a0b0d]">{{ $a->siswa->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">{{ $a->siswa->nisn ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ $a->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-center text-[#5b616e]">{{ $a->jam_masuk ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-[#5b616e]">{{ $a->jam_pulang ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-[#a8acb3] italic">Tidak ada data absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== PAGINATION ===== --}}
        <div>
            {!! $absensi->links() !!}
        </div>
    </div>
</x-app-layout>
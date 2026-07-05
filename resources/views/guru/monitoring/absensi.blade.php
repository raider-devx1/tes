<x-app-layout title="Monitoring Absensi">
    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Monitoring Absensi Siswa</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Pantau kehadiran siswa bimbingan Anda (hanya-baca).</p>
                </div>
                 <a href="{{ route('guru.dashboard') }}"
           class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
            Kembali ke Dashboard
        </a>
            </div>

            <!-- ===== CARD REKAP INFORMASI ===== -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Hadir</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['Hadir'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/30 bg-[#0047d6]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Izin</p>
                    <p class="mt-1 text-3xl font-bold text-[#0047d6]">{{ $rekap['Izin'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sakit</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['Sakit'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#cf202f]/30 bg-[#cf202f]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Alpha</p>
                    <p class="mt-1 text-3xl font-bold text-[#cf202f]">{{ $rekap['Alpha'] ?? 0 }}</p>
                </div>
            </div>

            <!-- ===== FORM FILTER ===== -->
            <form method="GET" action="{{ route('guru.monitoring.absensi') }}" class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 flex flex-wrap gap-3 items-end shadow-sm">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                    <select name="status" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua</option>
                        <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                        <option value="Izin" @selected(request('status') === 'Izin')>Izin</option>
                        <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                        <option value="Alpha" @selected(request('status') === 'Alpha')>Alpha</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                           class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                <a href="{{ route('guru.monitoring.absensi') }}"
                   class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
            </form>

            <!-- ===== TABEL DATA ===== -->
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-sm text-left">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold">Tanggal</th>
                                <th class="px-4 py-3 font-bold">Nama</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Jam Masuk</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Jam Pulang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($absensi as $a)
                                @php
                                    $badge = match($a->status) {
                                        'Hadir' => 'bg-[#05b169] text-white',
                                        'Izin'  => 'bg-[#0047d6] text-white',
                                        'Sakit' => 'bg-[#d98200] text-white',
                                        'Alpha' => 'bg-[#cf202f] text-white',
                                        default => 'bg-[#5b616e] text-white',
                                    };
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $absensi->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                        {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $a->siswa->name ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $a->siswa->nisn ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badge }}">{{ $a->status }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-black">{{ $a->jam_masuk ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-medium text-black">{{ $a->jam_pulang ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data absensi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== PAGINATION ===== -->
            <div class="mt-4">
                {!! $absensi->links() !!}
            </div>
        </div>
    </div>
</x-app-layout>
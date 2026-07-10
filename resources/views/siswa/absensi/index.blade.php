<x-app-layout>
     <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
           
            <h1 class="text-xl md:text-2xl font-bold text-black mb-6">Daftar Hadir PKL Saya</h1>
            
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                 Kembali ke Dashboard
            </a>
        </div>
    </x-slot>
    <div class="bg-white min-h-screen">
        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

            {{-- ===== KARTU REKAP ABSENSI ===== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="rounded-2xl border-2 border-[#05b169]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-[#05b169]">{{ $rekap['Hadir'] ?? 0 }}</p>
                    <p class="text-sm font-bold text-black mt-1">Hadir</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-[#0047d6]">{{ $rekap['Izin'] ?? 0 }}</p>
                    <p class="text-sm font-bold text-black mt-1">Izin</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-[#d98200]">{{ $rekap['Sakit'] ?? 0 }}</p>
                    <p class="text-sm font-bold text-black mt-1">Sakit</p>
                </div>
                <div class="rounded-2xl border-2 border-[#cf202f]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-[#cf202f]">{{ $rekap['Alpha'] ?? 0 }}</p>
                    <p class="text-sm font-bold text-black mt-1">Alpha</p>
                </div>
            </div>

            {{-- ===== FORM FILTER BULAN ===== --}}
            <form method="GET" action="{{ route('siswa.absensi.index') }}" class="mb-4 flex flex-wrap items-center gap-2">
                <input type="month" name="bulan" value="{{ request('bulan') }}"
                       class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                @if(request('bulan'))
                    <a href="{{ route('siswa.absensi.index') }}"
                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                @endif
            </form>

            {{-- ===== TABEL KEHADIRAN ===== --}}
            <div class="rounded-xl border-2 border-[#0047d6]/15 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 text-left font-bold">Tanggal</th>
                            <th class="px-4 py-3 text-center font-bold">Status</th>
                            <th class="px-4 py-3 text-center font-bold">Jam Masuk</th>
                            <th class="px-4 py-3 text-center font-bold">Jam Pulang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($absensis as $a)
                            @php
                                $badge = match ($a->status) {
                                    'Hadir' => 'bg-[#05b169] text-white',
                                    'Izin'  => 'bg-[#0047d6] text-white',
                                    'Sakit' => 'bg-[#d98200] text-white',
                                    'Alpha' => 'bg-[#cf202f] text-white',
                                    default => 'bg-[#5b616e] text-white',
                                };
                            @endphp
                            <tr class="transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                     {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $badge }}">{{ $a->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-black">{{ $a->jam_masuk ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-medium text-black">{{ $a->jam_pulang ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">
                                    Belum ada data kehadiran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout title="Monitoring Jurnal">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Monitoring Jurnal Siswa</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Pantau jurnal kegiatan siswa bimbingan Anda (hanya-baca).</p>
                </div>
                <a href="{{ route('guru.dashboard') }}"
           class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
            Kembali ke Dashboard
        </a>
            </div>

            {{-- ===== KARTU REKAP ===== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Jurnal</p>
                    <p class="mt-1 text-2xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Disetujui</p>
                    <p class="mt-1 text-2xl font-bold text-[#05b169]">{{ $rekap['disetujui'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Pending</p>
                    <p class="mt-1 text-2xl font-bold text-[#d98200]">{{ $rekap['pending'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Revisi</p>
                    <p class="mt-1 text-2xl font-bold text-[#cf202f]">{{ $rekap['revisi'] }}</p>
                </div>
            </div>

            {{-- ===== BANNER UTAMA & CETAK SEMUA PDF ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-black">Jurnal Kegiatan Siswa Bimbingan</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak jurnal sesuai
                        <span class="font-bold text-black">filter tanggal</span> di bawah. Bila tanggal dikosongkan, otomatis mencetak jurnal <span class="font-bold text-black">hari ini</span> (1 siswa per halaman).
                    </p>
                </div>

                <a href="{{ route('cetak.jurnal.semua', ['tanggal' => request('tanggal')]) }}" target="_blank"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                    </svg>
                    Cetak Semua PDF
                </a>
            </div>

            {{-- ===== FILTER DATA ===== --}}
            <form method="GET" action="{{ route('guru.monitoring.jurnal') }}"
                  class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 flex flex-wrap gap-3 items-end shadow-sm">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                    <select name="status"
                            class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua</option>
                        <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="revisi" @selected(request('status') === 'revisi')>Revisi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                           class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                <a href="{{ route('guru.monitoring.jurnal') }}"
                   class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
            </form>

            {{-- ===== TABEL KONTEN MONITORING ===== --}}
            <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full min-w-[1100px] text-sm text-left table-fixed">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                            <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                            <th class="px-4 py-3 font-bold w-40">Nama</th>
                            <th class="px-4 py-3 font-bold w-28">NISN</th>
                            <th class="px-4 py-3 font-bold w-[28%]">Unit Kerja</th>
                            <th class="px-4 py-3 font-bold w-[20%]">Catatan Instruktur</th>
                            <th class="px-4 py-3 font-bold w-36">Foto</th>
                            <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                            <th class="px-4 py-3 text-center font-bold w-24">Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($jurnals as $jurnal)
                            @php
                                $badgeStatus = match($jurnal->status_persetujuan) {
                                    'disetujui' => 'bg-[#05b169] text-white',
                                    'pending'   => 'bg-[#d98200] text-white',
                                    'revisi'    => 'bg-[#cf202f] text-white',
                                    default     => 'bg-[#5b616e] text-white',
                                };
                                $labelStatus = match($jurnal->status_persetujuan) {
                                    'disetujui' => 'Disetujui',
                                    'pending'   => 'Menunggu',
                                    'revisi'    => 'Revisi',
                                    default     => ucfirst($jurnal->status_persetujuan),
                                };
                                $items = $jurnal->items;
                                $fotos = $jurnal->items->whereNotNull('dokumentasi')->values();
                            @endphp
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black">{{ $jurnals->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 font-bold text-black break-words">{{ $jurnal->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $jurnal->siswa->nisn ?? '-' }}</td>

                                {{-- Unit Kerja (tampil 1, sisanya bisa dibuka) --}}
                                <td class="px-4 py-3 text-black break-words">
                                    @if($items->count())
                                        <div x-data="{ open: false }">
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words">{{ $items->first()->unit_kerja ?? '-' }}</span>
                                            </div>

                                            @if($items->count() > 1)
                                                <button type="button" @click="open = !open"
                                                        class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                    <span x-show="!open">+ {{ $items->count() - 1 }} unit kerja lainnya</span>
                                                    <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                         stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>

                                                <ol start="2" x-show="open" x-cloak x-transition
                                                    class="mt-2 list-decimal list-inside space-y-0.5 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                    @foreach($items->slice(1) as $it)
                                                        <li class="break-words">{{ $it->unit_kerja }}</li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>

                                {{-- Catatan Instruktur --}}
                                <td class="px-4 py-3 text-black break-words">
                                    @if($jurnal->catatan_instruktur)
                                        <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-xs font-medium italic text-black">
                                             {{ $jurnal->catatan_instruktur }}
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>

                                {{-- Foto Lampiran --}}
                                <td class="px-4 py-3 text-center">
                                    @if($fotos->count())
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($fotos as $k => $it)
                                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                                    <span class="text-xs font-semibold text-black">Foto {{ $k + 1 }}</span>
                                                    <a href="{{ asset('storage/'.$it->dokumentasi) }}" target="_blank"
                                                       class="inline-flex items-center rounded-full bg-[#0047d6] px-2.5 py-1 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                        Lihat
                                                    </a>
                                                    <a href="{{ asset('storage/'.$it->dokumentasi) }}"
                                                       download="Foto_Jurnal_{{ Str::slug($jurnal->siswa->name ?? 'siswa') }}_{{ $k + 1 }}"
                                                       class="inline-flex items-center rounded-full bg-[#05b169] px-2.5 py-1 text-xs font-bold text-white transition hover:bg-[#049a5b]">
                                                        Download
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sm text-[#5b616e]">Tidak ada</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badgeStatus }}">{{ $labelStatus }}</span>
                                </td>

                                {{-- Cetak baris tunggal --}}
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cetak.jurnal', ['siswa_id' => $jurnal->siswa_id, 'jurnal_id' => $jurnal->id]) }}" target="_blank"
                                       class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data jurnal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div>
                {!! $jurnals->links() !!}
            </div>
        </div>
    </div>
</x-app-layout>
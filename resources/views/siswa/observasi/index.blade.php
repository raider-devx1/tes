<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
             <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Lembar Observasi PKL</h2>

            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

              

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('siswa.observasi.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                               class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                        <select name="status"
                                class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            <option value="">Semua Status</option>
                            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                            <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                    <a href="{{ route('siswa.observasi.index') }}"
                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                </form>

                  <div class="flex justify-end mb-6">
                    <a href="{{ route('cetak.observasi') }}" target="_blank"
                       class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        Cetak Semua (PDF)
                    </a>
                </div>

                {{-- ===== TABEL DATA ===== --}}
                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[900px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3 font-bold w-40">Guru Pembimbing</th>
                                <th class="px-4 py-3 font-bold w-[28%]">Permasalahan</th>
                                <th class="px-4 py-3 font-bold w-[28%]">Solusi Pemecahan</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-20">Cetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($observasi as $item)
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $observasi->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                         {{ \Carbon\Carbon::parse($item->hari_tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $item->guru->name ?? '-' }}</td>

                                    {{-- ===== KOLOM PERMASALAHAN ===== --}}
                                    <td class="px-4 py-3 text-black break-words">
                                        @php $poinList = $item->items; @endphp
                                        @if($poinList->count())
                                            <div x-data="{ open: false }">
                                                <div class="flex items-start gap-1.5">
                                                    <span class="font-bold text-[#0047d6]">1.</span>
                                                    <span class="font-medium">{!! nl2br(e($poinList->first()->permasalahan)) !!}</span>
                                                </div>

                                                @if($poinList->count() > 1)
                                                    <button type="button" @click="open = !open"
                                                            class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                        <span x-show="!open">+ {{ $poinList->count() - 1 }} poin lainnya</span>
                                                        <span x-show="open" style="display:none;">Sembunyikan</span>
                                                        <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                             stroke-width="2.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                        </svg>
                                                    </button>

                                                    <ol start="2" x-show="open" x-cloak x-transition
                                                        class="mt-2 list-decimal list-inside space-y-1 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                        @foreach($poinList->slice(1) as $poin)
                                                            <li>{!! nl2br(e($poin->permasalahan)) !!}</li>
                                                        @endforeach
                                                    </ol>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>

                                    {{-- ===== KOLOM SOLUSI ===== --}}
                                    <td class="px-4 py-3 text-black break-words">
                                        @if($poinList->count())
                                            <div x-data="{ open: false }">
                                                <div class="flex items-start gap-1.5">
                                                    <span class="font-bold text-[#0047d6]">1.</span>
                                                    <span class="font-medium">{!! nl2br(e($poinList->first()->solusi)) !!}</span>
                                                </div>

                                                @if($poinList->count() > 1)
                                                    <button type="button" @click="open = !open"
                                                            class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                        <span x-show="!open">+ {{ $poinList->count() - 1 }} poin lainnya</span>
                                                        <span x-show="open" style="display:none;">Sembunyikan</span>
                                                        <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                             stroke-width="2.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                        </svg>
                                                    </button>

                                                    <ol start="2" x-show="open" x-cloak x-transition
                                                        class="mt-2 list-decimal list-inside space-y-1 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                        @foreach($poinList->slice(1) as $poin)
                                                            <li>{!! nl2br(e($poin->solusi)) !!}</li>
                                                        @endforeach
                                                    </ol>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        @if($item->is_approved)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Disetujui</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Menunggu</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('cetak.observasi', ['observasi_id' => $item->id]) }}" target="_blank"
                                           class="inline-flex items-center rounded-xl bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa] focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada observasi dari guru pembimbing.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $observasi->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
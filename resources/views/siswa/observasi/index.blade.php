<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Lembar Observasi PKL</h2>
    </x-slot>

    <style>
        [x-cloak]{display:none!important;}
        /* ===== Pergantian tampilan berbasis lebar layar (sama seperti Jurnal Siswa) ===== */
        .obs-desktop{ display:none; }   /* default: HP & Tablet -> tabel disembunyikan */
        .obs-mobile { display:block; }  /* default: HP & Tablet -> kartu tampil */
        @media (min-width:1024px){      /* laptop & PC (>=1024px) */
            .obs-desktop{ display:block; }  /* tabel tampil */
            .obs-mobile { display:none; }   /* kartu disembunyikan */
        }
    </style>

    <div class="py-8 md:py-12 bg-white">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12">

            {{-- ===== TOMBOL KEMBALI (paling atas) ===== --}}
            <div class="mb-6">
                <a href="{{ route('siswa.dashboard') }}"
                   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            {{-- ===== CARD MENU: CETAK ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm mb-6">
                 <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Menu Observasi</h3>
                        <p class="text-xs font-medium text-[#5b616e]">cetak seluruh riwayat jurnal kamu.</p>
                    </div>
                <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                    <a href="{{ route('cetak.observasi') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Semua (PDF)
                    </a>
                </div>
                </div>
            </div>

            {{-- ===== CARD UTAMA: FILTER + DATA ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('siswa.observasi.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                               class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                    <a href="{{ route('siswa.observasi.index') }}"
                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                </form>

                {{-- ============================================================= --}}
                {{-- ==========  TAMPILAN LAPTOP / PC (TABEL, >=1024px)  ========= --}}
                {{-- ============================================================= --}}
                <div class="obs-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1040px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3 font-bold w-40">Guru Pembimbing</th>
                                <th class="px-4 py-3 font-bold w-[24%]">Permasalahan</th>
                                <th class="px-4 py-3 font-bold w-[24%]">Solusi Pemecahan</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-44">Foto</th>
                                <th class="px-4 py-3 text-center font-bold w-20">Cetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($observasi as $item)
                                @php $isTervalidasi = ($item->status ?? 'draft') === 'tervalidasi'; @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $observasi->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                        {{ \Carbon\Carbon::parse($item->hari_tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $item->guru->name ?? '-' }}</td>
                                    {{-- ===== KOLOM PERMASALAHAN ===== --}}
                                    <td class="px-4 py-3 text-black break-words">
                                        @php $poinList = $item->items; @endphp
                                        @if($poinList && $poinList->count())
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
                                        @if($poinList && $poinList->count())
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
                                    {{-- ===== KOLOM STATUS ===== --}}
                                    <td class="px-4 py-3 text-center">
                                        @if($isTervalidasi)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-3 py-1 text-xs font-bold text-[#05b169]">Tervalidasi</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200]/10 px-3 py-1 text-xs font-bold text-[#d98200]">Draft</span>
                                        @endif
                                    </td>
                                    {{-- ===== KOLOM FOTO ===== --}}
                                    <td class="px-4 py-3 text-center">
                                        @if($item->foto_dokumentasi || $item->foto_lembar_observasi)
                                            <div class="flex flex-col items-center gap-1.5">
                                                @if($item->foto_dokumentasi)
                                                    <a href="{{ asset('storage/' . $item->foto_dokumentasi) }}" target="_blank" rel="noopener"
                                                       class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        Foto Dokumentasi
                                                    </a>
                                                @endif
                                                @if($item->foto_lembar_observasi)
                                                    <a href="{{ asset('storage/' . $item->foto_lembar_observasi) }}" target="_blank" rel="noopener"
                                                       class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-[#05b169]/10 px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/20">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        Lembar Berparaf
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('cetak.observasi', ['observasi_id' => $item->id]) }}" target="_blank"
                                           class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa] focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada observasi dari guru pembimbing.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ============================================================= --}}
                {{-- ============  TAMPILAN HP / TABLET (KARTU, <1024px)  ======== --}}
                {{-- ============================================================= --}}
                <div class="obs-mobile space-y-3">
                    @forelse ($observasi as $item)
                        @php
                            $isTervalidasi = ($item->status ?? 'draft') === 'tervalidasi';
                            $poinList = $item->items;
                            $tglLabel = \Carbon\Carbon::parse($item->hari_tanggal)->translatedFormat('d M Y');
                        @endphp
                      <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm"
     x-data="{ detail: false, openAjukan: false }"
     x-effect="document.body.style.overflow = (detail || openAjukan) ? 'hidden' : ''">
                            {{-- Ringkas: GURU + tanggal + status (kiri) + LIHAT DETAIL (kanan) --}}
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-black break-words">{{ $item->guru->name ?? '-' }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-medium text-[#5b616e]">{{ $tglLabel }}</span>
                                        @if($isTervalidasi)
                                            <span class="inline-block rounded-full bg-[#05b169]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#05b169]">Tervalidasi</span>
                                        @else
                                            <span class="inline-block rounded-full bg-[#d98200]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#d98200]">Draft</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- ===== TOMBOL LIHAT DETAIL DI KANAN ===== --}}
                                <button type="button" @click="detail = true"
                                        class="inline-flex flex-shrink-0 items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Lihat Detail
                                </button>
                            </div>

                            {{-- ===== POP-UP DETAIL ===== --}}
                            <div x-show="detail" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
     @keydown.escape.window="detail = false">
                                <div x-show="detail"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
     x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
     class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl text-left"
     @click.outside="detail = false">
                                    <div class="sticky top-0 flex items-center justify-between border-b-2 border-[#0047d6]/15 bg-white px-5 py-3">
                                        <h3 class="text-base font-bold text-black">Detail Observasi</h3>
                                        <button type="button" @click="detail = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                    </div>

                                    <div class="space-y-4 px-5 py-4">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p>
                                                <p class="text-sm font-medium text-black">{{ $tglLabel }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Status</p>
                                                @if($isTervalidasi)
                                                    <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-2.5 py-1 text-xs font-bold text-[#05b169]">Tervalidasi</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-[#d98200]/10 px-2.5 py-1 text-xs font-bold text-[#d98200]">Draft</span>
                                                @endif
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Guru Pembimbing</p>
                                                <p class="text-sm font-medium text-black break-words">{{ $item->guru->name ?? '-' }}</p>
                                            </div>
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Permasalahan</p>
                                            @if($poinList && $poinList->count())
                                                <ol class="list-decimal list-inside space-y-1 text-sm font-medium text-black">
                                                    @foreach($poinList as $poin)
                                                        <li>{!! nl2br(e($poin->permasalahan)) !!}</li>
                                                    @endforeach
                                                </ol>
                                            @else
                                                <p class="text-sm text-[#5b616e]">-</p>
                                            @endif
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Solusi Pemecahan</p>
                                            @if($poinList && $poinList->count())
                                                <ol class="list-decimal list-inside space-y-1 text-sm font-medium text-black">
                                                    @foreach($poinList as $poin)
                                                        <li>{!! nl2br(e($poin->solusi)) !!}</li>
                                                    @endforeach
                                                </ol>
                                            @else
                                                <p class="text-sm text-[#5b616e]">-</p>
                                            @endif
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-2">Foto</p>
                                            @if($item->foto_dokumentasi || $item->foto_lembar_observasi)
                                                <div class="flex flex-wrap gap-2">
                                                    @if($item->foto_dokumentasi)
                                                        <a href="{{ asset('storage/' . $item->foto_dokumentasi) }}" target="_blank" rel="noopener"
                                                           class="inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                            Foto Dokumentasi
                                                        </a>
                                                    @endif
                                                    @if($item->foto_lembar_observasi)
                                                        <a href="{{ asset('storage/' . $item->foto_lembar_observasi) }}" target="_blank" rel="noopener"
                                                           class="inline-flex items-center gap-1 rounded-full bg-[#05b169]/10 px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/20">
                                                            Lembar Berparaf
                                                        </a>
                                                    @endif
                                                </div>
                                            @else
                                                <p class="text-sm text-[#5b616e]">Tidak ada</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="sticky bottom-0 space-y-2 border-t-2 border-[#0047d6]/15 bg-white px-5 py-4">
                                        <a href="{{ route('cetak.observasi', ['observasi_id' => $item->id]) }}" target="_blank"
                                           class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                            </svg>
                                            Cetak PDF Observasi
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white px-4 py-8 text-center font-medium text-[#5b616e] italic">
                            Belum ada observasi dari guru pembimbing.
                        </div>
                    @endforelse
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $observasi->links() !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
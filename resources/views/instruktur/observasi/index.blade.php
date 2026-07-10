<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">
                Persetujuan Lembar Observasi
            </h2>
            <a href="{{ route('instruktur.dashboard') }}"
   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
    Kembali ke Dashboard
</a>
        </div>
    </x-slot>

    

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

          <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
        <div class="flex items-center gap-3 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm transition hover:border-[#0047d6]/40 sm:gap-4 sm:p-5">
           
            <div class="min-w-0">
                <p class="truncate text-xs font-bold uppercase tracking-wide text-[#0047d6] sm:text-sm">Total Observasi</p>
                <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['total'] }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm transition hover:border-[#0047d6]/40 sm:gap-4 sm:p-5">
           
            <div class="min-w-0">
                <p class="truncate text-xs font-bold uppercase tracking-wide text-[#0047d6] sm:text-sm">Disetujui</p>
                <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['disetujui'] }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm transition hover:border-[#0047d6]/40 sm:gap-4 sm:p-5">
          
            <div class="min-w-0">
                <p class="truncate text-xs font-bold uppercase tracking-wide text-[#0047d6] sm:text-sm">Menunggu</p>
                <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['menunggu'] }}</p>
            </div>
        </div>
    </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ===== BANNER & TOMBOL CETAK SEMUA ===== --}}
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Lembar Observasi Siswa Bimbingan</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak seluruh lembar observasi — 1 observasi per halaman.</p>
                    </div>

                    <a href="{{ route('cetak.observasi.semua') }}" target="_blank"
                       class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                        </svg>
                        Cetak Semua PDF
                    </a>
                </div>

                {{-- ===== FILTER ===== --}}
                <form method="GET" action="{{ route('instruktur.observasi.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>

                        <div class="w-full md:w-56">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                            <select name="status"
                                    class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="disetujui" @selected(request('status') === 'disetujui')>Sudah Disetujui</option>
                                <option value="belum" @selected(request('status') === 'belum')>Belum (Menunggu)</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                                Cari
                            </button>
                            <a href="{{ route('instruktur.observasi.index') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL DATA UTAMA ===== --}}
                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1100px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3 font-bold w-36">Nama Siswa</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 font-bold w-[26%]">Permasalahan</th>
                                <th class="px-4 py-3 font-bold w-[26%]">Solusi Pemecahan</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($observasi as $item)
                                @php $poin = $item->items; @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5" x-data="{ open: false }">
                                    <td class="px-4 py-3 text-center font-semibold text-black">
                                         {{ $observasi->firstItem() + $loop->index }} 
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                         {{ \Carbon\Carbon::parse($item->hari_tanggal)->translatedFormat('d M Y') }} 
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words">
                                         {{ $item->user->name ?? '-' }} 
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                         {{ $item->user->nisn ?? '-' }} 
                                    </td>

                                    {{-- ===== KOLOM PERMASALAHAN ===== --}}
                                    <td class="px-4 py-3 text-black break-words">
                                        @if($poin->count())
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words">{{ $poin->first()->permasalahan ?? '-' }}</span>
                                            </div>

                                            @if($poin->count() > 1)
                                                <button type="button" @click="open = !open"
                                                        class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                    <span x-show="!open">+ {{ $poin->count() - 1 }} lainnya</span>
                                                    <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                         stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>

                                                <ol start="2" x-show="open" x-cloak x-transition
                                                    class="mt-2 list-decimal list-inside space-y-0.5 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                    @foreach($poin->slice(1) as $it)
                                                        <li class="break-words">{{ $it->permasalahan }}</li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>

                                    {{-- ===== KOLOM SOLUSI ===== --}}
                                    <td class="px-4 py-3 text-black break-words">
                                        @if($poin->count())
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words">{{ $poin->first()->solusi ?? '-' }}</span>
                                            </div>

                                            @if($poin->count() > 1)
                                                <button type="button" @click="open = !open"
                                                        class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                    <span x-show="!open">+ {{ $poin->count() - 1 }} lainnya</span>
                                                    <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                         stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>

                                                <ol start="2" x-show="open" x-cloak x-transition
                                                    class="mt-2 list-decimal list-inside space-y-0.5 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                    @foreach($poin->slice(1) as $it)
                                                        <li class="break-words">{{ $it->solusi }}</li>
                                                    @endforeach
                                                </ol>
                                            @endif
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

                                    {{-- ===== KOLOM MANAJEMEN AKSI ===== --}}
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-stretch gap-2">
                                            @if($item->is_approved)
                                               <form action="{{ route('instruktur.observasi.batal', $item->id) }}" method="POST"
      data-confirm="Batalkan persetujuan observasi {{ $item->nama ?? 'ini' }}?"
      data-confirm-text="Observasi akan kembali berstatus menunggu."
      data-confirm-yes="Ya, batalkan">
    @csrf
    @method('PUT')
    <button type="submit"
            class="w-full inline-flex items-center justify-center rounded-full bg-[#d98200] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#b56d00]">
        Batalkan Persetujuan
    </button>
</form>
                                            @else
                                                <form action="{{ route('instruktur.observasi.approve', $item->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit"
                                                            class="w-full inline-flex items-center justify-center rounded-full bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049a5b]">
                                                        Setujui
                                                    </button>
                                                </form>
                                            @endif

                                            <a href="{{ route('cetak.observasi', ['siswa_id' => $item->user_id, 'observasi_id' => $item->id]) }}" target="_blank"
                                               class="w-full inline-flex items-center justify-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                Cetak PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada observasi untuk disetujui.</td>
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
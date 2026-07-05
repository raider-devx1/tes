<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">
                Persetujuan Jurnal Siswa
            </h2>
           <a href="{{ route('instruktur.dashboard') }}"
   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
    Kembali ke Dashboard
</a>
        </div>
    </x-slot>

    {{-- Cegah kedip x-cloak sebelum Alpine siap --}}
    <style>[x-cloak]{display:none !important;}</style>

    <div class="py-8 md:py-12 bg-white"
         x-data="{
            open: false,
            action: '',
            status: 'pending',
            catatan: '',
            siswa: '',
            tanggal: '',
            openModal(d){
                this.action  = d.action;
                this.status  = d.status;
                this.catatan = d.catatan ?? '';
                this.siswa   = d.siswa;
                this.tanggal = d.tanggal;
                this.open    = true;
            }
         }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <div class="flex items-center gap-3 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm transition hover:border-[#0047d6]/40 sm:gap-4 sm:p-5">
           
            <div class="min-w-0">
                <p class="truncate text-xs font-bold uppercase tracking-wide text-[#0047d6] sm:text-sm">Total Jurnal</p>
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
                <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['pending'] }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm transition hover:border-[#0047d6]/40 sm:gap-4 sm:p-5">
           
            <div class="min-w-0">
                <p class="truncate text-xs font-bold uppercase tracking-wide text-[#0047d6] sm:text-sm">Revisi</p>
                <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['revisi'] }}</p>
            </div>
        </div>
    </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

            
                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- ====== TOOLBAR: CETAK SEMUA PDF ====== --}}
                <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Cetak Jurnal Bimbingan</h3>
                        <p class="text-xs font-medium text-[#5b616e]">
                            Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak jurnal
                            <span class="font-bold text-black">
                                @if(request('tanggal'))
                                    tanggal {{ \Carbon\Carbon::parse(request('tanggal'))->translatedFormat('d M Y') }}
                                @else
                                    hari ini ( {{ \Carbon\Carbon::today()->translatedFormat('d M Y') }} )
                                @endif
                            </span>
                            — 1 siswa per halaman. Untuk tanggal lain, gunakan filter di bawah.
                        </p>
                    </div>

                    <a href="{{ route('cetak.jurnal.semua', ['tanggal' => request('tanggal')]) }}" target="_blank"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#05b169] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#049a5b] focus:outline-none focus:ring-4 focus:ring-[#05b169]/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                        </svg>
                        Cetak Semua PDF
                    </a>
                </div>

                {{-- ====== FILTER ====== --}}
                <form method="GET" action="{{ route('instruktur.jurnal.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Ketik nama atau NISN siswa..."
                               class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                    </div>
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
                            <option value="revisi" @selected(request('status') === 'revisi')>Revisi</option>
                            <option value="pending" @selected(request('status') === 'pending')>Menunggu</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Cari</button>
                    <a href="{{ route('instruktur.jurnal.index') }}"
                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                </form>

                {{-- ====== TABEL ====== --}}
                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1100px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-36">Nama Siswa</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3 font-bold w-[24%]">Unit Kerja</th>
                                <th class="px-4 py-3 font-bold w-[18%]">Catatan Instruktur</th>
                                <th class="px-4 py-3 text-center font-bold w-36">Foto</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Tindakan</th>
                                <th class="px-4 py-3 text-center font-bold w-20">Cetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse($jurnals as $jurnal)
                                @php
                                    $statusMap = [
                                        'pending'   => ['label' => 'Menunggu',  'badge' => 'bg-[#d98200]', 'btn' => 'bg-[#0047d6] hover:bg-[#0038aa]', 'btnLabel' => 'Validasi'],
                                        'disetujui' => ['label' => 'Disetujui', 'badge' => 'bg-[#05b169]', 'btn' => 'bg-[#05b169] hover:bg-[#049a5b]', 'btnLabel' => 'Ubah Status'],
                                        'revisi'    => ['label' => 'Revisi',    'badge' => 'bg-[#cf202f]', 'btn' => 'bg-[#cf202f] hover:bg-[#a81824]', 'btnLabel' => 'Tinjau Ulang'],
                                    ];
                                    $st = $statusMap[$jurnal->status_persetujuan] ?? $statusMap['pending'];
                                    $tglJurnal = \Carbon\Carbon::parse($jurnal->hari_tanggal)->translatedFormat('d M Y');
                                    $fotos = $jurnal->items->whereNotNull('dokumentasi')->values();
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $jurnals->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $jurnal->siswa->name ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $jurnal->siswa->nisn ?? '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $tglJurnal }}</td>

                                    {{-- ====== UNIT KERJA COLAAPSIBLE ====== --}}
                                    <td class="px-4 py-3 text-black break-words">
                                        @php $items = $jurnal->items; @endphp
                                        @if($items->count())
                                            <div x-data="{ open: false }">
                                                <div class="flex items-start gap-1.5">
                                                    <span class="font-bold text-[#0047d6]">1.</span>
                                                    <span class="font-medium break-words">{{ $items->first()->unit_kerja }}</span>
                                                </div>

                                                @if($items->count() > 1)
                                                    <button type="button" @click="open = !open"
                                                            class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                        <span x-show="!open">+ {{ $items->count() - 1 }} pekerjaan lainnya</span>
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

                                    <td class="px-4 py-3 text-black break-words">
                                        @if($jurnal->catatan_instruktur)
                                            <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-xs font-medium italic text-black">
                                                 {{ $jurnal->catatan_instruktur }}
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>

                                    {{-- ====== FOTO ATTACHMENT ====== --}}
                                    <td class="px-4 py-3 text-center">
                                        @if($fotos->count())
                                            <div class="flex flex-col gap-1.5">
                                                @foreach($fotos as $k => $it)
                                                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                                                        <span class="text-xs font-semibold text-black">Foto {{ $k + 1 }}</span>
                                                        <a href="{{ asset('storage/' . $it->dokumentasi) }}" target="_blank"
                                                           class="inline-flex items-center rounded-full bg-[#0047d6] px-2.5 py-1 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                            Lihat
                                                        </a>
                                                        <a href="{{ asset('storage/' . $it->dokumentasi) }}"
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
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold text-white {{ $st['badge'] }}">
                                             {{ $st['label'] }}
                                        </span>
                                    </td>

                                    {{-- ====== TINDAKAN TOMBOL MODAL VALIASI ====== --}}
                                    <td class="px-4 py-3 text-center">
                                        <button type="button"
                                                @click="openModal({
                                                    action: '{{ route('instruktur.jurnal.update', $jurnal->id) }}',
                                                    status: '{{ $jurnal->status_persetujuan }}',
                                                    catatan: @js($jurnal->catatan_instruktur),
                                                    siswa: @js($jurnal->siswa->name ?? '-'),
                                                    tanggal: '{{ $tglJurnal }}'
                                                })"
                                                class="inline-flex items-center justify-center rounded-full px-4 py-1.5 text-xs font-bold text-white transition {{ $st['btn'] }}">
                                             {{ $st['btnLabel'] }}
                                        </button>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('cetak.jurnal', ['siswa_id' => $jurnal->siswa_id, 'jurnal_id' => $jurnal->id]) }}" target="_blank"
                                           class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada jurnal dari siswa bimbingan Anda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ====== PAGINATION ====== --}}
                <div class="mt-4">
                    {!! $jurnals->links() !!}
                </div>

            </div>
        </div>

        {{-- ====== MODAL VALIDASI JURNAL (ALPINE) ====== --}}
        <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
            <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

            <div class="relative w-full max-w-md rounded-2xl border-2 border-[#0047d6]/20 bg-white p-6 shadow-xl"
                 @keydown.escape.window="open = false">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-black">Validasi Jurnal</h3>
                        <p class="text-xs font-medium text-[#5b616e]">
                            <span x-text="siswa"></span> • <span x-text="tanggal"></span>
                        </p>
                    </div>
                    <button type="button" @click="open = false"
                            class="rounded-full p-1 text-[#5b616e] transition hover:bg-[#0047d6]/10 hover:text-black">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="action" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Persetujuan</label>
                        <select name="status_persetujuan" x-model="status"
                                class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            <option value="pending">Menunggu</option>
                            <option value="disetujui">Setujui</option>
                            <option value="revisi">Revisi</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Catatan / Feedback</label>
                        <textarea name="catatan_instruktur" x-model="catatan" rows="3"
                                  placeholder="Tulis catatan untuk siswa (opsional)..."
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                                class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                            Batal
                        </button>
                        <button type="submit"
                                class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout title="Monitoring Jurnal">
    <style>
        [x-cloak]{display:none!important;}

        /* ===== Pergantian tampilan berbasis lebar layar (tanpa bergantung Tailwind lg:) ===== */
        .jurnal-desktop{ display:none; }   /* default: HP -> tabel disembunyikan */
        .jurnal-mobile { display:block; }  /* default: HP -> kartu tampil */

        @media (min-width:1024px){         /* laptop & PC (>=1024px) */
            .jurnal-desktop{ display:block; }  /* tabel tampil */
            .jurnal-mobile { display:none; }   /* kartu disembunyikan */
        }
    </style>

    {{--
        Responsif OTOMATIS:
        - >=1024px (laptop & PC): .jurnal-desktop tampil (tabel), .jurnal-mobile disembunyikan.
        - <1024px (HP & tablet kecil): .jurnal-mobile tampil (kartu ringkas), .jurnal-desktop disembunyikan.
    --}}
    <div class="py-6 md:py-10 bg-slate-50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 space-y-6">

            {{-- ===== HEADER ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Monitoring &amp; Validasi Jurnal Siswa</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Pantau jurnal siswa bimbingan Anda dan lakukan validasi bukti fisik.</p>
                </div>
                <a href="{{ route('guru.dashboard') }}"
                   class="inline-flex items-center justify-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    Kembali ke Dashboard
                </a>
            </div>

            {{-- ===== ALERT ===== --}}
            @if(session('success'))
                <div class="rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ===== REKAP ===== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Jurnal</p>
                    <p class="mt-1 text-2xl font-bold text-black">{{ $rekap['total'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Disetujui</p>
                    <p class="mt-1 text-2xl font-bold text-[#05b169]">{{ $rekap['disetujui'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Diajukan</p>
                    <p class="mt-1 text-2xl font-bold text-[#d98200]">{{ $rekap['diajukan'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Draft</p>
                    <p class="mt-1 text-2xl font-bold text-[#5b616e]">{{ $rekap['draft'] ?? 0 }}</p>
                </div>
            </div>

            {{-- ===== CETAK SEMUA ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-black">Jurnal Kegiatan Siswa Bimbingan</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak jurnal sesuai
                        <span class="font-bold text-black">filter tanggal</span> di bawah. Bila tanggal dikosongkan, otomatis mencetak jurnal <span class="font-bold text-black">hari ini</span> (1 siswa per halaman).
                    </p>
                </div>

                <a href="{{ route('cetak.jurnal.semua') }}" target="_blank"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#cf202f] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#a81824] focus:outline-none focus:ring-4 focus:ring-[#cf202f]/30 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                    </svg>
                    Cetak Semua PDF
                </a>
            </div>

            {{-- ===== FILTER ===== --}}
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
                        <option value="diajukan" @selected(request('status') === 'diajukan')>Diajukan</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
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

            {{-- ============================================================= --}}
            {{-- ==========  TAMPILAN LAPTOP / PC (TABEL, >=1024px)  ========== --}}
            {{-- ============================================================= --}}
            <div class="jurnal-desktop overflow-hidden rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full text-sm text-left table-auto">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                            <th class="px-4 py-3 font-bold">Tanggal</th>
                            <th class="px-4 py-3 font-bold">Nama</th>
                            <th class="px-4 py-3 font-bold">NISN</th>
                            <th class="px-4 py-3 font-bold">Unit Kerja</th>
                            <th class="px-4 py-3 font-bold">Catatan Instruktur</th>
                            <th class="px-4 py-3 font-bold">Foto Kegiatan</th>
                            <th class="px-4 py-3 text-center font-bold">Status</th>
                            <th class="px-4 py-3 text-center font-bold">Validasi</th>
                            <th class="px-4 py-3 text-center font-bold">Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($jurnals as $jurnal)
                            @php
                                $badgeStatus = match($jurnal->status) {
                                    'disetujui' => 'bg-[#05b169] text-white',
                                    'diajukan'  => 'bg-[#d98200] text-white',
                                    'draft'     => 'bg-[#5b616e] text-white',
                                    default     => 'bg-[#5b616e] text-white',
                                };
                                $labelStatus = match($jurnal->status) {
                                    'disetujui' => 'Disetujui',
                                    'diajukan'  => 'Diajukan',
                                    'draft'     => 'Draft',
                                    default     => ucfirst((string) $jurnal->status),
                                };
                                $items = $jurnal->items;
                                $fotos = $jurnal->items->whereNotNull('dokumentasi')->values();
                            @endphp
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black">{{ $jurnals->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $jurnal->hari_tanggal->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-bold text-black break-words">{{ $jurnal->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $jurnal->siswa->nisn ?? '-' }}</td>

                                <td class="px-4 py-3 text-black break-words">
                                    @if($items->count())
                                        <div x-data="{ open: false }">
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words">{{ $items->first()->unit_kerja }}</span>
                                            </div>
                                            @if($items->count() > 1)
                                                <button type="button" @click="open = !open"
                                                        class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                    <span x-show="!open">+ {{ $items->count() - 1 }} unit kerja lainnya</span>
                                                    <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
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

                                {{-- ===== VALIDASI ===== --}}
                                <td class="px-4 py-3 text-center">
                                    <div x-data="{ openValidasi: false }" class="flex flex-col items-center gap-1.5">
                                        @if($jurnal->foto_bukti)
                                            @php $extBukti = pathinfo($jurnal->foto_bukti, PATHINFO_EXTENSION); @endphp
                                            <a href="{{ asset('storage/'.$jurnal->foto_bukti) }}" target="_blank" rel="noopener"
                                               class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                Lihat Bukti
                                            </a>
                                            <a href="{{ asset('storage/'.$jurnal->foto_bukti) }}"
                                               download="bukti-jurnal-{{ $jurnal->siswa->nisn ?? $jurnal->id }}-{{ $jurnal->id . '.' . $extBukti }}"
                                               class="inline-flex w-full items-center justify-center gap-1 rounded-full border-2 border-[#0047d6] bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                Download Bukti
                                            </a>
                                        @endif

                                        @if($jurnal->status === 'diajukan')
                                            <button type="button" @click="openValidasi = true"
                                                    class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049a5b]">
                                                Validasi
                                            </button>
                                        @elseif($jurnal->status === 'disetujui')
                                            <span class="inline-flex w-full items-center justify-center rounded-full bg-[#05b169]/10 px-3 py-1.5 text-xs font-bold text-[#05b169]">
                                                Tervalidasi
                                            </span>
                                        @else
                                            <span class="text-xs font-medium text-[#5b616e]">Belum diajukan</span>
                                        @endif

                                        @if($jurnal->status === 'diajukan')
                                            <div x-show="openValidasi" x-cloak
                                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                                 @keydown.escape.window="openValidasi = false">
                                                <div class="w-full max-w-md rounded-2xl bg-white shadow-xl text-left" @click.outside="openValidasi = false">
                                                    <div class="flex items-center justify-between border-b-2 border-[#0047d6]/15 px-5 py-3">
                                                        <h3 class="text-base font-bold text-black">Validasi Jurnal — {{ $jurnal->siswa->name ?? '-' }}</h3>
                                                        <button type="button" @click="openValidasi = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                                    </div>
                                                    <div class="space-y-3 px-5 py-4">
                                                        <p class="text-sm font-medium text-black">
                                                            Jurnal tanggal <span class="font-bold">{{ $jurnal->hari_tanggal->format('d/m/Y') }}</span>.
                                                            Pastikan Anda telah memeriksa bukti fisik melalui tombol
                                                            <span class="font-bold text-[#0047d6]">Lihat Bukti</span> sebelum menyetujui.
                                                        </p>
                                                        @if($jurnal->catatan_instruktur)
                                                            <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-3">
                                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Catatan Instruktur</p>
                                                                <p class="text-sm font-medium text-black">{{ $jurnal->catatan_instruktur }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex justify-end gap-2 border-t-2 border-[#0047d6]/15 px-5 py-3">
                                                        <form action="{{ route('guru.jurnal.validasi', $jurnal->id) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="aksi" value="tolak">
                                                            <button type="submit" class="rounded-xl bg-[#cf202f]/10 px-4 py-2 text-sm font-bold text-[#cf202f] hover:bg-[#cf202f]/20">Tolak</button>
                                                        </form>
                                                        <form action="{{ route('guru.jurnal.validasi', $jurnal->id) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="aksi" value="valid">
                                                            <button type="submit" class="rounded-xl bg-[#05b169] px-5 py-2 text-sm font-bold text-white hover:bg-[#049a5b]">Valid (Setujui)</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- ===== CETAK (MERAH + IKON PRINT) ===== --}}
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cetak.jurnal', ['siswa_id' => $jurnal->siswa_id, 'jurnal_id' => $jurnal->id]) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 rounded-full bg-[#cf202f] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#a81824]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                        </svg>
                                        {{ $jurnal->status === 'disetujui' ? 'PDF Final' : 'Cetak Draf' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada jurnal yang diisi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============================================================= --}}
            {{-- ============  TAMPILAN HP (KARTU RINGKAS)  ========= --}}
            {{-- ============================================================= --}}
            <div class="jurnal-mobile space-y-3">
                @forelse ($jurnals as $jurnal)
                    @php
                        $badgeStatus = match($jurnal->status) {
                            'disetujui' => 'bg-[#05b169] text-white',
                            'diajukan'  => 'bg-[#d98200] text-white',
                            'draft'     => 'bg-[#5b616e] text-white',
                            default     => 'bg-[#5b616e] text-white',
                        };
                        $labelStatus = match($jurnal->status) {
                            'disetujui' => 'Disetujui',
                            'diajukan'  => 'Diajukan',
                            'draft'     => 'Draft',
                            default     => ucfirst((string) $jurnal->status),
                        };
                        $items = $jurnal->items;
                        $fotos = $jurnal->items->whereNotNull('dokumentasi')->values();
                        $extBukti = $jurnal->foto_bukti ? pathinfo($jurnal->foto_bukti, PATHINFO_EXTENSION) : null;
                    @endphp

                   <div x-data="{ detail: false }"
     x-effect="document.body.style.overflow = detail ? 'hidden' : ''"
     class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm">
                        {{-- Ringkas: NAMA + AKSI --}}
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-black truncate">{{ $jurnal->siswa->name ?? '-' }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="text-xs font-medium text-[#5b616e]">{{ $jurnal->hari_tanggal->format('d/m/Y') }}</span>
                                    <span class="inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $badgeStatus }}">{{ $labelStatus }}</span>
                                </div>
                            </div>
                            <button type="button" @click="detail = true"
                                    class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Lihat Detail
                            </button>
                        </div>

                        {{-- Pop-up card: SEMUA info yang tampil di tabel laptop --}}
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
                                    <h3 class="text-base font-bold text-black">Detail Jurnal</h3>
                                    <button type="button" @click="detail = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                </div>

                                <div class="space-y-4 px-5 py-4">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Nama</p>
                                            <p class="text-sm font-bold text-black">{{ $jurnal->siswa->name ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">NISN</p>
                                            <p class="text-sm font-medium text-black">{{ $jurnal->siswa->nisn ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p>
                                            <p class="text-sm font-medium text-black">{{ $jurnal->hari_tanggal->format('d/m/Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Status</p>
                                            <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badgeStatus }}">{{ $labelStatus }}</span>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Unit Kerja</p>
                                        @if($items->count())
                                            <ol class="list-decimal list-inside space-y-0.5 text-sm font-medium text-black">
                                                @foreach($items as $it)
                                                    <li class="break-words">{{ $it->unit_kerja }}</li>
                                                @endforeach
                                            </ol>
                                        @else
                                            <p class="text-sm text-[#5b616e]">-</p>
                                        @endif
                                    </div>

                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Catatan Instruktur</p>
                                        @if($jurnal->catatan_instruktur)
                                            <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-sm font-medium italic text-black">
                                                {{ $jurnal->catatan_instruktur }}
                                            </div>
                                        @else
                                            <p class="text-sm text-[#5b616e]">-</p>
                                        @endif
                                    </div>

                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Foto Kegiatan</p>
                                        @if($fotos->count())
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($fotos as $k => $it)
                                                    <a href="{{ asset('storage/'.$it->dokumentasi) }}" target="_blank"
                                                       class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                        Foto {{ $k + 1 }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-sm text-[#5b616e]">Tidak ada</p>
                                        @endif
                                    </div>

                                    @if($jurnal->foto_bukti)
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Bukti Fisik</p>
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ asset('storage/'.$jurnal->foto_bukti) }}" target="_blank" rel="noopener"
                                                   class="inline-flex items-center gap-1 rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                    Lihat Bukti
                                                </a>
                                                <a href="{{ asset('storage/'.$jurnal->foto_bukti) }}"
                                                   download="bukti-jurnal-{{ $jurnal->siswa->nisn ?? $jurnal->id }}-{{ $jurnal->id . '.' . $extBukti }}"
                                                   class="inline-flex items-center gap-1 rounded-full border-2 border-[#0047d6] bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                    Download Bukti
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="sticky bottom-0 space-y-2 border-t-2 border-[#0047d6]/15 bg-white px-5 py-4">
                                    @if($jurnal->status === 'diajukan')
                                        <div class="flex gap-2">
                                            <form action="{{ route('guru.jurnal.validasi', $jurnal->id) }}" method="POST" class="flex-1">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="aksi" value="tolak">
                                                <button type="submit" class="w-full rounded-xl bg-[#cf202f]/10 px-4 py-2.5 text-sm font-bold text-[#cf202f] hover:bg-[#cf202f]/20">Tolak</button>
                                            </form>
                                            <form action="{{ route('guru.jurnal.validasi', $jurnal->id) }}" method="POST" class="flex-1">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="aksi" value="valid">
                                                <button type="submit" class="w-full rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#049a5b]">Valid (Setujui)</button>
                                            </form>
                                        </div>
                                    @elseif($jurnal->status === 'disetujui')
                                        <p class="text-center text-sm font-bold text-[#05b169]">✓ Sudah Tervalidasi</p>
                                    @else
                                        <p class="text-center text-sm font-medium text-[#5b616e]">Belum diajukan siswa</p>
                                    @endif

                                    <a href="{{ route('cetak.jurnal', ['siswa_id' => $jurnal->siswa_id, 'jurnal_id' => $jurnal->id]) }}" target="_blank"
                                       class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#cf202f] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#a81824]">
                                        <svg xmlns="http://www.w3.org/2000/xl" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                        </svg>
                                        {{ $jurnal->status === 'disetujui' ? 'Cetak PDF Final' : 'Cetak Draf PDF' }}
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white px-4 py-8 text-center font-medium text-[#5b616e] italic">
                        Belum ada jurnal yang diisi.
                    </div>
                @endforelse
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div class="mt-4">
                {!! $jurnals->links() !!}
            </div>

        </div>
    </div>
</x-app-layout>
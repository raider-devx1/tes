<x-app-layout>
    <style>
        [x-cloak]{display:none!important;}
        /* ===== Pergantian tampilan berbasis lebar layar (sama seperti Jurnal Guru) ===== */
        .obs-desktop{ display:none; }   /* default: HP -> tabel disembunyikan */
        .obs-mobile { display:block; }  /* default: HP -> kartu tampil */
        @media (min-width:1024px){      /* laptop & PC (>=1024px) */
            .obs-desktop{ display:block; }  /* tabel tampil */
            .obs-mobile { display:none; }   /* kartu disembunyikan */
        }
    </style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">
                Lembar Observasi
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('guru.observasi.create') }}"
                   class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                    Tambah Observasi
                </a>
                <a href="{{ route('guru.dashboard') }}"
                   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white min-h-screen">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12">
            <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Observasi</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Draft</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['draft'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Tervalidasi</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['tervalidasi'] }}</p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">
                @if (session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Lembar Observasi Siswa Bimbingan</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Buat draf &rarr; cetak &rarr; minta paraf instruktur &amp; guru &rarr; <span class="font-bold text-black">Validasi</span> (unggah foto). Setelah tervalidasi, hasil cetak menampilkan keterangan <span class="font-bold text-black">SUDAH DIVALIDASI</span>.</p>
                    </div>
                    <a href="{{ route('cetak.observasi.semua') }}" target="_blank"
                       class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#cf202f] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#a81824] focus:outline-none focus:ring-4 focus:ring-[#cf202f]/30 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                        </svg>
                        Cetak Semua PDF
                    </a>
                </div>

                <form method="GET" action="{{ route('guru.observasi.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                Cari (Nama / NISN)
                            </label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                                Cari
                            </button>
                            <a href="{{ route('guru.observasi.index') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ============================================================= --}}
                {{-- ==========  TAMPILAN LAPTOP / PC (TABEL, >=1024px)  ========= --}}
                {{-- ============================================================= --}}
                <div class="obs-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1300px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3 font-bold w-36">Siswa</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 font-bold w-40">Pekerjaan/Projek</th>
                                <th class="px-4 py-3 font-bold w-[20%]">Permasalahan</th>
                                <th class="px-4 py-3 font-bold w-[20%]">Solusi Pemecahan</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Foto</th>
                                <th class="px-4 py-3 text-center font-bold w-20">Cetak</th>
                                <th class="px-4 py-3 text-center font-bold w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($observasi as $obs)
                                @php
                                    $poin = $obs->items;
                                    $isTervalidasi = ($obs->status ?? 'draft') === 'tervalidasi';
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5" x-data="{ open: false, showValidasi: false }" x-effect="document.body.style.overflow = showValidasi ? 'hidden' : ''">
                                    <td class="px-4 py-3 text-center font-semibold text-black">
                                        {{ $observasi->firstItem() + $loop->index }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                        {{ \Carbon\Carbon::parse($obs->hari_tanggal)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words">
                                        {{ $obs->user->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                        {{ $obs->user->nisn ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-black break-words">
                                        {{ $obs->pekerjaan_projek ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-black break-words">
                                        @if($poin && $poin->count())
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words">{{ $poin->first()->permasalahan }}</span>
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
                                    <td class="px-4 py-3 text-black break-words">
                                        @if($poin && $poin->count())
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words">{{ $poin->first()->solusi }}</span>
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
                                        @if($isTervalidasi)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-3 py-1 text-xs font-bold text-[#05b169]">Tervalidasi</span>
                                            @if($obs->validated_at)
                                                <p class="mt-1 text-[10px] font-medium text-[#5b616e]">{{ \Carbon\Carbon::parse($obs->validated_at)->format('d M Y') }}</p>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200]/10 px-3 py-1 text-xs font-bold text-[#d98200]">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($obs->foto_dokumentasi || $obs->foto_lembar_observasi)
                                            <div class="flex flex-col items-center gap-1.5">
                                                @if ($obs->foto_dokumentasi)
                                                    <a href="{{ asset('storage/' . $obs->foto_dokumentasi) }}" download target="_blank" rel="noopener"
                                                       class="inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                        Foto Dokumentasi
                                                    </a>
                                                @endif
                                                @if ($obs->foto_lembar_observasi)
                                                    <a href="{{ asset('storage/' . $obs->foto_lembar_observasi) }}" download target="_blank" rel="noopener"
                                                       class="inline-flex items-center gap-1 rounded-full bg-[#05b169]/10 px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/20">
                                                        Lembar Berparaf
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('cetak.observasi', ['siswa_id' => $obs->user_id, 'observasi_id' => $obs->id]) }}" target="_blank"
                                           class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">PDF</a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <button type="button" @click="showValidasi = true"
                                                    class="inline-flex w-full items-center justify-center rounded-full bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049457]">
                                                {{ $isTervalidasi ? 'Validasi Ulang' : 'Validasi' }}
                                            </button>
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('guru.observasi.edit', $obs->id) }}"
                                                   class="inline-flex items-center rounded-full bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('guru.observasi.destroy', $obs->id) }}"
                                                      onsubmit="return confirm('Hapus observasi ini? Seluruh poin permasalahan & solusi pada observasi ini akan ikut terhapus.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center rounded-full bg-[#cf202f]/10 px-3 py-1.5 text-xs font-bold text-[#cf202f] transition hover:bg-[#cf202f]/20">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        {{-- ===== MODAL VALIDASI (desktop) ===== --}}
                                        <div x-show="showValidasi" x-cloak
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0"
                                             x-transition:enter-end="opacity-100"
                                             x-transition:leave="transition ease-in duration-200"
                                             x-transition:leave-start="opacity-100"
                                             x-transition:leave-end="opacity-0"
                                             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4 text-left"
                                             @keydown.escape.window="showValidasi = false">
                                            <div x-show="showValidasi"
                                                 x-transition:enter="transition ease-out duration-300"
                                                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                                                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                                 x-transition:leave="transition ease-in duration-200"
                                                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                                                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-6 text-left shadow-xl"
                                                 @click.outside="showValidasi = false">
                                                <div class="mb-4 flex items-center justify-between">
                                                    <h3 class="text-lg font-bold text-black">Validasi Lembar Observasi</h3>
                                                    <button type="button" @click="showValidasi = false"
                                                            class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                                </div>
                                                <p class="mb-4 text-sm text-[#5b616e]">
                                                    Unggah foto dokumentasi kegiatan dan foto lembar observasi yang sudah diparaf
                                                    <span class="font-semibold text-black">instruktur &amp; guru pembimbing</span>.
                                                    Setelah divalidasi, hasil cetak PDF akan menampilkan keterangan
                                                    <span class="font-bold text-black">SUDAH DIVALIDASI</span>.
                                                </p>
                                                <form method="POST" action="{{ route('guru.observasi.validasi', $obs->id) }}"
                                                      enctype="multipart/form-data" class="space-y-4">
                                                    @csrf
                                                    @method('PUT')
                                                    <div>
                                                        <label class="block text-sm font-bold text-black mb-1">
                                                            Foto Dokumentasi Kegiatan <span class="text-red-500">*</span>
                                                        </label>
                                                        <input type="file" name="foto_dokumentasi" accept="image/*" capture="environment" required
                                                               class="w-full text-sm text-gray-600 rounded-lg border border-gray-300 bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                                        <p class="mt-1 text-xs text-gray-500">Wajib. Format JPG/JPEG/PNG, maksimal 2 MB.</p>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-bold text-black mb-1">
                                                            Foto Lembar Observasi (Sudah Diparaf) <span class="text-red-500">*</span>
                                                        </label>
                                                        <input type="file" name="foto_lembar_observasi" accept="image/*" capture="environment" required
                                                               class="w-full text-sm text-gray-600 rounded-lg border border-gray-300 bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                                                        <p class="mt-1 text-xs text-gray-500">Wajib. Foto lembar fisik yang sudah diparaf instruktur &amp; guru pembimbing.</p>
                                                    </div>
                                                    <div class="flex justify-end gap-2 pt-2">
                                                        <button type="button" @click="showValidasi = false"
                                                                class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                            Batal
                                                        </button>
                                                        <button type="submit"
                                                                class="inline-flex items-center rounded-xl bg-[#05b169] px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#049457]">
                                                            Simpan Validasi
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada data observasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ============================================================= --}}
                {{-- ============  TAMPILAN HP (KARTU RINGKAS, <1024px)  ========= --}}
                {{-- ============================================================= --}}
                <div class="obs-mobile space-y-3">
                    @forelse ($observasi as $obs)
                        @php
                            $poin = $obs->items;
                            $isTervalidasi = ($obs->status ?? 'draft') === 'tervalidasi';
                        @endphp
                        <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm"
                             x-data="{ detail: false, showValidasi: false }"
                             x-effect="document.body.style.overflow = (detail || showValidasi) ? 'hidden' : ''">
                            {{-- Ringkas: SISWA (kiri) + AKSI (kanan) --}}
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-black truncate">{{ $obs->user->name ?? '-' }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-medium text-[#5b616e]">{{ \Carbon\Carbon::parse($obs->hari_tanggal)->format('d M Y') }}</span>
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

                            {{-- ===== POP-UP DETAIL: semua info tabel laptop ===== --}}
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
                                                <p class="text-sm font-medium text-black">{{ \Carbon\Carbon::parse($obs->hari_tanggal)->format('d M Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Status</p>
                                                @if($isTervalidasi)
                                                    <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-2.5 py-1 text-xs font-bold text-[#05b169]">Tervalidasi</span>
                                                    @if($obs->validated_at)
                                                        <p class="mt-1 text-[10px] font-medium text-[#5b616e]">{{ \Carbon\Carbon::parse($obs->validated_at)->format('d M Y') }}</p>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-[#d98200]/10 px-2.5 py-1 text-xs font-bold text-[#d98200]">Draft</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Siswa</p>
                                                <p class="text-sm font-bold text-black break-words">{{ $obs->user->name ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">NISN</p>
                                                <p class="text-sm font-medium text-black">{{ $obs->user->nisn ?? '-' }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Pekerjaan/Projek</p>
                                                <p class="text-sm font-medium text-black break-words">{{ $obs->pekerjaan_projek ?? '-' }}</p>
                                            </div>
                                        </div>
                                        {{-- Permasalahan --}}
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Permasalahan</p>
                                            @if($poin && $poin->count())
                                                <ol class="list-decimal list-inside space-y-0.5 text-sm font-medium text-black">
                                                    @foreach($poin as $it)
                                                        <li class="break-words">{{ $it->permasalahan }}</li>
                                                    @endforeach
                                                </ol>
                                            @else
                                                <span class="text-sm text-[#5b616e]">-</span>
                                            @endif
                                        </div>
                                        {{-- Solusi --}}
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Solusi Pemecahan</p>
                                            @if($poin && $poin->count())
                                                <ol class="list-decimal list-inside space-y-0.5 text-sm font-medium text-black">
                                                    @foreach($poin as $it)
                                                        <li class="break-words">{{ $it->solusi }}</li>
                                                    @endforeach
                                                </ol>
                                            @else
                                                <span class="text-sm text-[#5b616e]">-</span>
                                            @endif
                                        </div>
                                        {{-- Foto --}}
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-2">Foto</p>
                                            @if ($obs->foto_dokumentasi || $obs->foto_lembar_observasi)
                                                <div class="flex flex-wrap gap-2">
                                                    @if ($obs->foto_dokumentasi)
                                                        <a href="{{ asset('storage/' . $obs->foto_dokumentasi) }}" download target="_blank" rel="noopener"
                                                           class="inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                            Foto Dokumentasi
                                                        </a>
                                                    @endif
                                                    @if ($obs->foto_lembar_observasi)
                                                        <a href="{{ asset('storage/' . $obs->foto_lembar_observasi) }}" download target="_blank" rel="noopener"
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
                                    {{-- Footer aksi --}}
                                    <div class="sticky bottom-0 space-y-2 border-t-2 border-[#0047d6]/15 bg-white px-5 py-4">
                                        {{-- Validasi: tutup detail dulu agar modal validasi tampil penuh --}}
                                        <button type="button" @click="detail = false; showValidasi = true"
                                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#05b169] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#049457]">
                                            {{ $isTervalidasi ? 'Validasi Ulang' : 'Validasi' }}
                                        </button>
                                        <div class="flex gap-2">
                                            <a href="{{ route('cetak.observasi', ['siswa_id' => $obs->user_id, 'observasi_id' => $obs->id]) }}" target="_blank"
                                               class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                                </svg>
                                                Cetak PDF
                                            </a>
                                            <a href="{{ route('guru.observasi.edit', $obs->id) }}"
                                               class="flex flex-1 items-center justify-center rounded-xl bg-[#0047d6]/10 px-3 py-2.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('guru.observasi.destroy', $obs->id) }}"
                                                  class="flex-1"
                                                  onsubmit="return confirm('Hapus observasi ini? Seluruh poin permasalahan & solusi pada observasi ini akan ikut terhapus.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="flex w-full items-center justify-center rounded-xl bg-[#cf202f]/10 px-3 py-2.5 text-xs font-bold text-[#cf202f] transition hover:bg-[#cf202f]/20">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ===== MODAL VALIDASI (mobile) ===== --}}
                            <div x-show="showValidasi" x-cloak
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
                                 @keydown.escape.window="showValidasi = false">
                                <div x-show="showValidasi"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                                     class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-6 text-left shadow-xl"
                                     @click.outside="showValidasi = false">
                                    <div class="mb-4 flex items-center justify-between">
                                        <h3 class="text-lg font-bold text-black">Validasi Lembar Observasi</h3>
                                        <button type="button" @click="showValidasi = false"
                                                class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                    </div>
                                    <p class="mb-4 text-sm text-[#5b616e]">
                                        Unggah foto dokumentasi kegiatan dan foto lembar observasi yang sudah diparaf
                                        <span class="font-semibold text-black">instruktur &amp; guru pembimbing</span>.
                                        Setelah divalidasi, hasil cetak PDF akan menampilkan keterangan
                                        <span class="font-bold text-black">SUDAH DIVALIDASI</span>.
                                    </p>
                                    <form method="POST" action="{{ route('guru.observasi.validasi', $obs->id) }}"
                                          enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-sm font-bold text-black mb-1">
                                                Foto Dokumentasi Kegiatan <span class="text-red-500">*</span>
                                            </label>
                                            <input type="file" name="foto_dokumentasi" accept="image/*" capture="environment" required
                                                   class="w-full text-sm text-gray-600 rounded-lg border border-gray-300 bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            <p class="mt-1 text-xs text-gray-500">Wajib. Format JPG/JPEG/PNG, maksimal 2 MB.</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-black mb-1">
                                                Foto Lembar Observasi (Sudah Diparaf) <span class="text-red-500">*</span>
                                            </label>
                                            <input type="file" name="foto_lembar_observasi" accept="image/*" capture="environment" required
                                                   class="w-full text-sm text-gray-600 rounded-lg border border-gray-300 bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                                            <p class="mt-1 text-xs text-gray-500">Wajib. Foto lembar fisik yang sudah diparaf instruktur &amp; guru pembimbing.</p>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button type="button" @click="showValidasi = false"
                                                    class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                Batal
                                            </button>
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-xl bg-[#05b169] px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#049457]">
                                                Simpan Validasi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white px-4 py-8 text-center font-medium text-[#5b616e] italic">
                            Belum ada data observasi.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {!! $observasi->withQueryString()->links() !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

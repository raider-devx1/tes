<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">
                Rekap &amp; Penilaian (Guru Pembimbing)
            </h2>
            <a href="{{ route('guru.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <style>
        [x-cloak]{display:none!important;}
        /* ===== Pergantian tampilan berbasis lebar layar (tanpa bergantung Tailwind lg:) ===== */
        .nilai-desktop{ display:none; }   /* default: HP -> tabel disembunyikan */
        .nilai-mobile { display:block; }  /* default: HP -> kartu tampil */
        @media (min-width:1024px){        /* laptop & PC (>=1024px) */
            .nilai-desktop{ display:block; }  /* tabel tampil */
            .nilai-mobile { display:none; }   /* kartu disembunyikan */
        }
    </style>

    {{--
        Responsif OTOMATIS (sama seperti halaman Jurnal Guru):
        - >=1024px (laptop & PC): .nilai-desktop tampil (tabel penuh), kartu disembunyikan.
        - <1024px (HP & tablet kecil): .nilai-mobile tampil (kartu ringkas), tabel disembunyikan.
        - Lebar konten penuh kiri-kanan, dibatasi maksimal 1920px agar tetap rapi di layar besar.
        - Modal "Beri Nilai" ditaruh di blok .nilai-modals (selalu di DOM) dan dipanggil via event Alpine,
          sehingga bisa dibuka baik dari tabel (laptop), kartu (HP), maupun dari modal detail (HP).
    --}}
    <div class="py-6 md:py-10 bg-slate-50 min-h-screen">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 space-y-6">
            {{-- ===== ALERT ===== --}}
            @if (session('success'))
                <div class="rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ===== REKAP ===== --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['total'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Dinilai (Lengkap)</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['sudah_dinilai'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Belum Dinilai</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['belum_dinilai'] ?? 0 }}</p>
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- ==========  TAMPILAN LAPTOP / PC (TABEL, >=1024px)  ========= --}}
            {{-- ============================================================= --}}
            <div class="nilai-desktop overflow-hidden rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full text-left text-sm table-auto">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                            <th class="px-4 py-3 font-bold">Nama Siswa</th>
                            <th class="px-4 py-3 font-bold">Status PKL</th>
                            <th class="px-4 py-3 text-center font-bold">Rata-rata</th>
                            <th class="px-4 py-3 text-center font-bold">Status Penilaian</th>
                            <th class="px-4 py-3 text-center font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($siswa as $index => $s)
                            @php
                                $daftarSkor = [
                                    optional($s->nilai)->skor_soft_skill,
                                    optional($s->nilai)->skor_hard_skill,
                                    optional($s->nilai)->skor_pengembangan,
                                    optional($s->nilai)->skor_kewirausahaan,
                                    optional($s->nilai)->skor_laporan,
                                    optional($s->nilai)->skor_presentasi,
                                ];
                                $nilaiLengkap = $s->nilai && ! in_array(null, $daftarSkor, true);
                            @endphp
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black">{{ $siswa->firstItem() + $index }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-black break-words">{{ $s->name ?? '' }}</div>
                                    <div class="text-xs text-[#5b616e]">{{ $s->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @if(($s->status_pkl ?? '') === 'aktif')
                                        <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-2.5 py-1 text-xs font-bold text-[#05b169]">Aktif PKL</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-[#5b616e]/10 px-2.5 py-1 text-xs font-bold text-[#5b616e]">Selesai/Belum</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($nilaiLengkap)
                                        <span class="font-bold text-black">{{ $s->nilai->nilai_akhir }}</span>
                                    @else
                                        <span class="text-[#5b616e] italic">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($nilaiLengkap)
                                        <span class="inline-flex items-center rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6]">Lengkap</span>
                                    @elseif($s->nilai)
                                        <span class="inline-flex items-center rounded-full bg-[#d98200]/10 px-2.5 py-1 text-xs font-bold text-[#d98200]">Belum Lengkap</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-[#5b616e]/10 px-2.5 py-1 text-xs font-bold text-[#5b616e]">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div x-data class="flex flex-wrap items-center justify-center gap-2">
                                        {{-- Template kosong (cetak) --}}
                                        <a href="{{ route('cetak.nilai.template', $s->id) }}" target="_blank" title="Cetak Template Kosong untuk Instruktur"
                                           class="inline-flex items-center gap-1.5 rounded-lg border-2 border-[#5b616e]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#5b616e] transition hover:bg-[#5b616e]/5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                            </svg>
                                            Template Kosong
                                        </a>
                                        {{-- Beri nilai (buka modal via event) --}}
                                        <button type="button" @click="$dispatch('open-nilai-{{ $s->id }}')"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                            Beri Nilai
                                        </button>
                                        {{-- PDF Guru (cetak MERAH + ikon print) --}}
                                        @if($nilaiLengkap)
                                            <a href="{{ route('cetak.nilai.guru', $s->id) }}" target="_blank" title="Cetak Format Penilaian Guru"
                                               class="inline-flex items-center gap-1.5 rounded-lg bg-[#cf202f] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#a81824]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                                </svg>
                                                PDF Guru
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data siswa PKL yang Anda bimbing / cocok dengan pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============================================================= --}}
            {{-- ============  TAMPILAN HP (KARTU RINGKAS, <1024px)  ========= --}}
            {{-- ============================================================= --}}
            <div class="nilai-mobile space-y-3">
                @forelse ($siswa as $index => $s)
                    @php
                        $daftarSkor = [
                            optional($s->nilai)->skor_soft_skill,
                            optional($s->nilai)->skor_hard_skill,
                            optional($s->nilai)->skor_pengembangan,
                            optional($s->nilai)->skor_kewirausahaan,
                            optional($s->nilai)->skor_laporan,
                            optional($s->nilai)->skor_presentasi,
                        ];
                        $nilaiLengkap = $s->nilai && ! in_array(null, $daftarSkor, true);
                    @endphp
                    <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm"
                         x-data="{ detail: false }"
                         x-effect="document.body.style.overflow = detail ? 'hidden' : ''">
                        {{-- Ringkas: NAMA (kiri) + AKSI (kanan) --}}
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-black truncate">{{ $s->name ?? '' }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-medium text-[#5b616e]">{{ $s->nisn ?? '-' }}</span>
                                    @if($nilaiLengkap)
                                        <span class="inline-block rounded-full bg-[#0047d6]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#0047d6]">Lengkap</span>
                                    @elseif($s->nilai)
                                        <span class="inline-block rounded-full bg-[#d98200]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#d98200]">Belum Lengkap</span>
                                    @else
                                        <span class="inline-block rounded-full bg-[#5b616e]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#5b616e]">Belum Dinilai</span>
                                    @endif
                                </div>
                            </div>
                            {{-- ===== AKSI DI KANAN: Lihat Detail + Beri Nilai ===== --}}
                            <div class="flex flex-shrink-0 flex-col gap-2">
                                <button type="button" @click="detail = true"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Lihat Detail
                                </button>
                                <button type="button" @click="$dispatch('open-nilai-{{ $s->id }}')"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                    Beri Nilai
                                </button>
                            </div>
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
                                    <h3 class="text-base font-bold text-black">Detail Penilaian</h3>
                                    <button type="button" @click="detail = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                </div>
                                <div class="space-y-4 px-5 py-4">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="col-span-2">
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Nama Siswa</p>
                                            <p class="text-sm font-bold text-black">{{ $s->name ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">NISN</p>
                                            <p class="text-sm font-medium text-black">{{ $s->nisn ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Status PKL</p>
                                            @if(($s->status_pkl ?? '') === 'aktif')
                                                <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-2.5 py-1 text-xs font-bold text-[#05b169]">Aktif PKL</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-[#5b616e]/10 px-2.5 py-1 text-xs font-bold text-[#5b616e]">Selesai/Belum</span>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Rata-rata</p>
                                            @if($nilaiLengkap)
                                                <p class="text-sm font-bold text-black">{{ $s->nilai->nilai_akhir }}</p>
                                            @else
                                                <p class="text-sm text-[#5b616e] italic">-</p>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Status Penilaian</p>
                                            @if($nilaiLengkap)
                                                <span class="inline-flex items-center rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6]">Lengkap</span>
                                            @elseif($s->nilai)
                                                <span class="inline-flex items-center rounded-full bg-[#d98200]/10 px-2.5 py-1 text-xs font-bold text-[#d98200]">Belum Lengkap</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-[#5b616e]/10 px-2.5 py-1 text-xs font-bold text-[#5b616e]">Belum Dinilai</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="sticky bottom-0 space-y-2 border-t-2 border-[#0047d6]/15 bg-white px-5 py-4">
                                    {{-- Beri nilai dari dalam modal detail: tutup detail dulu, lalu buka modal nilai --}}
                                    <button type="button" @click="detail = false; $dispatch('open-nilai-{{ $s->id }}')"
                                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                        Beri Nilai
                                    </button>
                                    <div class="flex gap-2">
                                        {{-- Template kosong (cetak) --}}
                                        <a href="{{ route('cetak.nilai.template', $s->id) }}" target="_blank"
                                           class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border-2 border-[#5b616e]/25 bg-white px-3 py-2.5 text-xs font-bold text-[#5b616e] transition hover:bg-[#5b616e]/5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                            </svg>
                                            Template Kosong
                                        </a>
                                        {{-- PDF Guru (cetak MERAH + ikon print) --}}
                                        @if($nilaiLengkap)
                                            <a href="{{ route('cetak.nilai.guru', $s->id) }}" target="_blank"
                                               class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-[#cf202f] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#a81824]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                                </svg>
                                                PDF Guru
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white px-4 py-8 text-center font-medium text-[#5b616e] italic">
                        Tidak ada data siswa PKL yang Anda bimbing / cocok dengan pencarian.
                    </div>
                @endforelse
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div class="mt-4">
                {!! $siswa->withQueryString()->links() !!}
            </div>
        </div>
    </div>

    {{-- ============================================================= --}}
    {{-- ====  MODAL "BERI NILAI" (selalu di DOM, di luar toggle)  ==== --}}
    {{-- ============================================================= --}}
    <div class="nilai-modals">
        @foreach ($siswa as $s)
            <div x-data="{ open: false }" x-show="open" x-cloak
                 @open-nilai-{{ $s->id }}.window="open = true"
                 @keydown.escape.window="open = false"
                 x-effect="document.body.style.overflow = open ? 'hidden' : ''"
                 class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-0">
                <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl text-left overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                        <h3 class="text-lg font-bold text-black">Penilaian PKL: {{ $s->name ?? '' }}</h3>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="px-6 py-4 overflow-y-auto text-left">
                        <form action="{{ route('guru.nilai.store') }}" method="POST" id="form-nilai-{{ $s->id }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $s->id }}">
                            <input type="hidden" name="guru_id" value="{{ auth()->id() }}">
                            <div class="space-y-6">
                                {{-- ===== UPLOAD FOTO LEMBAR INSTRUKTUR ===== --}}
                                <div class="p-4 bg-[#0047d6]/5 rounded-lg border border-[#0047d6]/20">
                                    <label class="block text-sm font-bold text-black mb-1">Foto Lembar Penilaian Instruktur</label>
                                    <p class="text-xs text-[#5b616e] mb-2">Unggah foto lembar penilaian yang sudah diisi &amp; diparaf instruktur (JPG/PNG, maks 2 MB).</p>
                                    <input type="file" name="foto_lembar_instruktur" accept="image/*"
                                           class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#0047d6] file:px-4 file:py-2 file:text-white file:font-bold">
                                    @if(optional($s->nilai)->foto_lembar_instruktur)
                                        <p class="text-xs mt-2">
                                            <a href="{{ asset('storage/'.$s->nilai->foto_lembar_instruktur) }}" download target="_blank" class="font-bold text-[#0047d6] underline">Lihat foto yang sudah diunggah</a>
                                            <span class="text-[#5b616e]"> (kosongkan bila tidak ingin mengganti)</span>
                                        </p>
                                    @endif
                                </div>

                                {{-- ===== BAGIAN A: NILAI DARI INSTRUKTUR ===== --}}
                                <h4 class="text-sm font-bold text-[#0047d6] uppercase tracking-wide">A. Nilai dari Instruktur (salin dari lembar instruktur)</h4>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-bold text-black mb-1">1. Internalisasi dan penerapan soft skill (0-100)</label>
                                    <input type="number" name="skor_soft_skill" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_soft_skill ?? '' }}" required>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                    <textarea name="deskripsi_soft_skill" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_soft_skill ?? 'Menunjukkan kemampuan komunikasi, kerja sama tim, disiplin, tanggung jawab, etika kerja, dan kemampuan beradaptasi yang sangat baik dalam lingkungan kerja. Aktif berinisiatif serta mampu menyelesaikan tugas secara mandiri.' }}</textarea>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-bold text-black mb-1">2. Penerapan hard skill (0-100)</label>
                                    <input type="number" name="skor_hard_skill" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_hard_skill ?? '' }}" required>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                    <textarea name="deskripsi_hard_skill" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_hard_skill ?? 'Mampu menerapkan kompetensi keahlian sesuai bidang PKL dengan sangat baik, teliti, dan mandiri sesuai standar kerja industri.' }}</textarea>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-bold text-black mb-1">3. Peningkatan dan pengembangan hard skill (0-100)</label>
                                    <input type="number" name="skor_pengembangan" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_pengembangan ?? '' }}" required>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                    <textarea name="deskripsi_pengembangan" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_pengembangan ?? 'Menunjukkan perkembangan kompetensi yang sangat signifikan, cepat memahami keterampilan baru, serta mampu meningkatkan kualitas kerja secara mandiri.' }}</textarea>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-bold text-black mb-1">4. Penyiapan dan kemandirian kewirausahaan (0-100)</label>
                                    <input type="number" name="skor_kewirausahaan" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_kewirausahaan ?? '' }}" required>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                    <textarea name="deskripsi_kewirausahaan" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_kewirausahaan ?? 'Menunjukkan sikap mandiri dan tanggung jawab yang sangat baik serta mulai memahami peluang dan budaya kerja kewirausahaan.' }}</textarea>
                                </div>

                                {{-- ===== BAGIAN B: NILAI DARI GURU ===== --}}
                                <h4 class="text-sm font-bold text-[#05b169] uppercase tracking-wide">B. Nilai dari Guru Pembimbing</h4>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-bold text-black mb-1">5. Penulisan laporan (0-100)</label>
                                    <input type="number" name="skor_laporan" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_laporan ?? '' }}" required>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                    <textarea name="deskripsi_laporan" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_laporan ?? 'Penulisan laporan sangat rapi dan sistematis sesuai dengan pedoman penulisan laporan PKL. Tata bahasa yang digunakan baku dan mudah dipahami.' }}</textarea>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <label class="block text-sm font-bold text-black mb-1">6. Pemaparan presentasi (0-100)</label>
                                    <input type="number" name="skor_presentasi" min="0" max="100" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm mb-2" value="{{ optional($s->nilai)->skor_presentasi ?? '' }}" required>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                                    <textarea name="deskripsi_presentasi" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#0047d6] sm:text-sm" required>{{ optional($s->nilai)->deskripsi_presentasi ?? 'Mampu memaparkan hasil PKL dengan percaya diri, sistematis, dan komunikatif serta menjawab pertanyaan dengan baik saat presentasi.' }}</textarea>
                                </div>
                                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <label class="block text-sm font-bold text-blue-900 mb-1">Catatan Akhir Penilaian</label>
                                    <textarea name="catatan_guru" rows="4" class="block w-full rounded-lg border-blue-300 shadow-sm focus:ring-[#0047d6] sm:text-sm">{{ optional($s->nilai)->catatan_guru ?? 'SANGAT BAIK. Terus pertahankan dan tingkatkan kemampuan Softskill dan Hardskill secara konsisten terutama pada pengetahuan dan keterampilan yang baru sehingga dapat bersaing di wirausaha maupun dunia industri.' }}</textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 shrink-0">
                        <button @click="open = false" type="button"
                                class="rounded-xl px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-4 focus:ring-gray-100">
                            Batal
                        </button>
                        <button type="submit" form="form-nilai-{{ $s->id }}"
                                class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>

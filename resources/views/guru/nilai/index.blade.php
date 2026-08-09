<x-app-layout title="Rekap & Penilaian">
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
            {{-- ===== HEADER ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Rekap &amp; Penilaian (Guru Pembimbing)</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Beri nilai siswa bimbingan Anda dan cetak lembar penilaian PKL.</p>
                </div>
                <a href="{{ route('guru.dashboard') }}"
                   class="inline-flex items-center justify-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    Kembali ke Dashboard
                </a>
            </div>

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
            @if ($errors->any())
                <div class="rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                    <p class="mb-1 font-bold">Penilaian gagal disimpan. Periksa kembali:</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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

            {{-- ===== CETAK SEMUA PDF ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-black">Cetak Semua Penilaian</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak penilaian seluruh siswa bimbingan Anda dalam format <span class="font-bold text-black">PDF Guru</span> (1 siswa per halaman). Hanya siswa dengan nilai lengkap yang disertakan.
                    </p>
                </div>

                <a href="{{ route('cetak.nilai.guru.semua') }}" target="_blank"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#cf202f] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#a81824] focus:outline-none focus:ring-4 focus:ring-[#cf202f]/30 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                    </svg>
                    Cetak Semua PDF
                </a>
            </div>

            {{-- ============================================================= --}}
            {{-- ==========  TAMPILAN LAPTOP / PC (TABEL, >=1024px)  ========= --}}
            {{-- ============================================================= --}}
            <div class="nilai-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full min-w-[64rem] text-left text-sm table-auto">
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
        @php
            // Tanda tangan tersimpan milik guru yang sedang login. Diunggah
            // sekali lewat tombol "Tanda Tangan Saya" di halaman Monitoring
            // Absensi, lalu tinggal dipilih di sini tanpa mengunggah ulang.
            $guruAktif        = auth()->user();
            $ttdGuruTersimpan = $guruAktif && $guruAktif->punyaTtdTersimpan()
                ? $guruAktif->ttdTersimpanUrl()
                : null;
        @endphp
        @foreach ($siswa as $s)
            @php
                $ttdNilaiPembimbing = optional($s->nilai)->ttd_pembimbing;
                $ttdNilaiInstruktur = optional($s->nilai)->ttd_instruktur;
                $adaFotoLama        = (bool) optional($s->nilai)->foto_lembar_instruktur;

                // Pilihan bawaan tanda tangan pembimbing, berurutan:
                // pakai yang sudah menempel -> pakai tanda tangan tersimpan -> unggah baru.
                $sumberTtdAwal = $ttdNilaiPembimbing
                    ? 'tetap'
                    : ($ttdGuruTersimpan ? 'tersimpan' : 'unggah');

                // Pop up dibuka lagi otomatis bila penyimpanan barusan gagal,
                // supaya guru langsung melihat peringatannya di dalam form.
                $modalGagal = $errors->any() && (string) old('user_id') === (string) $s->id;
            @endphp
            <div x-data="{
                     open: {{ $modalGagal ? 'true' : 'false' }},
                     fotoLama: {{ $adaFotoLama ? 'true' : 'false' }},
                     fotoBaru: false,
                     namaFoto: '',
                     ttdSumber: @js(old('sumber_ttd_pembimbing', $sumberTtdAwal)),
                     ttdPratinjau: null,
                     get fotoOk() { return this.fotoLama || this.fotoBaru; }
                 }" x-show="open" x-cloak
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
                                {{-- ===== PERINGATAN GAGAL SIMPAN (tampil di dalam pop up) ===== --}}
                                @if($modalGagal)
                                    <div class="rounded-lg border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3">
                                        <p class="text-sm font-bold text-[#cf202f]">Penilaian belum tersimpan. Periksa kembali:</p>
                                        <ul class="mt-1 list-disc space-y-0.5 pl-5 text-xs font-semibold text-black">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- ===== IDENTITAS UNTUK HASIL CETAK (NIS / NISN) ===== --}}
                                @php
                                    $labelIdentitas = optional($s->nilai)->label_identitas === 'nis' ? 'nis' : 'nisn';
                                    $nomorIdentitas = optional($s->nilai)->nomor_identitas;
                                @endphp
                                <div x-data="{ label: '{{ $labelIdentitas }}', nomor: @js($nomorIdentitas ?? ''), nisnSiswa: @js($s->nisn ?? '') }"
                                     class="p-4 bg-[#05b169]/5 rounded-lg border border-[#05b169]/25">
                                    <label class="block text-sm font-bold text-black mb-1">Identitas pada Hasil Cetak</label>
                                    <p class="text-xs text-[#5b616e] mb-3">
                                        Pilih kata yang tercetak pada lembar penilaian: <span class="font-bold text-black">NISN</span> atau <span class="font-bold text-black">NIS</span>, lalu isi nomornya sesuai yang ingin dipakai.
                                    </p>

                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="label_identitas" value="nisn" x-model="label" class="peer sr-only">
                                            <span class="inline-flex items-center rounded-lg border-2 border-gray-200 bg-white px-4 py-2 text-xs font-bold text-[#5b616e] transition peer-checked:border-[#05b169] peer-checked:bg-[#05b169] peer-checked:text-white">
                                                Pakai NISN
                                            </span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="label_identitas" value="nis" x-model="label" class="peer sr-only">
                                            <span class="inline-flex items-center rounded-lg border-2 border-gray-200 bg-white px-4 py-2 text-xs font-bold text-[#5b616e] transition peer-checked:border-[#05b169] peer-checked:bg-[#05b169] peer-checked:text-white">
                                                Pakai NIS
                                            </span>
                                        </label>
                                    </div>

                                    <label class="block text-xs font-medium text-gray-600 mb-1">
                                        Nomor <span class="font-bold text-black" x-text="label === 'nis' ? 'NIS' : 'NISN'">NISN</span> yang dicetak
                                    </label>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <input type="text" name="nomor_identitas" x-model="nomor" maxlength="30" inputmode="numeric"
                                               :placeholder="label === 'nis' ? 'Ketik NIS siswa, mis. 12345' : (nisnSiswa || 'Ketik NISN siswa')"
                                               class="block w-full sm:w-64 rounded-lg border-gray-300 shadow-sm focus:ring-[#05b169] sm:text-sm">
                                        <button type="button" x-show="nisnSiswa" @click="label = 'nisn'; nomor = nisnSiswa"
                                                class="rounded-lg bg-white border border-[#05b169]/40 px-3 py-2 text-[11px] font-bold text-[#05b169] hover:bg-[#05b169]/10">
                                            Pakai NIS data siswa (<span x-text="nisnSiswa"></span>)
                                        </button>
                                        <button type="button" @click="nomor = ''"
                                                class="rounded-lg bg-white border border-gray-300 px-3 py-2 text-[11px] font-bold text-[#5b616e] hover:bg-gray-100">
                                            Kosongkan
                                        </button>
                                    </div>

                                    <p class="text-[11px] text-[#5b616e] mt-2">
                                        Pratinjau baris cetak:
                                        <span class="font-bold text-black" x-text="(label === 'nis' ? 'NIS' : 'NISN') + ' : ' + (nomor.trim() !== '' ? nomor.trim() : (label === 'nis' ? '-' : (nisnSiswa || '-')))"></span>
                                    </p>
                                    <p class="text-[11px] text-[#5b616e]">
                                        Bila nomor dikosongkan dan pilihannya NISN, cetakan otomatis memakai NISN dari data siswa.
                                    </p>
                                </div>

                                {{-- ===== UPLOAD FOTO LEMBAR INSTRUKTUR (WAJIB) ===== --}}
                                <div class="p-4 rounded-lg border-2 transition"
                                     :class="fotoOk ? 'bg-[#0047d6]/5 border-[#0047d6]/20' : 'bg-[#cf202f]/5 border-[#cf202f]'">
                                    <label class="block text-sm font-bold text-black mb-1">
                                        Foto Lembar Penilaian Instruktur <span class="text-[#cf202f]">*</span>
                                    </label>
                                    <p class="text-xs text-[#5b616e] mb-2">Unggah foto lembar penilaian yang sudah diisi &amp; diparaf instruktur (JPG/PNG, maks 3 MB).</p>

                                    {{-- Peringatan wajib, muncul langsung di dalam pop up --}}
                                    <div x-show="!fotoOk" x-cloak
                                         class="mb-2 flex items-start gap-2 rounded-lg border border-[#cf202f]/40 bg-white px-3 py-2">
                                        <span class="mt-0.5 flex h-4 w-4 flex-none items-center justify-center rounded-full bg-[#cf202f] text-[10px] font-bold leading-none text-white">!</span>
                                        <p class="text-xs font-bold text-[#cf202f]">
                                            Wajib unggah lembar penilaian instruktur. Penilaian tidak bisa disimpan sebelum foto ini dipilih.
                                        </p>
                                    </div>

                                    <input type="file" name="foto_lembar_instruktur" accept="image/*"
                                           @change="fotoBaru = $event.target.files.length > 0; namaFoto = $event.target.files.length ? $event.target.files[0].name : ''"
                                           class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#0047d6] file:px-4 file:py-2 file:text-white file:font-bold">

                                    {{-- Tanpa pratinjau gambar. Cukup keterangan singkat bahwa berkas sudah terpilih,
                                         supaya pop up tidak jadi panjang dan tidak perlu ikut memuat gambarnya. --}}
                                    <p x-show="namaFoto" x-cloak class="mt-2 flex items-start gap-1.5 text-xs font-bold text-[#05b169]">
                                        <span class="flex-none">&check;</span>
                                        <span class="min-w-0 break-all" x-text="'Foto terpilih: ' + namaFoto"></span>
                                    </p>

                                    @if(optional($s->nilai)->foto_lembar_instruktur)
                                        <p class="text-xs mt-2">
                                            <a href="{{ asset('storage/'.$s->nilai->foto_lembar_instruktur) }}" download target="_blank" class="font-bold text-[#0047d6] underline">Lihat foto yang sudah diunggah</a>
                                            <span class="text-[#5b616e]"> (kosongkan bila tidak ingin mengganti)</span>
                                        </p>
                                    @endif
                                </div>

                                {{-- ===== TANDA TANGAN UNTUK HASIL CETAK PDF ===== --}}
                                <div class="p-4 bg-[#05b169]/5 rounded-lg border border-[#05b169]/25">
                                    <label class="block text-sm font-bold text-black mb-1">Tanda Tangan untuk Hasil Cetak</label>
                                    <p class="text-xs text-[#5b616e] mb-3">
                                        Unggah foto tanda tangannya saja, bukan seluruh lembar. Gambar otomatis dipasang
                                        di kolom tanda tangan ketika penilaian dicetak ke PDF. Latar putih di sekelilingnya
                                        dipangkas sendiri oleh sistem, jadi ukuran cetaknya selalu pas dan seragam.
                                        Opsional &mdash; boleh dikosongkan (JPG/PNG, maks 3 MB).
                                    </p>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                        {{-- ---------- GURU PEMBIMBING (kolom KIRI saat dicetak) ---------- --}}
                                        <div class="rounded-lg border border-gray-200 bg-white p-3">
                                            <p class="text-xs font-bold text-black">Guru Pembimbing</p>
                                            <p class="text-[11px] text-[#5b616e] mb-2">Tercetak di kolom kiri</p>

                                            {{-- Kotak pratinjau mengikuti pilihan di bawahnya --}}
                                            <div class="mb-2 flex h-20 items-center justify-center overflow-hidden rounded-md border border-dashed border-gray-300 bg-gray-50 p-1">
                                                <img x-show="ttdSumber === 'unggah' &amp;&amp; ttdPratinjau" x-cloak :src="ttdPratinjau"
                                                     alt="Pratinjau tanda tangan baru" class="max-h-full max-w-full object-contain">

                                                @if($ttdGuruTersimpan)
                                                    <img x-show="ttdSumber === 'tersimpan'" x-cloak src="{{ $ttdGuruTersimpan }}"
                                                         alt="Tanda tangan tersimpan" class="max-h-full max-w-full object-contain">
                                                @endif

                                                @if($ttdNilaiPembimbing)
                                                    <img x-show="ttdSumber === 'tetap'" x-cloak src="{{ asset('storage/'.$ttdNilaiPembimbing) }}"
                                                         alt="Tanda tangan pada penilaian ini" class="max-h-full max-w-full object-contain">
                                                    <span x-show="ttdSumber === 'hapus'" x-cloak class="text-[11px] font-bold text-[#cf202f]">Akan dikosongkan</span>
                                                @endif

                                                <span x-show="ttdSumber === 'unggah' &amp;&amp; !ttdPratinjau" x-cloak class="text-[11px] text-gray-400">Belum ada berkas dipilih</span>
                                            </div>

                                            {{-- Pilihan sumber tanda tangan --}}
                                            <div class="space-y-1.5">
                                                @if($ttdNilaiPembimbing)
                                                    <label class="flex cursor-pointer items-start gap-2 text-[11px] font-medium text-[#5b616e]">
                                                        <input type="radio" name="sumber_ttd_pembimbing" value="tetap" x-model="ttdSumber"
                                                               class="mt-0.5 border-gray-300 text-[#05b169] focus:ring-[#05b169]">
                                                        <span>Pakai yang sudah ada di penilaian ini</span>
                                                    </label>
                                                @endif

                                                @if($ttdGuruTersimpan)
                                                    <label class="flex cursor-pointer items-start gap-2 text-[11px] font-medium text-[#5b616e]">
                                                        <input type="radio" name="sumber_ttd_pembimbing" value="tersimpan" x-model="ttdSumber"
                                                               class="mt-0.5 border-gray-300 text-[#05b169] focus:ring-[#05b169]">
                                                        <span><span class="font-bold text-[#05b169]">Pakai tanda tangan tersimpan</span> &mdash; tidak perlu unggah ulang</span>
                                                    </label>
                                                @else
                                                    <p class="rounded-md bg-[#fff8e6] px-2 py-1.5 text-[11px] font-medium text-[#8a6100]">
                                                        Belum punya tanda tangan tersimpan. Unggah sekali lewat tombol
                                                        <span class="font-bold">Tanda Tangan Saya</span> di halaman Monitoring Absensi
                                                        supaya bisa langsung dipilih di sini.
                                                    </p>
                                                @endif

                                                <label class="flex cursor-pointer items-start gap-2 text-[11px] font-medium text-[#5b616e]">
                                                    <input type="radio" name="sumber_ttd_pembimbing" value="unggah" x-model="ttdSumber"
                                                           class="mt-0.5 border-gray-300 text-[#05b169] focus:ring-[#05b169]">
                                                    <span>Unggah tanda tangan baru</span>
                                                </label>

                                                @if($ttdNilaiPembimbing)
                                                    <label class="flex cursor-pointer items-start gap-2 text-[11px] font-medium text-[#cf202f]">
                                                        <input type="radio" name="sumber_ttd_pembimbing" value="hapus" x-model="ttdSumber"
                                                               class="mt-0.5 border-gray-300 text-[#cf202f] focus:ring-[#cf202f]">
                                                        <span>Hapus tanda tangan dari penilaian ini</span>
                                                    </label>
                                                @endif
                                            </div>

                                            {{-- Berkas hanya diperlukan saat memilih "unggah baru" --}}
                                            <div x-show="ttdSumber === 'unggah'" x-cloak class="mt-2">
                                                <input type="file" name="ttd_pembimbing" accept="image/png,image/jpeg"
                                                       @change="ttdPratinjau = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null"
                                                       class="block w-full text-xs text-gray-700 file:mr-2 file:rounded-md file:border-0 file:bg-[#05b169] file:px-3 file:py-1.5 file:font-bold file:text-white">
                                            </div>
                                        </div>

                                        {{-- ---------- PEMBIMBING DUNIA KERJA (kolom KANAN saat dicetak) ---------- --}}
                                        <div x-data="{ pratinjau: null }" class="rounded-lg border border-gray-200 bg-white p-3">
                                            <p class="text-xs font-bold text-black">Pembimbing Dunia Kerja</p>
                                            <p class="text-[11px] text-[#5b616e] mb-2">Instruktur industri, kolom kanan</p>

                                            <div class="mb-2 flex h-20 items-center justify-center overflow-hidden rounded-md border border-dashed border-gray-300 bg-gray-50 p-1">
                                                <img x-show="pratinjau" x-cloak :src="pratinjau" alt="Pratinjau tanda tangan" class="max-h-full max-w-full object-contain">
                                                @if($ttdNilaiInstruktur)
                                                    <img x-show="!pratinjau" src="{{ asset('storage/'.$ttdNilaiInstruktur) }}" alt="Tanda tangan tersimpan" class="max-h-full max-w-full object-contain">
                                                @else
                                                    <span x-show="!pratinjau" class="text-[11px] text-gray-400">Belum ada tanda tangan</span>
                                                @endif
                                            </div>

                                            <input type="file" name="ttd_instruktur" accept="image/png,image/jpeg"
                                                   @change="pratinjau = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null"
                                                   class="block w-full text-xs text-gray-700 file:mr-2 file:rounded-md file:border-0 file:bg-[#05b169] file:px-3 file:py-1.5 file:font-bold file:text-white">

                                            @if($ttdNilaiInstruktur)
                                                <label class="mt-2 flex items-center gap-2 text-[11px] font-medium text-[#cf202f]">
                                                    <input type="checkbox" name="hapus_ttd_instruktur" value="1"
                                                           class="rounded border-gray-300 text-[#cf202f] focus:ring-[#cf202f]">
                                                    Hapus tanda tangan ini
                                                </label>
                                            @endif
                                        </div>
                                    </div>
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
                        <button type="submit" form="form-nilai-{{ $s->id }}" :disabled="!fotoOk"
                                :class="!fotoOk ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#0038aa]'"
                                :title="!fotoOk ? 'Unggah dulu foto lembar penilaian instruktur' : ''"
                                class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>

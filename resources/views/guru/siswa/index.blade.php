<x-app-layout title="Daftar Siswa Bimbingan">
    <style>
        [x-cloak]{display:none!important;}
        /* ===== Pergantian tampilan berbasis lebar layar (sama seperti Jurnal Siswa) ===== */
        .mon-desktop{ display:none; }   /* default: HP -> tabel disembunyikan */
        .mon-mobile { display:block; }  /* default: HP -> kartu tampil */
        @media (min-width:1024px){      /* laptop & PC (>=1024px) */
            .mon-desktop{ display:block; }  /* tabel tampil */
            .mon-mobile { display:none; }   /* kartu disembunyikan */
        }
    </style>

    <div class="py-8 md:py-12 bg-white min-h-screen">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12 space-y-6">

            {{-- ===== HEADER ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Daftar Siswa Bimbingan</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Kelola dan pantau siswa PKL yang Anda bimbing.</p>
                </div>
                <a href="{{ route('guru.dashboard') }}"
                   class="inline-flex items-center justify-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    Kembali ke Dashboard
                </a>
            </div>

            {{-- ===== CARD REKAP INFORMASI ===== --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Siswa Aktif</p>
                    <p class="mt-2 text-4xl font-bold text-[#05b169]">{{ $rekap['aktif'] }}</p>
                    <p class="mt-1 text-sm font-medium text-[#5b616e]">Sedang menjalani PKL.</p>
                </div>
            </div>

            {{-- ===== CARD UTAMA (Filter + Data) ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">
                <h3 class="text-lg font-bold text-black mb-6">Siswa PKL Anda</h3>

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('guru.siswa.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                Cari (Nama / NISN / Kelas / Jurusan / Instruktur)
                            </label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik kata kunci..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                                Cari
                            </button>
                            <a href="{{ route('guru.siswa.index') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ============================================================= --}}
                {{-- ==========  TAMPILAN LAPTOP / PC (TABEL, >=1024px)  ========= --}}
                {{-- ============================================================= --}}
                <div class="mon-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-3 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-3 py-3 font-bold">Nama Siswa</th>
                                <th class="px-3 py-3 font-bold">NISN</th>
                                <th class="px-3 py-3 font-bold">Kelas</th>
                                <th class="px-3 py-3 font-bold">Jurusan</th>
                                <th class="px-3 py-3 font-bold">Nama Instruktur</th>
                                <th class="px-3 py-3 font-bold">Tempat Industri</th>
                                <th class="px-3 py-3 text-center font-bold">Status</th>
                                <th class="px-3 py-3 text-center font-bold">Aksi Monitoring</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse($siswas as $siswa)
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-3 py-3 text-center font-semibold text-black">{{ $siswas->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-3 font-bold text-black break-words">{{ $siswa->name }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap font-medium text-black">{{ $siswa->nisn ?? '-' }}</td>
                                    <td class="px-3 py-3 font-medium text-black">{{ $siswa->kelas ?? '-' }}</td>
                                    <td class="px-3 py-3 font-medium text-black">{{ $siswa->jurusan ?? '-' }}</td>
                                    <td class="px-3 py-3 font-medium text-black">{{ optional($siswa->instruktur)->name ?? '-' }}</td>
                                    <td class="px-3 py-3 font-medium text-black">{{ optional($siswa->perusahaan)->nama_perusahaan ?? '-' }}</td>
                                    <td class="px-3 py-3 text-center">
                                        @php $sp = $siswa->status_pkl ?? 'belum'; @endphp
                                        @if($sp === 'aktif')
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Aktif</span>
                                        @elseif($sp === 'selesai')
                                            <span class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1 text-xs font-bold text-white">Selesai</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Belum</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('guru.catatan.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Catatan</a>
                                            <a href="{{ route('guru.observasi.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Observasi</a>
                                            <a href="{{ route('guru.nilai.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Rekap Nilai</a>
                                            <a href="{{ route('guru.monitoring.jurnal', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Jurnal</a>
                                            <a href="{{ route('guru.monitoring.absensi', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Absensi</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">
                                        Tidak ada siswa yang cocok dengan pencarian / belum ada siswa bimbingan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ============================================================= --}}
                {{-- ============  TAMPILAN HP (KARTU RINGKAS, <1024px)  ========= --}}
                {{-- ============================================================= --}}
                <div class="mon-mobile space-y-3">
                    @forelse($siswas as $siswa)
                        @php $sp = $siswa->status_pkl ?? 'belum'; @endphp
                        <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm"
                             x-data="{ detail: false }"
                             x-effect="document.body.style.overflow = detail ? 'hidden' : ''">
                            {{-- Ringkas: NAMA + status (kiri) + LIHAT DETAIL (kanan) --}}
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-black truncate">{{ $siswa->name }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        @if($sp === 'aktif')
                                            <span class="inline-block rounded-full bg-[#05b169]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#05b169]">Aktif</span>
                                        @elseif($sp === 'selesai')
                                            <span class="inline-block rounded-full bg-[#0047d6]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#0047d6]">Selesai</span>
                                        @else
                                            <span class="inline-block rounded-full bg-[#d98200]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#d98200]">Belum</span>
                                        @endif
                                        <span class="text-xs font-medium text-[#5b616e]">{{ $siswa->nisn ?? '-' }}</span>
                                    </div>
                                </div>
                                <button type="button" @click="detail = true"
                                        class="inline-flex flex-shrink-0 items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Lihat Detail
                                </button>
                            </div>

                            {{-- ===== POP-UP DETAIL (animasi smooth) ===== --}}
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
                                        <h3 class="text-base font-bold text-black">Detail Siswa</h3>
                                        <button type="button" @click="detail = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                    </div>
                                    <div class="space-y-4 px-5 py-4">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Nama Siswa</p>
                                            <p class="text-sm font-bold text-black break-words">{{ $siswa->name }}</p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">NISN</p>
                                                <p class="text-sm font-bold text-black">{{ $siswa->nisn ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Status</p>
                                                @if($sp === 'aktif')
                                                    <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-2.5 py-1 text-xs font-bold text-[#05b169]">Aktif</span>
                                                @elseif($sp === 'selesai')
                                                    <span class="inline-flex items-center rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6]">Selesai</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-[#d98200]/10 px-2.5 py-1 text-xs font-bold text-[#d98200]">Belum</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Kelas</p>
                                                <p class="text-sm font-bold text-black">{{ $siswa->kelas ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Jurusan</p>
                                                <p class="text-sm font-bold text-black">{{ $siswa->jurusan ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Nama Instruktur</p>
                                            <p class="text-sm font-bold text-black break-words">{{ optional($siswa->instruktur)->name ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Tempat Industri</p>
                                            <p class="text-sm font-bold text-black break-words">{{ optional($siswa->perusahaan)->nama_perusahaan ?? '-' }}</p>
                                        </div>
                                    </div>
                                    {{-- Footer aksi monitoring --}}
                                    <div class="sticky bottom-0 space-y-2 border-t-2 border-[#0047d6]/15 bg-white px-5 py-4">
                                        <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Aksi Monitoring</p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('guru.catatan.index', ['q' => $siswa->nisn]) }}"
                                               class="flex items-center justify-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Catatan</a>
                                            <a href="{{ route('guru.observasi.index', ['q' => $siswa->nisn]) }}"
                                               class="flex items-center justify-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Observasi</a>
                                            <a href="{{ route('guru.nilai.index', ['q' => $siswa->nisn]) }}"
                                               class="flex items-center justify-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Rekap Nilai</a>
                                            <a href="{{ route('guru.monitoring.jurnal', ['q' => $siswa->nisn]) }}"
                                               class="flex items-center justify-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Jurnal</a>
                                            <a href="{{ route('guru.monitoring.absensi', ['q' => $siswa->nisn]) }}"
                                               class="col-span-2 flex items-center justify-center rounded-xl bg-[#0047d6] px-3 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Absensi</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white px-4 py-8 text-center font-medium text-[#5b616e] italic">
                            Tidak ada siswa yang cocok dengan pencarian / belum ada siswa bimbingan.
                        </div>
                    @endforelse
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $siswas->withQueryString()->links() !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout title="Monitoring Absensi">
    <style>
        [x-cloak]{display:none!important;}

        /* ===== Pergantian tampilan berbasis lebar layar (tanpa bergantung Tailwind lg:) ===== */
        .absensi-desktop{ display:none; }   /* default: HP -> tabel disembunyikan */
        .absensi-mobile { display:block; }  /* default: HP -> kartu tampil */

        @media (min-width:1024px){          /* laptop & PC (>=1024px) */
            .absensi-desktop{ display:block; }  /* tabel tampil */
            .absensi-mobile { display:none; }   /* kartu disembunyikan */
        }
    </style>

    {{--
        Responsif OTOMATIS (sama seperti halaman Jurnal Guru):
        - >=1024px (laptop & PC): .absensi-desktop tampil (tabel penuh), kartu disembunyikan.
        - <1024px (HP & tablet kecil): .absensi-mobile tampil (kartu ringkas: Nama + Lihat Detail), tabel disembunyikan.
    --}}
    <div class="py-6 md:py-10 bg-slate-50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 space-y-6">

            {{-- ===== HEADER ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Monitoring &amp; Validasi Absensi</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Validasi bukti fisik kehadiran siswa bimbingan Anda.</p>
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

            {{-- ===== REKAP ===== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Hadir</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['Hadir'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/30 bg-[#0047d6]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Izin</p>
                    <p class="mt-1 text-3xl font-bold text-[#0047d6]">{{ $rekap['Izin'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sakit</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['Sakit'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#cf202f]/30 bg-[#cf202f]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Alpha</p>
                    <p class="mt-1 text-3xl font-bold text-[#cf202f]">{{ $rekap['Alpha'] ?? 0 }}</p>
                </div>
            </div>

            {{-- ===== CARD KHUSUS: CETAK SEMUA PDF (di atas filter) ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-black">Rekap Absensi Siswa Bimbingan</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak rekap absensi seluruh siswa bimbingan Anda (1 siswa per halaman).
                    </p>
                </div>

                <a href="{{ route('cetak.absensi.semua') }}" target="_blank" rel="noopener"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#cf202f] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#a81824] focus:outline-none focus:ring-4 focus:ring-[#cf202f]/30 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                    </svg>
                    Cetak Semua PDF
                </a>
            </div>

            {{-- ===== FILTER ===== --}}
            <form method="GET" action="{{ route('guru.monitoring.absensi') }}"
                  class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 flex flex-wrap gap-3 items-end shadow-sm">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                    <select name="status" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua</option>
                        <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                        <option value="Izin" @selected(request('status') === 'Izin')>Izin</option>
                        <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                        <option value="Alpha" @selected(request('status') === 'Alpha')>Alpha</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                           class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                <a href="{{ route('guru.monitoring.absensi') }}"
                   class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
            </form>

            {{-- ============================================================= --}}
            {{-- ==========  TAMPILAN LAPTOP / PC (TABEL, >=1024px)  ========= --}}
            {{-- ============================================================= --}}
            <div class="absensi-desktop overflow-hidden rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full text-sm text-left table-auto">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                            <th class="px-4 py-3 font-bold">Tanggal</th>
                            <th class="px-4 py-3 font-bold">Nama</th>
                            <th class="px-4 py-3 font-bold">NISN</th>
                            <th class="px-4 py-3 text-center font-bold">Status</th>
                            <th class="px-4 py-3 text-center font-bold">Jam Masuk</th>
                            <th class="px-4 py-3 text-center font-bold">Jam Pulang</th>
                            <th class="px-4 py-3 text-center font-bold">Validasi</th>
                            <th class="px-4 py-3 text-center font-bold">Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($absensi as $a)
                            @php
                                $badge = match($a->status) {
                                    'Hadir' => 'bg-[#05b169] text-white',
                                    'Izin'  => 'bg-[#0047d6] text-white',
                                    'Sakit' => 'bg-[#d98200] text-white',
                                    'Alpha' => 'bg-[#cf202f] text-white',
                                    default => 'bg-[#5b616e] text-white',
                                };
                                $sv = $a->status_validasi ?? 'draft';
                                $svBadge = match ($sv) {
                                    'disetujui' => 'bg-[#05b169] text-white',
                                    'diajukan'  => 'bg-[#d98200] text-white',
                                    default     => 'bg-[#5b616e]/15 text-[#5b616e]',
                                };
                                $svLabel = match ($sv) {
                                    'disetujui' => 'Tervalidasi',
                                    'diajukan'  => 'Menunggu Validasi',
                                    default     => 'Belum Diajukan',
                                };
                                $extBukti = $a->foto_bukti ? pathinfo($a->foto_bukti, PATHINFO_EXTENSION) : '';
                            @endphp
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black">{{ $absensi->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 font-bold text-black break-words">{{ $a->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $a->siswa->nisn ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badge }}">{{ $a->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-black">{{ $a->jam_masuk ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-medium text-black">{{ $a->jam_pulang ?? '-' }}</td>

                                {{-- ===== VALIDASI ===== --}}
                                <td class="px-4 py-3">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}">{{ $svLabel }}</span>

                                        @if($a->foto_bukti)
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                <a href="{{ asset('storage/'.$a->foto_bukti) }}" download target="_blank" rel="noopener"
                                                   class="inline-flex items-center gap-1 rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                    Lihat Bukti
                                                </a>
                                                <a href="{{ asset('storage/'.$a->foto_bukti) }}" download
                                                   download="bukti-absensi-{{ $a->siswa->nisn ?? $a->id }}-{{ $a->id . '.' . $extBukti }}"
                                                   class="inline-flex items-center gap-1 rounded-lg border-2 border-[#05b169]/40 bg-white px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/5">
                                                    Download
                                                </a>
                                            </div>
                                        @endif

                                        @if($sv === 'diajukan')
                                            <div x-data="{ openValidasi: false }" class="inline-block">
                                                <button type="button" @click="openValidasi = true"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                    Validasi
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="openValidasi" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                        <div class="absolute inset-0 bg-black/50" @click="openValidasi = false"></div>
                                                        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl text-left">
                                                            <div class="flex items-center justify-between mb-4">
                                                                <h3 class="text-lg font-bold text-black">Validasi Absensi</h3>
                                                                <button type="button" @click="openValidasi = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                                            </div>
                                                            <div class="space-y-2 text-sm text-black mb-4">
                                                                <p><span class="font-bold">Siswa:</span> {{ $a->siswa->name ?? '-' }}</p>
                                                                <p><span class="font-bold">Tanggal:</span> {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</p>
                                                                <p><span class="font-bold">Status:</span> {{ $a->status }}</p>
                                                                @if($a->catatan_instruktur)
                                                                    <p><span class="font-bold">Catatan Instruktur:</span> {{ $a->catatan_instruktur }}</p>
                                                                @endif
                                                            </div>
                                                            <p class="text-xs text-[#5b616e] mb-4">
                                                                Pastikan bukti fisik sudah diperiksa (gunakan tombol Lihat/Download Bukti) sebelum memvalidasi.
                                                            </p>
                                                            <div class="flex justify-end gap-2">
                                                                <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}">
                                                                    @csrf @method('PUT')
                                                                    <input type="hidden" name="aksi" value="tolak">
                                                                    <button type="submit" class="rounded-xl border-2 border-[#cf202f]/40 bg-white px-4 py-2 text-sm font-bold text-[#cf202f] transition hover:bg-[#cf202f]/5">Tolak</button>
                                                                </form>
                                                                <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}">
                                                                    @csrf @method('PUT')
                                                                    <input type="hidden" name="aksi" value="valid">
                                                                    <button type="submit" class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049a5b]">Valid</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        @elseif($sv === 'disetujui')
                                            <span class="inline-flex items-center justify-center rounded-full bg-[#05b169]/10 px-3 py-1.5 text-xs font-bold text-[#05b169]">Tervalidasi</span>
                                        @else
                                            <span class="text-xs font-medium text-[#5b616e]">Belum diajukan</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- ===== CETAK (MERAH + IKON PRINT) ===== --}}
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cetak.absensi', $a->siswa_id) }}" target="_blank" rel="noopener"
                                       title="Cetak PDF absensi siswa ini"
                                       class="inline-flex items-center gap-1.5 rounded-full bg-[#cf202f] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#a81824]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                        </svg>
                                        Cetak
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============================================================= --}}
            {{-- ============  TAMPILAN HP (KARTU RINGKAS, <1024px)  ========= --}}
            {{-- ============================================================= --}}
            <div class="absensi-mobile space-y-3">
                @forelse ($absensi as $a)
                    @php
                        $badge = match($a->status) {
                            'Hadir' => 'bg-[#05b169] text-white',
                            'Izin'  => 'bg-[#0047d6] text-white',
                            'Sakit' => 'bg-[#d98200] text-white',
                            'Alpha' => 'bg-[#cf202f] text-white',
                            default => 'bg-[#5b616e] text-white',
                        };
                        $sv = $a->status_validasi ?? 'draft';
                        $svBadge = match ($sv) {
                            'disetujui' => 'bg-[#05b169] text-white',
                            'diajukan'  => 'bg-[#d98200] text-white',
                            default     => 'bg-[#5b616e]/15 text-[#5b616e]',
                        };
                        $svLabel = match ($sv) {
                            'disetujui' => 'Tervalidasi',
                            'diajukan'  => 'Menunggu Validasi',
                            default     => 'Belum Diajukan',
                        };
                        $extBukti = $a->foto_bukti ? pathinfo($a->foto_bukti, PATHINFO_EXTENSION) : '';
                    @endphp

                   <div x-data="{ detail: false }"
     x-effect="document.body.style.overflow = detail ? 'hidden' : ''"
     class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm">
                        {{-- Ringkas: NAMA + AKSI --}}
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-black truncate">{{ $a->siswa->name ?? '-' }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-medium text-[#5b616e]">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</span>
                                    <span class="inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $badge }}">{{ $a->status }}</span>
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
                                    <h3 class="text-base font-bold text-black">Detail Absensi</h3>
                                    <button type="button" @click="detail = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                </div>

                                <div class="space-y-4 px-5 py-4">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Nama</p>
                                            <p class="text-sm font-bold text-black">{{ $a->siswa->name ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">NISN</p>
                                            <p class="text-sm font-medium text-black">{{ $a->siswa->nisn ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p>
                                            <p class="text-sm font-medium text-black">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Status</p>
                                            <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badge }}">{{ $a->status }}</span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Jam Masuk</p>
                                            <p class="text-sm font-medium text-black">{{ $a->jam_masuk ?? '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Jam Pulang</p>
                                            <p class="text-sm font-medium text-black">{{ $a->jam_pulang ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Status Validasi</p>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}">{{ $svLabel }}</span>
                                    </div>

                                    @if($a->catatan_instruktur)
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Catatan Instruktur</p>
                                            <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-sm font-medium italic text-black">
                                                {{ $a->catatan_instruktur }}
                                            </div>
                                        </div>
                                    @endif

                                    @if($a->foto_bukti)
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Bukti Fisik</p>
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ asset('storage/'.$a->foto_bukti) }}" download target="_blank" rel="noopener"
                                                   class="inline-flex items-center gap-1 rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                    Lihat Bukti
                                                </a>
                                                <a href="{{ asset('storage/'.$a->foto_bukti) }}" download
                                                   download="bukti-absensi-{{ $a->siswa->nisn ?? $a->id }}-{{ $a->id . '.' . $extBukti }}"
                                                   class="inline-flex items-center gap-1 rounded-full border-2 border-[#05b169]/40 bg-white px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/5">
                                                    Download Bukti
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="sticky bottom-0 space-y-2 border-t-2 border-[#0047d6]/15 bg-white px-5 py-4">
                                    @if($sv === 'diajukan')
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}" class="flex-1">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="aksi" value="tolak">
                                                <button type="submit" class="w-full rounded-xl border-2 border-[#cf202f]/40 bg-white px-4 py-2.5 text-sm font-bold text-[#cf202f] transition hover:bg-[#cf202f]/5">Tolak</button>
                                            </form>
                                            <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}" class="flex-1">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="aksi" value="valid">
                                                <button type="submit" class="w-full rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#049a5b]">Valid</button>
                                            </form>
                                        </div>
                                    @elseif($sv === 'disetujui')
                                        <p class="text-center text-sm font-bold text-[#05b169]">✓ Sudah Tervalidasi</p>
                                    @else
                                        <p class="text-center text-sm font-medium text-[#5b616e]">Belum diajukan siswa</p>
                                    @endif

                                    <a href="{{ route('cetak.absensi', $a->siswa_id) }}" target="_blank" rel="noopener"
                                       class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#cf202f] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#a81824]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                        </svg>
                                        Cetak PDF Absensi
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white px-4 py-8 text-center font-medium text-[#5b616e] italic">
                        Tidak ada data absensi.
                    </div>
                @endforelse
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div class="mt-4">
                {!! $absensi->links() !!}
            </div>

        </div>
    </div>
</x-app-layout>
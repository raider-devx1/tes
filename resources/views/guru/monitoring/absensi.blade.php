<x-app-layout title="Monitoring Absensi">
    <style>
        [x-cloak]{display:none!important;}
        .absensi-desktop{ display:none; }
        .absensi-mobile { display:block; }
        @media (min-width:1024px){
            .absensi-desktop{ display:block; }
            .absensi-mobile { display:none; }
        }
    </style>

    <div class="py-6 md:py-10 bg-slate-50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-12 2xl:px-16 space-y-6">

            {{-- ===== HEADER ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Monitoring &amp; Validasi Absensi</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Validasi bukti fisik &amp; jam kerja industri siswa bimbingan Anda.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- BUKA / TUTUP ABSENSI (siswa bimbingan: semua / per NISN) --}}
                    <div x-data="{ open:false, mode:'semua', nisn:'', list: @js($siswas->map(fn($s)=>['nisn'=>(string)$s->nisn,'name'=>$s->name])->values()), get cocok(){ const n=(this.nisn||'').trim(); return n ? (this.list.find(x=>x.nisn===n)||null) : null; } }" class="inline-block">
                        <button type="button" @click="open=true"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-[#05b169] bg-white px-4 py-2 text-sm font-bold text-[#05b169] transition hover:bg-[#05b169]/5">
                            <span class="inline-block h-2 w-2 rounded-full bg-[#05b169]"></span>
                            Buka / Tutup Absensi
                        </button>
                        {{-- Responsif: lembar bawah (bottom sheet) di HP, dialog tengah di laptop --}}
                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" @keydown.escape.window="open=false">
                            <div class="absolute inset-0 bg-black/50" @click="open=false"></div>
                            <div class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:max-h-[88vh] sm:max-w-lg sm:rounded-2xl">
                                <div class="flex-none border-b border-gray-100 px-4 pb-3 pt-3 sm:px-5 sm:py-4">
                                    <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-[#d7dbe3] sm:hidden"></div>
                                    <div class="flex items-center justify-between gap-3">
                                        <h3 class="text-base font-bold text-black">Buka / Tutup Absensi</h3>
                                        <button type="button" @click="open=false" aria-label="Tutup"
                                                class="-mr-1 flex h-8 w-8 flex-none items-center justify-center rounded-lg text-2xl leading-none text-[#5b616e] transition hover:bg-black/5 hover:text-black">&times;</button>
                                    </div>
                                </div>
                                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 pb-[calc(1rem+env(safe-area-inset-bottom))] text-left sm:px-5 sm:pb-4">
                                    <div class="flex gap-2">
                                        <button type="button" @click="mode='semua'" :class="mode==='semua' ? 'bg-[#0047d6] text-white' : 'bg-gray-100 text-[#5b616e]'" class="flex-1 rounded-lg px-3 py-2 text-sm font-bold">Semua Bimbingan</button>
                                        <button type="button" @click="mode='nisn'" :class="mode==='nisn' ? 'bg-[#0047d6] text-white' : 'bg-gray-100 text-[#5b616e]'" class="flex-1 rounded-lg px-3 py-2 text-sm font-bold">Per NISN</button>
                                    </div>
                                    <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-[#d98200]">Berlaku untuk siswa bimbingan Anda. Jika admin membuka absensi global, absensi tetap terbuka untuk semua.</p>

                                    <div x-show="mode==='semua'" class="space-y-3">
                                        <p class="text-sm text-[#5b616e]">Buka absensi untuk <span class="font-bold text-black">semua siswa bimbingan Anda</span> tanpa mengikuti jadwal. Tutup untuk mengembalikan ke jadwal.</p>
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('guru.monitoring.absensi.buka') }}" class="flex-1">@csrf<input type="hidden" name="mode" value="semua"><input type="hidden" name="aksi" value="buka"><button type="submit" class="w-full rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#049a5b]">Buka Semua</button></form>
                                            <form method="POST" action="{{ route('guru.monitoring.absensi.buka') }}" class="flex-1">@csrf<input type="hidden" name="mode" value="semua"><input type="hidden" name="aksi" value="tutup"><button type="submit" class="w-full rounded-xl border-2 border-[#cf202f]/40 bg-white px-4 py-2.5 text-sm font-bold text-[#cf202f] hover:bg-[#cf202f]/5">Tutup Semua</button></form>
                                        </div>
                                    </div>

                                    <div x-show="mode==='nisn'" class="space-y-3">
                                        <div>
                                            <label class="mb-1 block text-xs font-bold text-[#5b616e]">NISN Siswa</label>
                                            <input type="text" x-model="nisn" placeholder="Masukkan NISN" class="w-full rounded-xl border-gray-200 text-sm focus:border-[#0047d6] focus:ring-[#0047d6]">
                                            <p x-show="nisn.trim()!=='' && cocok" x-cloak class="mt-1 text-xs font-bold text-[#05b169]">✓ Cocok: <span x-text="cocok?.name"></span></p>
                                            <p x-show="nisn.trim()!=='' && !cocok" x-cloak class="mt-1 text-xs font-bold text-[#cf202f]">NISN bukan siswa bimbingan Anda / tidak ditemukan.</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('guru.monitoring.absensi.buka') }}" class="flex-1">@csrf<input type="hidden" name="mode" value="nisn"><input type="hidden" name="nisn" :value="nisn"><input type="hidden" name="aksi" value="buka"><button type="submit" :disabled="!cocok" :class="!cocok ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#049a5b]'" class="w-full rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white">Buka Siswa Ini</button></form>
                                            <form method="POST" action="{{ route('guru.monitoring.absensi.buka') }}" class="flex-1">@csrf<input type="hidden" name="mode" value="nisn"><input type="hidden" name="nisn" :value="nisn"><input type="hidden" name="aksi" value="tutup"><button type="submit" :disabled="!cocok" :class="!cocok ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#cf202f]/5'" class="w-full rounded-xl border-2 border-[#cf202f]/40 bg-white px-4 py-2.5 text-sm font-bold text-[#cf202f]">Tutup Siswa Ini</button></form>
                                        </div>
                                        @php $bukaGuru = isset($siswas) ? $siswas->where('absensi_dibuka', true) : collect(); @endphp
                                        @if($bukaGuru->count())
                                            <div class="rounded-lg bg-[#05b169]/5 px-3 py-2 text-xs text-[#05b169]">Sedang dibuka: @foreach($bukaGuru as $bg)<span class="font-semibold">{{ $bg->name }} ({{ $bg->nisn }})</span>@if(!$loop->last), @endif @endforeach</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('guru.dashboard') }}"
                       class="inline-flex items-center justify-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

            {{-- ===== ALERT ===== --}}
            @if (session('success'))
                <div class="rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                    <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- ===== REKAP ===== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Hadir</p><p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['Hadir'] ?? 0 }}</p></div>
                <div class="rounded-2xl border-2 border-[#0047d6]/30 bg-[#0047d6]/5 p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Izin</p><p class="mt-1 text-3xl font-bold text-[#0047d6]">{{ $rekap['Izin'] ?? 0 }}</p></div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sakit</p><p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['Sakit'] ?? 0 }}</p></div>
                <div class="rounded-2xl border-2 border-[#cf202f]/30 bg-[#cf202f]/5 p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Alpha</p><p class="mt-1 text-3xl font-bold text-[#cf202f]">{{ $rekap['Alpha'] ?? 0 }}</p></div>
            </div>

            {{-- ===== JAM KERJA INDUSTRI: VALIDASI USULAN + EDIT MANDIRI ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm">
                <div class="mb-4">
                    <h3 class="text-lg font-bold tracking-tight text-black">Jam Kerja Industri Siswa Bimbingan</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Jam standar admin: <span class="font-bold text-black">{{ substr($jamAdmin['masuk'],0,5) }} – {{ substr($jamAdmin['pulang'],0,5) }} WITA</span>.
                        Validasi usulan jam siswa (bila sesuai tempat industrinya) atau atur sendiri jam masuk &amp; pulang siswa.
                    </p>
                </div>

                {{-- Data siswa bimbingan untuk pencarian cepat via NISN (UX mobile) --}}
                @php
                    $updateJamUrlTemplate = route('guru.absensi.jam.update', ['siswa' => '__ID__']);
                    $siswaJamList = ($siswas ?? collect())->map(function ($s) {
                        $st = $s->status_jam_usulan ?? 'none';
                        return [
                            'id'          => $s->id,
                            'nisn'        => (string) ($s->nisn ?? ''),
                            'name'        => $s->name,
                            'kelas'       => $s->kelas ?? '-',
                            'jm'          => substr($s->jamMasukEfektif(), 0, 5),
                            'jp'          => substr($s->jamPulangEfektif(), 0, 5),
                            'status'      => $st,
                            'statusLabel' => match ($st) {
                                'diajukan'  => 'Menunggu Validasi',
                                'disetujui' => 'Jam Khusus Disetujui',
                                default     => 'Mengikuti Jam Admin',
                            },
                        ];
                    })->values();
                @endphp

                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                    {{-- ===== TOMBOL: LIHAT PENGAJUAN MENUNGGU VALIDASI ===== --}}
                    @if(($usulanJam ?? collect())->count())
                        <div x-data="{ openUsulan: false }" x-effect="document.body.style.overflow = openUsulan ? 'hidden' : ''" class="w-full sm:w-auto">
                            <button type="button" @click="openUsulan = true"
                                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border-2 border-[#d98200] bg-[#d98200]/5 px-4 py-2.5 text-sm font-bold text-[#d98200] transition hover:bg-[#d98200]/10">
                                <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#d98200] px-1.5 text-[11px] font-bold text-white">{{ $usulanJam->count() }}</span>
                                Lihat Pengajuan Menunggu Validasi
                            </button>

                            <template x-teleport="body">
                                <div x-show="openUsulan" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="openUsulan=false">
                                    <div class="absolute inset-0 bg-black/50" @click="openUsulan=false"></div>
                                    <div class="relative flex max-h-[85vh] w-full max-w-3xl flex-col rounded-2xl bg-white shadow-xl">
                                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                            <div>
                                                <h3 class="text-base font-bold text-black">Pengajuan Jam Menunggu Validasi</h3>
                                                <p class="text-xs font-medium text-[#5b616e]">Total {{ $usulanJam->count() }} siswa mengajukan perubahan jam kerja.</p>
                                            </div>
                                            <button type="button" @click="openUsulan=false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                        </div>
                                        <div class="overflow-y-auto px-5 py-4">
                                            <div class="grid gap-3 md:grid-cols-2">
                                                @foreach($usulanJam as $s)
                                                    <div class="rounded-xl border-2 border-[#0047d6]/15 bg-white p-4">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div class="min-w-0">
                                                                <p class="font-bold text-black truncate">{{ $s->name }}</p>
                                                                <p class="text-xs font-medium text-[#5b616e]">{{ $s->kelas ?? '-' }} · {{ $s->nisn ?? '-' }}</p>
                                                            </div>
                                                            <span class="inline-block rounded-full bg-[#d98200] px-2.5 py-1 text-[11px] font-bold text-white whitespace-nowrap">Diajukan</span>
                                                        </div>
                                                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                                                            <div class="rounded-lg bg-slate-50 p-2"><p class="font-bold uppercase tracking-wide text-[#5b616e]">Jam Admin</p><p class="font-bold text-black">{{ substr($jamAdmin['masuk'],0,5) }} – {{ substr($jamAdmin['pulang'],0,5) }}</p></div>
                                                            <div class="rounded-lg bg-[#0047d6]/5 p-2"><p class="font-bold uppercase tracking-wide text-[#5b616e]">Diajukan</p><p class="font-bold text-[#0047d6]">{{ substr($s->jam_masuk_usulan,0,5) }} – {{ substr($s->jam_pulang_usulan,0,5) }}</p></div>
                                                        </div>
                                                        @if($s->catatan_jam_usulan)
                                                            <p class="mt-2 rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-xs font-medium italic text-black">“{{ $s->catatan_jam_usulan }}”</p>
                                                        @endif
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            <form method="POST" action="{{ route('guru.absensi.jam.validasi', $s->id) }}" class="flex-1">
                                                                @csrf @method('PUT')
                                                                <input type="hidden" name="aksi" value="setuju">
                                                                <button type="submit" class="w-full rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049a5b]">Setujui</button>
                                                            </form>
                                                            <form method="POST" action="{{ route('guru.absensi.jam.validasi', $s->id) }}" class="flex-1">
                                                                @csrf @method('PUT')
                                                                <input type="hidden" name="aksi" value="tolak">
                                                                <button type="submit" class="w-full rounded-xl border-2 border-[#cf202f]/40 bg-white px-4 py-2 text-sm font-bold text-[#cf202f] transition hover:bg-[#cf202f]/5">Tolak</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endif

                    {{-- ===== TOMBOL: CARI & EDIT JAM KERJA via NISN ===== --}}
                    @if(($siswas ?? collect())->isEmpty())
                        <p class="text-sm font-medium text-[#5b616e] italic">Belum ada siswa bimbingan.</p>
                    @else
                        <div x-data="jamFinder(@js($siswaJamList), '{{ $updateJamUrlTemplate }}')" x-effect="document.body.style.overflow = open ? 'hidden' : ''" class="w-full sm:w-auto">
                            <button type="button" @click="bukaModal()"
                                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#0038aa]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                                Cari &amp; Edit Jam Kerja Siswa
                            </button>

                            <template x-teleport="body">
                                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="tutupModal()">
                                    <div class="absolute inset-0 bg-black/50" @click="tutupModal()"></div>
                                    <div class="relative flex max-h-[85vh] w-full max-w-md flex-col rounded-2xl bg-white shadow-xl">
                                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                            <h3 class="text-base font-bold text-black">Cari &amp; Edit Jam Kerja Siswa</h3>
                                            <button type="button" @click="tutupModal()" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                        </div>
                                        <div class="overflow-y-auto px-5 py-4 space-y-4 text-left">
                                            <div>
                                                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">NISN Siswa</label>
                                                <div class="flex gap-2">
                                                    <input type="text" x-model="nisn" @keydown.enter.prevent="cari()" inputmode="numeric" placeholder="Masukkan NISN siswa..."
                                                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                    <button type="button" @click="cari()" class="shrink-0 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Cari</button>
                                                </div>
                                                <p x-show="pesanError" x-cloak x-text="pesanError" class="mt-1 text-xs font-bold text-[#cf202f]"></p>
                                                <p class="mt-1 text-xs font-medium text-[#5b616e]">Masukkan NISN siswa bimbingan Anda untuk menampilkan &amp; mengubah jam kerjanya.</p>
                                            </div>

                                            <template x-if="siswa">
                                                <div class="space-y-4">
                                                    <div class="rounded-xl border-2 border-[#0047d6]/15 bg-slate-50 p-4">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div class="min-w-0">
                                                                <p class="font-bold text-black truncate" x-text="siswa.name"></p>
                                                                <p class="text-xs font-medium text-[#5b616e]"><span x-text="siswa.kelas"></span> · <span x-text="siswa.nisn"></span></p>
                                                            </div>
                                                            <span class="inline-block rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-[11px] font-bold text-[#0047d6] whitespace-nowrap" x-text="siswa.statusLabel"></span>
                                                        </div>
                                                        <p class="mt-2 text-sm font-bold text-black">Jam berlaku saat ini: <span class="text-[#0047d6]" x-text="siswa.jm + ' – ' + siswa.jp"></span></p>
                                                    </div>

                                                    <form :action="actionUrl" method="POST" class="space-y-4">
                                                        @csrf
                                                        @method('PUT')
                                                        <p class="text-xs font-medium text-[#5b616e]">Jam yang Anda simpan langsung berlaku sebagai jam kerja industri siswa (status disetujui).</p>
                                                        <div class="grid grid-cols-2 gap-3">
                                                            <div>
                                                                <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Masuk</label>
                                                                <input type="time" name="jam_masuk_industri" x-model="form.jm" required
                                                                       class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Pulang</label>
                                                                <input type="time" name="jam_pulang_industri" x-model="form.jp" required
                                                                       class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                            </div>
                                                        </div>
                                                        <div class="flex justify-end gap-2 pt-2">
                                                            <button type="button" @click="tutupModal()" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                                                            <button type="submit" class="rounded-xl bg-[#0047d6] px-4 py-2 text-sm font-bold text-white hover:bg-[#0038aa]">Simpan Jam</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ===== CETAK SEMUA PDF ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-black">Rekap Absensi Siswa Bimbingan</h3>
                    <p class="text-xs font-medium text-[#5b616e]">Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak rekap absensi seluruh siswa bimbingan Anda (1 siswa per halaman).</p>
                </div>
                <a href="{{ route('cetak.absensi.semua') }}" target="_blank" rel="noopener"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#cf202f] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#a81824] focus:outline-none focus:ring-4 focus:ring-[#cf202f]/30 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                    Cetak Semua PDF
                </a>
            </div>

            {{-- ===== FILTER ===== --}}
            <form method="GET" action="{{ route('guru.monitoring.absensi') }}" class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 flex flex-wrap gap-3 items-end shadow-sm">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                    <select name="status" class="rounded-xl border-2 border-[#0047d6]/25 bg-white pl-3 pr-10  py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
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
                <button type="submit" class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                <a href="{{ route('guru.monitoring.absensi') }}" class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
            </form>

            {{-- ========== TABEL LAPTOP / PC (>=1024px) ========== --}}
            <div class="absensi-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full min-w-[64rem] text-sm text-left table-auto">
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
                                $jm = $a->jam_masuk ? \Illuminate\Support\Str::substr($a->jam_masuk,0,5) : '';
                                $jp = $a->jam_pulang ? \Illuminate\Support\Str::substr($a->jam_pulang,0,5) : '';
                                $extBukti = $a->foto_bukti ? pathinfo($a->foto_bukti, PATHINFO_EXTENSION) : '';
                            @endphp
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black">{{ $absensi->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 font-bold text-black break-words">{{ $a->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $a->siswa->nisn ?? '-' }}</td>
                                <td class="px-4 py-3 text-center"><span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badge }}">{{ $a->status }}</span>@if($a->telat_masuk)<span class="mt-1 block text-[10px] font-bold text-[#d98200]">Telat Masuk</span>@endif</td>
                                <td class="px-4 py-3 text-center font-medium text-black">{{ $jm ?: '-' }}</td>
                                <td class="px-4 py-3 text-center font-medium text-black">{{ $jp ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}">{{ $svLabel }}</span>
                                        @if($a->foto_bukti)
                                            <div x-data class="flex flex-wrap items-center justify-center gap-2">
                                                {{-- Lihat Bukti: buka popup, TIDAK mengunduh & TIDAK pindah tab --}}
                                                <button type="button"
                                                        @click="$dispatch('buka-foto', { src: @js(asset('storage/'.$a->foto_bukti)), nama: @js($a->siswa->name ?? '-'), sub: @js(\Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y')), unduh: @js('bukti-absensi-'.($a->siswa->nisn ?? $a->id).'-'.$a->id.'.'.$extBukti) })"
                                                   class="inline-flex items-center gap-1 rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Lihat Bukti</button>
                                                <a href="{{ asset('storage/'.$a->foto_bukti) }}" download="bukti-absensi-{{ $a->siswa->nisn ?? $a->id }}-{{ $a->id . '.' . $extBukti }}"
                                                   class="inline-flex items-center gap-1 rounded-lg border-2 border-[#05b169]/40 bg-white px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/5">Download</a>
                                            </div>
                                        @endif
                                        @if($sv === 'diajukan')
                                            <div x-data="{ openValidasi: false }" class="inline-block">
                                                <button type="button" @click="openValidasi = true; window.ttdPadSiapkan && window.ttdPadSiapkan()" class="inline-flex items-center gap-1 rounded-lg bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">Validasi</button>
                                                <template x-teleport="body">
                                                    {{-- Responsif: lembar bawah (bottom sheet) di HP, dialog tengah di laptop --}}
                                                    <div x-show="openValidasi" x-cloak class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4" @keydown.escape.window="openValidasi=false">
                                                        <div class="absolute inset-0 bg-black/50" @click="openValidasi = false"></div>
                                                        <div class="relative max-h-[92vh] w-full overflow-y-auto overscroll-contain rounded-t-3xl bg-white px-4 py-4 pb-[calc(1rem+env(safe-area-inset-bottom))] text-left shadow-xl sm:max-h-[88vh] sm:max-w-lg sm:rounded-2xl sm:p-6">
                                                            <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-[#d7dbe3] sm:hidden"></div>
                                                            <div class="flex items-center justify-between gap-3 mb-4">
                                                                <h3 class="text-base font-bold text-black sm:text-lg">Validasi Absensi</h3>
                                                                <button type="button" @click="openValidasi = false" aria-label="Tutup"
                                                                        class="-mr-1 flex h-8 w-8 flex-none items-center justify-center rounded-lg text-2xl leading-none text-[#5b616e] transition hover:bg-black/5 hover:text-black">&times;</button>
                                                            </div>
                                                            <div class="space-y-2 text-sm text-black mb-4">
                                                                <p><span class="font-bold">Siswa:</span> {{ $a->siswa->name ?? '-' }}</p>
                                                                <p><span class="font-bold">Tanggal:</span> {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</p>
                                                                <p><span class="font-bold">Status:</span> {{ $a->status }}</p>
                                                                <p><span class="font-bold">Jam:</span> {{ $jm ?: '-' }} – {{ $jp ?: '-' }}</p>
                                                                @if($a->catatan_instruktur)<p><span class="font-bold">Catatan:</span> {{ $a->catatan_instruktur }}</p>@endif
                                                            </div>
                                                            <p class="text-xs text-[#5b616e] mb-4">Pastikan bukti fisik sudah diperiksa sebelum memvalidasi.</p>
                                                            {{-- Menyetujui WAJIB tanda tangan digital. Kanvas diletakkan DI DALAM form
                                                                 "Valid" saja, sehingga tombol "Tolak" tetap bisa ditekan tanpa tanda tangan. --}}
                                                            <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}" class="space-y-2">
                                                                @csrf @method('PUT')
                                                                <input type="hidden" name="aksi" value="valid">
                                                                <x-ttd-pad name="ttd_guru" label="Tanda Tangan Digital Guru Pembimbing" :tinggi="150" />
                                                                <button type="submit" class="w-full rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#049a5b]">Setujui &amp; Tanda Tangani</button>
                                                            </form>
                                                            {{-- Tombol "Tolak" membuka pop up: isi catatan -> Konfirmasi Tolak / Batal --}}
                                                            <div class="mt-3 flex justify-end">
                                                                @include('guru.partials.modal-tolak-absensi', ['a' => $a, 'varian' => 'desktop'])
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        @elseif($sv === 'disetujui')
                                            <span class="inline-flex items-center justify-center rounded-full bg-[#05b169]/10 px-3 py-1.5 text-xs font-bold text-[#05b169]">Tervalidasi</span>
                                            @if($a->ttd_guru)
                                                {{-- Paraf digital guru; gambar yang sama ikut tercetak di PDF absensi --}}
                                                <x-paraf-instruktur :ttd="$a->ttd_guru"
                                                                    :nama="$a->ttd_guru_nama"
                                                                    :waktu="$a->ttd_guru_signed_at"
                                                                    :tinggi="34"
                                                                    judul="Paraf Digital Guru Pembimbing"
                                                                    label="Paraf guru"
                                                                    peran-label="Nama guru"
                                                                    unduh-nama="paraf-absensi-{{ $a->id }}" />
                                            @endif
                                            {{-- Batalkan validasi: paraf digital dihapus & absensi kembali menunggu validasi --}}
                                            <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}"
                                                  data-confirm="Batalkan validasi absensi ini?"
                                                  data-confirm-text="Paraf digital yang sudah dibubuhkan akan dihapus dan absensi kembali berstatus Menunggu Validasi."
                                                  data-confirm-yes="Ya, batalkan">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="aksi" value="batal">
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border-2 border-[#cf202f]/40 bg-white px-3 py-1.5 text-xs font-bold text-[#cf202f] transition hover:bg-[#cf202f]/5">Batalkan Validasi</button>
                                            </form>
                                        @else
                                            <span class="text-xs font-medium text-[#5b616e]">Belum diajukan</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cetak.absensi', $a->siswa_id) }}" target="_blank" rel="noopener" title="Cetak PDF absensi siswa ini"
                                       class="inline-flex items-center gap-1.5 rounded-full bg-[#cf202f] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#a81824]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                                        Cetak
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data absensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ========== KARTU HP (<1024px) ========== --}}
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
                        $jm = $a->jam_masuk ? \Illuminate\Support\Str::substr($a->jam_masuk,0,5) : '';
                        $jp = $a->jam_pulang ? \Illuminate\Support\Str::substr($a->jam_pulang,0,5) : '';
                        $extBukti = $a->foto_bukti ? pathinfo($a->foto_bukti, PATHINFO_EXTENSION) : '';
                    @endphp
                    <div x-data="{ detail: false }" x-effect="window.detailTerbuka = detail; document.body.style.overflow = (detail || window.fotoPopupTerbuka) ? 'hidden' : ''"
                         class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-black truncate">{{ $a->siswa->name ?? '-' }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-medium text-[#5b616e]">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</span>
                                    <span class="inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $badge }}">{{ $a->status }}</span>
                                    @if($a->telat_masuk)<span class="inline-block rounded-full bg-[#fff4e5] px-2 py-0.5 text-[10px] font-bold text-[#d98200]">Telat Masuk</span>@endif
                                </div>
                            </div>
                            <button type="button" @click="detail = true" class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Lihat Detail</button>
                        </div>

                        <div x-show="detail" x-cloak
                             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4" @click.self="detail = false" @keydown.escape.window="if (! window.fotoPopupTerbuka) detail = false">
                            <div x-show="detail"
                                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl text-left">
                                <div class="sticky top-0 flex items-center justify-between border-b-2 border-[#0047d6]/15 bg-white px-5 py-3">
                                    <h3 class="text-base font-bold text-black">Detail Absensi</h3>
                                    <button type="button" @click="detail = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                </div>
                                <div class="space-y-4 px-5 py-4">
                                    <div class="grid grid-cols-2 gap-3">
                                        <div><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Nama</p><p class="text-sm font-bold text-black">{{ $a->siswa->name ?? '-' }}</p></div>
                                        <div><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">NISN</p><p class="text-sm font-medium text-black">{{ $a->siswa->nisn ?? '-' }}</p></div>
                                        <div><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p><p class="text-sm font-medium text-black">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</p></div>
                                        <div><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Status</p><span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badge }}">{{ $a->status }}</span></div>
                                        <div><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Jam Masuk</p><p class="text-sm font-medium text-black">{{ $jm ?: '-' }}</p></div>
                                        <div><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Jam Pulang</p><p class="text-sm font-medium text-black">{{ $jp ?: '-' }}</p></div>
                                    </div>
                                    <div><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Status Validasi</p><span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}">{{ $svLabel }}</span></div>
                                    @if($a->catatan_instruktur)
                                        <div><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Catatan Instruktur</p><div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-sm font-medium italic text-black">{{ $a->catatan_instruktur }}</div></div>
                                    @endif
                                    @if($a->foto_bukti)
                                        <div><p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Bukti Fisik</p>
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button"
                                                        @click="$dispatch('buka-foto', { src: @js(asset('storage/'.$a->foto_bukti)), nama: @js($a->siswa->name ?? '-'), sub: @js(\Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y')), unduh: @js('bukti-absensi-'.($a->siswa->nisn ?? $a->id).'-'.$a->id.'.'.$extBukti) })"
                                                        class="inline-flex items-center gap-1 rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">Lihat Bukti</button>
                                                <a href="{{ asset('storage/'.$a->foto_bukti) }}" download="bukti-absensi-{{ $a->siswa->nisn ?? $a->id }}-{{ $a->id . '.' . $extBukti }}" class="inline-flex items-center gap-1 rounded-full border-2 border-[#05b169]/40 bg-white px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/5">Download Bukti</a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="sticky bottom-0 space-y-2 border-t-2 border-[#0047d6]/15 bg-white px-5 py-4">
                                    @if($sv === 'diajukan')
                                        {{-- Menyetujui WAJIB tanda tangan digital. Kanvas diletakkan DI DALAM form
                                             "Valid" saja, sehingga tombol "Tolak" tetap bisa ditekan tanpa tanda tangan. --}}
                                        <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}" class="space-y-2">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="aksi" value="valid">
                                            <x-ttd-pad name="ttd_guru" label="Tanda Tangan Digital Guru Pembimbing" :tinggi="130" />
                                            <button type="submit" class="w-full rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#049a5b]">Setujui &amp; Tanda Tangani</button>
                                        </form>
                                        {{-- Tombol "Tolak" membuka pop up: isi catatan -> Konfirmasi Tolak / Batal --}}
                                        @include('guru.partials.modal-tolak-absensi', ['a' => $a, 'varian' => 'mobile'])
                                    @elseif($sv === 'disetujui')
                                        <p class="text-center text-sm font-bold text-[#05b169]">✓ Sudah Tervalidasi</p>
                                        @if($a->ttd_guru)
                                            {{-- Paraf digital guru; gambar yang sama ikut tercetak di PDF absensi --}}
                                            <div class="rounded-xl border-2 border-[#05b169]/40 bg-white px-3 py-2 text-center">
                                                <img src="{{ asset('storage/'.$a->ttd_guru) }}" alt="Paraf guru pembimbing" style="height:44px; width:auto; margin:0 auto; display:block;">
                                                <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-[#05b169]">Paraf guru pembimbing</p>
                                                @if($a->ttd_guru_nama)<p class="text-[10px] font-semibold text-[#5b616e]">{{ $a->ttd_guru_nama }}</p>@endif
                                            </div>
                                        @endif
                                        {{-- Batalkan validasi: paraf digital dihapus & absensi kembali menunggu validasi --}}
                                        <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}"
                                              data-confirm="Batalkan validasi absensi ini?"
                                              data-confirm-text="Paraf digital yang sudah dibubuhkan akan dihapus dan absensi kembali berstatus Menunggu Validasi."
                                              data-confirm-yes="Ya, batalkan">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="aksi" value="batal">
                                            <button type="submit" class="w-full rounded-xl border-2 border-[#cf202f]/40 bg-white px-4 py-2.5 text-sm font-bold text-[#cf202f] transition hover:bg-[#cf202f]/5">Batalkan Validasi</button>
                                        </form>
                                    @else
                                        <p class="text-center text-sm font-medium text-[#5b616e]">Belum diajukan siswa</p>
                                    @endif
                                    <a href="{{ route('cetak.absensi', $a->siswa_id) }}" target="_blank" rel="noopener" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#cf202f] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#a81824]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/></svg>
                                        Cetak PDF Absensi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data absensi.</div>
                @endforelse
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div class="mt-4">{!! $absensi->links() !!}</div>
        </div>
    </div>

    {{-- ===== POPUP LIHAT BUKTI FOTO =====
         Dipanggil dari tombol "Lihat Bukti" (tabel & kartu) lewat event 'buka-foto'.
         Foto tampil di kotak melayang, ditutup dengan tanda X, tombol Tutup,
         tombol Esc, atau klik area gelap di luar kotak. Tidak mengunduh apa pun. --}}
    <div x-data="{ open:false, src:'', nama:'', sub:'', unduh:'bukti-absensi.jpg' }"
         @buka-foto.window="src = $event.detail.src; nama = $event.detail.nama || ''; sub = $event.detail.sub || ''; unduh = $event.detail.unduh || 'bukti-absensi.jpg'; open = true"
         {{-- Tandai popup foto sedang terbuka, dan JANGAN buka kunci gulir selama popup detail masih terbuka --}}
         x-effect="window.fotoPopupTerbuka = open; document.body.style.overflow = (open || window.detailTerbuka) ? 'hidden' : ''">
        <template x-teleport="body">
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 flex items-end justify-center sm:items-center sm:p-4" style="z-index:60"
                 @keydown.escape.window="open = false">

                {{-- Lapisan gelap: klik untuk menutup --}}
                <div class="absolute inset-0" style="background-color:rgba(0,0,0,.65)" @click="open = false"></div>

                {{-- Responsif: lembar bawah (bottom sheet) di HP, dialog tengah di laptop --}}
                <div class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:max-h-[90vh] sm:max-w-3xl sm:rounded-2xl">
                    {{-- Kepala kotak + tombol X --}}
                    <div class="flex-none border-b-2 border-[#0047d6]/15 px-4 pb-2.5 pt-3 sm:px-5 sm:py-3">
                        <div class="mx-auto mb-2.5 h-1.5 w-10 rounded-full bg-[#d7dbe3] sm:hidden"></div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-black">Bukti Absensi</h3>
                                <p class="truncate text-xs font-medium text-[#5b616e]" x-text="[nama, sub].filter(Boolean).join(' \u2022 ')"></p>
                            </div>
                            <button type="button" @click="open = false" title="Tutup (Esc)" aria-label="Tutup"
                                    class="-mr-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-2xl leading-none text-[#5b616e] transition hover:bg-gray-100 hover:text-black">&times;</button>
                        </div>
                    </div>

                    {{-- Foto --}}
                    <div class="flex min-h-0 flex-1 items-center justify-center overflow-auto overscroll-contain p-3" style="background-color:#f4f6fb">
                        <img :src="src" alt="Bukti absensi" class="max-h-[52vh] max-w-full rounded-lg object-contain sm:max-h-[70vh]">
                    </div>

                    {{-- Kaki kotak --}}
                    <div class="flex-none border-t-2 border-[#0047d6]/15 px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] sm:px-5 sm:pb-3">
                        <div class="flex flex-col-reverse items-stretch gap-2 sm:flex-row sm:items-center sm:justify-end">
                            <a :href="src" :download="unduh"
                               class="inline-flex items-center justify-center gap-1 rounded-xl border-2 border-[#05b169]/40 bg-white px-4 py-3 text-sm font-bold text-[#05b169] transition hover:bg-[#05b169]/5 sm:py-2">Download</a>
                            <button type="button" @click="open = false"
                                    class="inline-flex items-center justify-center gap-1 rounded-xl bg-[#0047d6] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa] sm:py-2">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ===== Alpine: pencarian & edit jam kerja siswa via NISN ===== --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('jamFinder', (list, urlTemplate) => ({
                open: false,
                nisn: '',
                siswa: null,
                pesanError: '',
                form: { jm: '', jp: '' },
                list: Array.isArray(list) ? list : [],
                urlTemplate: urlTemplate || '',
                get actionUrl() {
                    return this.siswa ? this.urlTemplate.replace('__ID__', this.siswa.id) : '';
                },
                bukaModal() {
                    this.open = true;
                    this.nisn = '';
                    this.siswa = null;
                    this.pesanError = '';
                },
                tutupModal() {
                    this.open = false;
                },
                cari() {
                    const n = (this.nisn || '').trim();
                    if (!n) {
                        this.siswa = null;
                        this.pesanError = 'Masukkan NISN terlebih dahulu.';
                        return;
                    }
                    const found = this.list.find((x) => String(x.nisn) === n);
                    if (!found) {
                        this.siswa = null;
                        this.pesanError = 'NISN tidak ditemukan atau bukan siswa bimbingan Anda.';
                        return;
                    }
                    this.pesanError = '';
                    this.siswa = found;
                    this.form.jm = found.jm || '';
                    this.form.jp = found.jp || '';
                },
            }));
        });
    </script>
</x-app-layout>

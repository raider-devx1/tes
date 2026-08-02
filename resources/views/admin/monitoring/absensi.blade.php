<x-app-layout title="Monitoring Absensi">
    <style>[x-cloak]{display:none!important;}</style>

    <div x-data="absensiCrud()" class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10 py-6 sm:py-8 space-y-6">

        {{-- HEADER --}}
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Monitoring Absensi Siswa</h2>
            <p class="text-sm text-gray-500">Kelola kehadiran &amp; validasi siswa PKL (tambah, ubah, hapus, ubah status, cetak rekap).</p>
        </div>

        {{-- ===== CARD: AKSI ABSENSI ===== --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#2563EB]/10 text-[#2563EB]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Aksi Absensi</h3>
                    <p class="text-xs text-gray-500">Buka/tutup absensi, pengaturan jam, cetak rekap, dan tambah data absensi.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                {{-- BUKA / TUTUP ABSENSI (semua siswa / per NISN) --}}
                <div x-data="{ open:false, mode:'semua', nisn:'', list: @js($siswaList->map(fn($s)=>['nisn'=>(string)$s->nisn,'name'=>$s->name])->values()), get cocok(){ const n=(this.nisn||'').trim(); return n ? (this.list.find(x=>x.nisn===n)||null) : null; } }" class="inline-block">
                    <button type="button" @click="open=true"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg {{ $paksaBuka ? 'bg-[#05b169] text-white hover:bg-[#049a5b]' : 'border border-[#05b169] text-[#05b169] hover:bg-[#05b169]/5' }} px-4 py-2.5 text-sm font-semibold">
                        <span class="inline-block h-2 w-2 rounded-full {{ $paksaBuka ? 'bg-white' : 'bg-[#05b169]' }}"></span>
                        {{ $paksaBuka ? 'Absensi Dibuka' : 'Buka / Tutup Absensi' }}
                    </button>
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="open=false">
                        <div class="absolute inset-0 bg-black/50" @click="open=false"></div>
                        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl">
                            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                <h3 class="text-base font-bold text-gray-800">Buka / Tutup Absensi</h3>
                                <button type="button" @click="open=false" class="text-2xl leading-none text-gray-400 hover:text-black">&times;</button>
                            </div>
                            <div class="px-5 py-4 space-y-4 text-left">
                                <div class="flex gap-2">
                                    <button type="button" @click="mode='semua'" :class="mode==='semua' ? 'bg-[#2563EB] text-white' : 'bg-gray-100 text-gray-600'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">Semua Siswa</button>
                                    <button type="button" @click="mode='nisn'" :class="mode==='nisn' ? 'bg-[#2563EB] text-white' : 'bg-gray-100 text-gray-600'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">Per NISN</button>
                                </div>

                                <div x-show="mode==='semua'" class="space-y-3">
                                    <p class="text-sm text-gray-600">Buka absensi untuk <span class="font-bold">semua siswa</span> tanpa mengikuti jadwal jam. Tutup untuk mengembalikan semua ke jadwal.</p>
                                    <div class="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">
                                        <span class="font-semibold">Masuk:</span> <span class="{{ $paksaMasuk ? 'font-bold text-[#05b169]' : 'text-gray-500' }}">{{ $paksaMasuk ? 'DIBUKA (bebas waktu)' : 'Ikut jadwal' }}</span>
                                        <span class="mx-1 text-gray-300">|</span>
                                        <span class="font-semibold">Pulang:</span> <span class="{{ $paksaPulang ? 'font-bold text-[#05b169]' : 'text-gray-500' }}">{{ $paksaPulang ? 'DIBUKA (bebas waktu)' : 'Ikut jadwal' }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}">@csrf<input type="hidden" name="mode" value="semua"><input type="hidden" name="target" value="masuk"><input type="hidden" name="aksi" value="buka"><button type="submit" class="w-full rounded-lg bg-[#05b169] px-3 py-2 text-xs font-semibold text-white hover:bg-[#049a5b]">Buka Masuk</button></form>
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}">@csrf<input type="hidden" name="mode" value="semua"><input type="hidden" name="target" value="masuk"><input type="hidden" name="aksi" value="tutup"><button type="submit" class="w-full rounded-lg border border-[#cf202f] px-3 py-2 text-xs font-semibold text-[#cf202f] hover:bg-[#cf202f]/5">Tutup Masuk</button></form>
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}">@csrf<input type="hidden" name="mode" value="semua"><input type="hidden" name="target" value="pulang"><input type="hidden" name="aksi" value="buka"><button type="submit" class="w-full rounded-lg bg-[#05b169] px-3 py-2 text-xs font-semibold text-white hover:bg-[#049a5b]">Buka Pulang</button></form>
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}">@csrf<input type="hidden" name="mode" value="semua"><input type="hidden" name="target" value="pulang"><input type="hidden" name="aksi" value="tutup"><button type="submit" class="w-full rounded-lg border border-[#cf202f] px-3 py-2 text-xs font-semibold text-[#cf202f] hover:bg-[#cf202f]/5">Tutup Pulang</button></form>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}" class="flex-1">@csrf<input type="hidden" name="mode" value="semua"><input type="hidden" name="target" value="semua"><input type="hidden" name="aksi" value="buka"><button type="submit" class="w-full rounded-lg bg-[#05b169] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#049a5b]">Buka Semua</button></form>
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}" class="flex-1">@csrf<input type="hidden" name="mode" value="semua"><input type="hidden" name="target" value="semua"><input type="hidden" name="aksi" value="tutup"><button type="submit" class="w-full rounded-lg border border-[#cf202f] px-4 py-2.5 text-sm font-semibold text-[#cf202f] hover:bg-[#cf202f]/5">Tutup Semua</button></form>
                                    </div>
                                    <p class="rounded-lg bg-[#d98200]/10 px-3 py-2 text-[11px] leading-relaxed text-[#d98200]">Jika absensi dibuka admin, siswa <span class="font-semibold">tetap bisa absen walau hari Sabtu/Minggu</span> (di luar jadwal hari kerja). Hari libur yang tidak dibuka tetap kosong dan tidak otomatis Alpha.</p>
                                </div>

                                <div x-show="mode==='nisn'" class="space-y-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-gray-600">NISN Siswa</label>
                                        <input type="text" x-model="nisn" placeholder="Masukkan NISN" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                                        <p x-show="nisn.trim()!=='' && cocok" x-cloak class="mt-1 text-xs font-semibold text-[#05b169]">✓ Cocok: <span x-text="cocok?.name"></span></p>
                                        <p x-show="nisn.trim()!=='' && !cocok" x-cloak class="mt-1 text-xs font-semibold text-[#cf202f]">NISN tidak ditemukan.</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}">@csrf<input type="hidden" name="mode" value="nisn"><input type="hidden" name="nisn" :value="nisn"><input type="hidden" name="target" value="masuk"><input type="hidden" name="aksi" value="buka"><button type="submit" :disabled="!cocok" :class="!cocok ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#049a5b]'" class="w-full rounded-lg bg-[#05b169] px-3 py-2 text-xs font-semibold text-white">Buka Masuk</button></form>
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}">@csrf<input type="hidden" name="mode" value="nisn"><input type="hidden" name="nisn" :value="nisn"><input type="hidden" name="target" value="masuk"><input type="hidden" name="aksi" value="tutup"><button type="submit" :disabled="!cocok" :class="!cocok ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#cf202f]/5'" class="w-full rounded-lg border border-[#cf202f] px-3 py-2 text-xs font-semibold text-[#cf202f]">Tutup Masuk</button></form>
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}">@csrf<input type="hidden" name="mode" value="nisn"><input type="hidden" name="nisn" :value="nisn"><input type="hidden" name="target" value="pulang"><input type="hidden" name="aksi" value="buka"><button type="submit" :disabled="!cocok" :class="!cocok ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#049a5b]'" class="w-full rounded-lg bg-[#05b169] px-3 py-2 text-xs font-semibold text-white">Buka Pulang</button></form>
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}">@csrf<input type="hidden" name="mode" value="nisn"><input type="hidden" name="nisn" :value="nisn"><input type="hidden" name="target" value="pulang"><input type="hidden" name="aksi" value="tutup"><button type="submit" :disabled="!cocok" :class="!cocok ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#cf202f]/5'" class="w-full rounded-lg border border-[#cf202f] px-3 py-2 text-xs font-semibold text-[#cf202f]">Tutup Pulang</button></form>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}" class="flex-1">@csrf<input type="hidden" name="mode" value="nisn"><input type="hidden" name="nisn" :value="nisn"><input type="hidden" name="target" value="semua"><input type="hidden" name="aksi" value="buka"><button type="submit" :disabled="!cocok" :class="!cocok ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#049a5b]'" class="w-full rounded-lg bg-[#05b169] px-4 py-2.5 text-sm font-semibold text-white">Buka Semua (Siswa Ini)</button></form>
                                        <form method="POST" action="{{ route('admin.monitoring.absensi.buka') }}" class="flex-1">@csrf<input type="hidden" name="mode" value="nisn"><input type="hidden" name="nisn" :value="nisn"><input type="hidden" name="target" value="semua"><input type="hidden" name="aksi" value="tutup"><button type="submit" :disabled="!cocok" :class="!cocok ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#cf202f]/5'" class="w-full rounded-lg border border-[#cf202f] px-4 py-2.5 text-sm font-semibold text-[#cf202f]">Tutup Semua (Siswa Ini)</button></form>
                                    </div>
                                    <p class="rounded-lg bg-[#d98200]/10 px-3 py-2 text-[11px] leading-relaxed text-[#d98200]">Jika absensi siswa ini dibuka admin, siswa <span class="font-semibold">tetap bisa absen walau hari Sabtu/Minggu</span> (di luar jadwal hari kerjanya). Hari libur yang tidak dibuka tetap kosong dan tidak otomatis Alpha.</p>
                                    @if(isset($dibukaList) && count($dibukaList))
                                        <div class="rounded-lg bg-[#05b169]/5 px-3 py-2 text-xs text-[#05b169]">Dibuka per-siswa: @foreach($dibukaList as $d)@php $mk=$d->absensi_dibuka_masuk||$d->absensi_dibuka; $pl=$d->absensi_dibuka_pulang||$d->absensi_dibuka; $fx=$mk&&$pl?'Masuk+Pulang':($mk?'Masuk':'Pulang'); @endphp<span class="font-semibold">{{ $d->name }} ({{ $d->nisn }}) [{{ $fx }}]</span>@if(!$loop->last), @endif @endforeach</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" @click="pengaturanOpen=true"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-[#2563EB] px-4 py-2.5 text-sm font-semibold text-[#2563EB] hover:bg-blue-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Pengaturan Absensi
                </button>
                <button type="button" @click="cetakOpen=true"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#cf202f] px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Rekap
                </button>
                <button type="button" @click="tambah()"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Tambah Absensi</button>

                {{-- ===== TOMBOL: ATUR JUMLAH HADIR / IZIN / SAKIT / ALPHA ===== --}}
                @php
                    $rekapSiswaList = ($siswaJam ?? collect())->map(fn ($s) => [
                        'nisn'  => (string) ($s->nisn ?? ''),
                        'name'  => $s->name,
                        'kelas' => $s->kelas ?? '-',
                        'statusPkl' => $s->status_pkl ?? '-',
                    ])->values();
                @endphp
                <div x-data="rekapMassal(@js($rekapSiswaList), @js(route('admin.monitoring.absensi.rekap-siswa')), @js($pengaturanAbsensi['hari_kerja'] ?? 'senin_jumat'))"
                     x-effect="document.body.style.overflow = open ? 'hidden' : ''" class="inline-block">
                    <button type="button" @click="bukaModal()"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#d98200] px-4 py-2.5 text-sm font-semibold text-white hover:opacity-90">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6h13M9 5h13M4 7h.01M4 12h.01M4 17h.01"/></svg>
                        Atur Jumlah H/I/S/A
                    </button>

                    <template x-teleport="body">
                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="open=false">
                            <div class="absolute inset-0 bg-black/50" @click="open=false"></div>
                            <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col rounded-2xl bg-white shadow-xl">
                                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                    <div>
                                        <h3 class="text-base font-bold text-gray-800">Atur Jumlah Hadir / Izin / Sakit / Alpha</h3>
                                        <p class="text-xs font-medium text-[#5b616e]">Tentukan jumlah hari tiap status, sistem yang menuliskan tanggalnya.</p>
                                    </div>
                                    <button type="button" @click="open=false" class="text-2xl leading-none text-gray-400 hover:text-black">&times;</button>
                                </div>

                                <form method="POST" action="{{ route('admin.monitoring.absensi.rekap') }}" class="overflow-y-auto px-5 py-4 space-y-4 text-left" @submit="kirim($event)">
                                    @csrf

                                    {{-- Pilih lingkup --}}
                                    <div class="flex gap-2">
                                        <button type="button" @click="mode='nisn'" :class="mode==='nisn' ? 'bg-[#2563EB] text-white' : 'bg-gray-100 text-gray-600'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">Per Siswa (NISN)</button>
                                        <button type="button" @click="mode='semua'" :class="mode==='semua' ? 'bg-[#2563EB] text-white' : 'bg-gray-100 text-gray-600'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">Semua Siswa</button>
                                    </div>
                                    <input type="hidden" name="mode" :value="mode">

                                    {{-- Pencarian NISN --}}
                                    <div x-show="mode==='nisn'" x-cloak>
                                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">NISN Siswa</label>
                                        <div class="flex gap-2">
                                            <input type="text" x-model="nisn" @keydown.enter.prevent="cari()" inputmode="numeric" placeholder="Masukkan NISN siswa..."
                                                   class="w-full rounded-xl border-2 border-[#2563EB]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/30">
                                            <button type="button" @click="cari()" class="shrink-0 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1d4ed8]">Cari</button>
                                        </div>
                                        <input type="hidden" name="nisn" :value="nisn">
                                        <p x-show="memuat" x-cloak class="mt-1 text-xs font-medium text-[#5b616e]">Memuat data absensi siswa...</p>
                                        <p x-show="pesanError" x-cloak x-text="pesanError" class="mt-1 text-xs font-bold text-[#cf202f]"></p>

                                        {{-- Informasi absensi siswa yang ditemukan --}}
                                        <div x-show="info" x-cloak class="mt-3 rounded-xl border-2 border-[#2563EB]/20 bg-[#2563EB]/5 px-4 py-3">
                                            <p class="text-sm font-bold text-black" x-text="info?.siswa?.name"></p>
                                            <p class="text-xs font-medium text-[#5b616e]">
                                                NISN <span x-text="info?.siswa?.nisn"></span> &middot;
                                                <span x-text="info?.siswa?.kelas"></span> &middot;
                                                status PKL <span x-text="info?.siswa?.status_pkl"></span> &middot;
                                                jadwal <span class="font-bold" x-text="info?.siswa?.hari_kerja_label"></span>
                                                <span x-show="info?.siswa?.hari_kerja_khusus" class="font-bold text-[#d98200]">(khusus)</span>
                                            </p>

                                            <p class="mt-2 text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Absensi saat ini (seluruh riwayat)</p>
                                            <div class="mt-1 grid grid-cols-4 gap-2 text-center">
                                                <div class="rounded-lg bg-white px-2 py-1.5">
                                                    <p class="text-[10px] font-bold uppercase text-green-600">Hadir</p>
                                                    <p class="text-base font-extrabold text-black" x-text="info?.total?.Hadir ?? 0"></p>
                                                </div>
                                                <div class="rounded-lg bg-white px-2 py-1.5">
                                                    <p class="text-[10px] font-bold uppercase text-blue-600">Izin</p>
                                                    <p class="text-base font-extrabold text-black" x-text="info?.total?.Izin ?? 0"></p>
                                                </div>
                                                <div class="rounded-lg bg-white px-2 py-1.5">
                                                    <p class="text-[10px] font-bold uppercase text-amber-500">Sakit</p>
                                                    <p class="text-base font-extrabold text-black" x-text="info?.total?.Sakit ?? 0"></p>
                                                </div>
                                                <div class="rounded-lg bg-white px-2 py-1.5">
                                                    <p class="text-[10px] font-bold uppercase text-red-500">Alpha</p>
                                                    <p class="text-base font-extrabold text-black" x-text="info?.total?.Alpha ?? 0"></p>
                                                </div>
                                            </div>

                                            <p x-show="info?.rentang" x-cloak class="mt-2 text-[11px] font-medium text-[#5b616e]">
                                                Pada rentang tanggal terpilih:
                                                Hadir <span class="font-bold" x-text="info?.rentang?.Hadir ?? 0"></span>,
                                                Izin <span class="font-bold" x-text="info?.rentang?.Izin ?? 0"></span>,
                                                Sakit <span class="font-bold" x-text="info?.rentang?.Sakit ?? 0"></span>,
                                                Alpha <span class="font-bold" x-text="info?.rentang?.Alpha ?? 0"></span>.
                                            </p>

                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <button type="button" @click="isiDari('total')" class="rounded-lg bg-white px-3 py-1.5 text-[11px] font-bold text-[#2563EB] hover:bg-[#2563EB]/10">Salin angka saat ini</button>
                                                <button type="button" @click="isiDari('rentang')" x-show="info?.rentang" class="rounded-lg bg-white px-3 py-1.5 text-[11px] font-bold text-[#2563EB] hover:bg-[#2563EB]/10">Salin angka rentang</button>
                                                <button type="button" @click="nolkan()" class="rounded-lg bg-white px-3 py-1.5 text-[11px] font-bold text-[#cf202f] hover:bg-[#cf202f]/10">Nolkan semua</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="mode==='semua'" x-cloak class="rounded-xl border-2 border-[#cf202f]/30 bg-[#cf202f]/5 px-4 py-3 text-xs font-medium text-[#cf202f]">
                                        Jumlah ini akan diterapkan ke <span class="font-bold">SEMUA siswa PKL periode berjalan</span>. Absensi lama mereka pada rentang tanggal di bawah akan ditimpa.
                                    </div>

                                    {{-- Rentang tanggal --}}
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Tanggal Mulai</label>
                                            <input type="date" name="tanggal_mulai" x-model="form.mulai" required
                                                   class="w-full rounded-xl border-2 border-[#2563EB]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/30">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Tanggal Selesai</label>
                                            <input type="date" name="tanggal_selesai" x-model="form.selesai" required
                                                   class="w-full rounded-xl border-2 border-[#2563EB]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/30">
                                        </div>
                                    </div>

                                    {{-- Jadwal hari kerja: menentukan hari mana yang boleh diisi absensi --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Jadwal Hari Kerja</label>
                                        <select name="hari_kerja" x-model="form.hariKerja" class="w-full rounded-xl border-2 border-[#2563EB]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/30">
                                            <option value="">Ikut jadwal yang berlaku sekarang (<span x-text="labelJadwalAktif"></span>)</option>
                                            <option value="senin_jumat">Senin - Jumat (Sabtu &amp; Minggu dilewati)</option>
                                            <option value="senin_sabtu">Senin - Sabtu (hanya Minggu dilewati)</option>
                                        </select>
                                        <p class="mt-1 text-xs font-medium text-[#5b616e]">
                                            <span x-show="mode==='nisn'">Jadwal ini disimpan khusus untuk siswa tersebut — mis. siswa yang tetap masuk sampai Sabtu.</span>
                                            <span x-show="mode==='semua'" x-cloak>Jadwal ini disimpan sebagai jadwal global sekolah.</span>
                                            Hari libur dilewati: dikosongkan tanpa baris absensi dan tidak ditandai Alpha otomatis.
                                        </p>
                                    </div>

                                    {{-- Jumlah tiap status --}}
                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                        <div>
                                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-green-600">Hadir</label>
                                            <input type="number" min="0" name="jumlah_hadir" x-model.number="form.hadir" required class="w-full rounded-xl border-2 border-green-200 px-3 py-2.5 text-sm font-bold text-black focus:border-green-500 focus:ring-2 focus:ring-green-200">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-blue-600">Izin</label>
                                            <input type="number" min="0" name="jumlah_izin" x-model.number="form.izin" required class="w-full rounded-xl border-2 border-blue-200 px-3 py-2.5 text-sm font-bold text-black focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-amber-500">Sakit</label>
                                            <input type="number" min="0" name="jumlah_sakit" x-model.number="form.sakit" required class="w-full rounded-xl border-2 border-amber-200 px-3 py-2.5 text-sm font-bold text-black focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-red-500">Alpha</label>
                                            <input type="number" min="0" name="jumlah_alpha" x-model.number="form.alpha" required class="w-full rounded-xl border-2 border-red-200 px-3 py-2.5 text-sm font-bold text-black focus:border-red-500 focus:ring-2 focus:ring-red-200">
                                        </div>
                                    </div>

                                    {{-- Sisa hari --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Sisa Hari Diisi Sebagai</label>
                                        <select name="status_sisa" x-model="form.statusSisa" class="w-full rounded-xl border-2 border-[#2563EB]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/30">
                                            <option value="">Dikosongkan (tanpa baris absensi)</option>
                                            <option value="Hadir">Hadir</option>
                                            <option value="Izin">Izin</option>
                                            <option value="Sakit">Sakit</option>
                                            <option value="Alpha">Alpha</option>
                                        </select>
                                        <p class="mt-1 text-xs font-medium text-[#5b616e]">Bila dikosongkan, hari kerja sisa yang tanggalnya sudah lewat dapat ditandai <span class="font-bold">Alpha otomatis</span> oleh sistem. Hari libur tidak pernah ditandai Alpha.</p>
                                    </div>

                                    {{-- Pengosongan total --}}
                                    <label class="flex items-start gap-2 rounded-xl border-2 border-[#cf202f]/25 bg-[#cf202f]/5 px-4 py-3 text-xs font-medium text-[#cf202f]">
                                        <input type="checkbox" name="reset_total" value="1" x-model="form.resetTotal" class="mt-0.5 rounded border-[#cf202f]/40 text-[#cf202f] focus:ring-[#cf202f]">
                                        <span>Hapus <span class="font-bold">SELURUH riwayat absensi</span> (semua tanggal, bukan hanya rentang di atas) sebelum menulis ulang. Centang ini bila ingin memulai data absensi benar-benar dari 0.</span>
                                    </label>

                                    {{-- Ringkasan --}}
                                    <div class="rounded-xl bg-slate-50 px-4 py-3 text-xs font-medium text-gray-600">
                                        Hari kerja tersedia pada rentang: <span class="font-bold text-gray-800" x-text="hariTersedia"></span> hari
                                        (jadwal <span class="font-bold text-gray-800" x-text="labelJadwalAktif"></span>) &middot;
                                        total diisi: <span class="font-bold text-gray-800" x-text="totalDiminta"></span> hari
                                        <p x-show="totalDiminta > hariTersedia" x-cloak class="mt-1 font-bold text-[#cf202f]">Total melebihi hari kerja tersedia. Perlebar rentang tanggal atau kurangi jumlahnya.</p>
                                        <p x-show="totalDiminta === 0 && form.statusSisa === ''" x-cloak class="mt-1 font-bold text-[#cf202f]">Semua jumlah 0: rekap absensi akan dikosongkan menjadi 0.</p>
                                    </div>

                                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-medium text-amber-700">
                                        Absensi lama akan <span class="font-bold">dihapus dan ditulis ulang</span> (termasuk foto buktinya). Tindakan ini tidak bisa dibatalkan.
                                    </div>

                                    <div class="flex justify-end gap-2 pt-1">
                                        <button type="button" @click="open=false" class="rounded-xl border-2 border-[#2563EB]/25 bg-white px-4 py-2 text-sm font-bold text-[#2563EB] hover:bg-[#2563EB]/5">Batal</button>
                                        <button type="submit" :disabled="!bolehKirim" :class="!bolehKirim ? 'opacity-40 cursor-not-allowed' : 'hover:opacity-90'"
                                                class="rounded-xl bg-[#d98200] px-4 py-2 text-sm font-bold text-white">Terapkan Jumlah</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- ===== CARD: JAM KERJA INDUSTRI SISWA ===== --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-4 sm:p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#2563EB]/10 text-[#2563EB]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Jam Kerja Industri Siswa</h3>
                    <p class="text-xs text-gray-500">Cari siswa lewat NISN untuk mengubah jam kerja, dan tinjau pengajuan yang menunggu validasi.</p>
                </div>
            </div>
                {{-- Data siswa untuk pencarian cepat via NISN (UX mobile) --}}
                @php
                    $updateJamUrlTemplate = route('admin.monitoring.absensi.jam.update', ['siswa' => '__ID__']);
                    $siswaJamList = ($siswaJam ?? collect())->map(function ($s) {
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
                                                    <div class="rounded-xl border-2 border-[#2563EB]/15 bg-white p-4">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div class="min-w-0">
                                                                <p class="font-bold text-black truncate">{{ $s->name }}</p>
                                                                <p class="text-xs font-medium text-[#5b616e]">{{ $s->kelas ?? '-' }} · {{ $s->nisn ?? '-' }}</p>
                                                            </div>
                                                            <span class="inline-block rounded-full bg-[#d98200] px-2.5 py-1 text-[11px] font-bold text-white whitespace-nowrap">Diajukan</span>
                                                        </div>
                                                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                                                            <div class="rounded-lg bg-slate-50 p-2"><p class="font-bold uppercase tracking-wide text-[#5b616e]">Jam Admin</p><p class="font-bold text-black">{{ substr($jamAdmin['masuk'],0,5) }} – {{ substr($jamAdmin['pulang'],0,5) }}</p></div>
                                                            <div class="rounded-lg bg-[#2563EB]/5 p-2"><p class="font-bold uppercase tracking-wide text-[#5b616e]">Diajukan</p><p class="font-bold text-[#2563EB]">{{ substr($s->jam_masuk_usulan,0,5) }} – {{ substr($s->jam_pulang_usulan,0,5) }}</p></div>
                                                        </div>
                                                        @if($s->catatan_jam_usulan)
                                                            <p class="mt-2 rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-xs font-medium italic text-black">“{{ $s->catatan_jam_usulan }}”</p>
                                                        @endif
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            <form method="POST" action="{{ route('admin.monitoring.absensi.jam.validasi', $s->id) }}" class="flex-1">
                                                                @csrf @method('PUT')
                                                                <input type="hidden" name="aksi" value="setuju">
                                                                <button type="submit" class="w-full rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049a5b]">Setujui</button>
                                                            </form>
                                                            <form method="POST" action="{{ route('admin.monitoring.absensi.jam.validasi', $s->id) }}" class="flex-1">
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
                    @if(($siswaJam ?? collect())->isEmpty())
                        <p class="text-sm font-medium text-[#5b616e] italic">Belum ada siswa PKL aktif.</p>
                    @else
                        <div x-data="jamFinder(@js($siswaJamList), '{{ $updateJamUrlTemplate }}')" x-effect="document.body.style.overflow = open ? 'hidden' : ''" class="w-full sm:w-auto">
                            <button type="button" @click="bukaModal()"
                                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#1d4ed8]">
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
                                                           class="w-full rounded-xl border-2 border-[#2563EB]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/30">
                                                    <button type="button" @click="cari()" class="shrink-0 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1d4ed8]">Cari</button>
                                                </div>
                                                <p x-show="pesanError" x-cloak x-text="pesanError" class="mt-1 text-xs font-bold text-[#cf202f]"></p>
                                                <p class="mt-1 text-xs font-medium text-[#5b616e]">Masukkan NISN siswa untuk menampilkan &amp; mengubah jam kerjanya.</p>
                                            </div>

                                            <template x-if="siswa">
                                                <div class="space-y-4">
                                                    <div class="rounded-xl border-2 border-[#2563EB]/15 bg-slate-50 p-4">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div class="min-w-0">
                                                                <p class="font-bold text-black truncate" x-text="siswa.name"></p>
                                                                <p class="text-xs font-medium text-[#5b616e]"><span x-text="siswa.kelas"></span> · <span x-text="siswa.nisn"></span></p>
                                                            </div>
                                                            <span class="inline-block rounded-full bg-[#2563EB]/10 px-2.5 py-1 text-[11px] font-bold text-[#2563EB] whitespace-nowrap" x-text="siswa.statusLabel"></span>
                                                        </div>
                                                        <p class="mt-2 text-sm font-bold text-black">Jam berlaku saat ini: <span class="text-[#2563EB]" x-text="siswa.jm + ' – ' + siswa.jp"></span></p>
                                                    </div>

                                                    <form :action="actionUrl" method="POST" class="space-y-4">
                                                        @csrf
                                                        @method('PUT')
                                                        <p class="text-xs font-medium text-[#5b616e]">Jam yang Anda simpan langsung berlaku sebagai jam kerja industri siswa (status disetujui).</p>
                                                        <div class="grid grid-cols-2 gap-3">
                                                            <div>
                                                                <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Masuk</label>
                                                                <input type="time" name="jam_masuk_industri" x-model="form.jm" required
                                                                       class="w-full rounded-xl border-2 border-[#2563EB]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/30">
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Pulang</label>
                                                                <input type="time" name="jam_pulang_industri" x-model="form.jp" required
                                                                       class="w-full rounded-xl border-2 border-[#2563EB]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/30">
                                                            </div>
                                                        </div>
                                                        <div class="flex justify-end gap-2 pt-2">
                                                            <button type="button" @click="tutupModal()" class="rounded-xl border-2 border-[#2563EB]/25 bg-white px-4 py-2 text-sm font-bold text-[#2563EB] hover:bg-[#2563EB]/5">Batal</button>
                                                            <button type="submit" class="rounded-xl bg-[#2563EB] px-4 py-2 text-sm font-bold text-white hover:bg-[#1d4ed8]">Simpan Jam</button>
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

        {{-- STATUS BUKA-PAKSA ABSENSI --}}
        @if($paksaMasuk || $paksaPulang)
            <div class="flex items-start gap-2 rounded-xl border border-[#05b169]/40 bg-[#05b169]/5 px-4 py-3 text-sm font-medium text-[#05b169]">
                <span class="mt-1 inline-block h-2 w-2 flex-shrink-0 rounded-full bg-[#05b169]"></span>
                <span>
                    @if($paksaMasuk && $paksaPulang)
                        Absensi <span class="font-bold">MASUK &amp; PULANG DIBUKA untuk semua siswa</span> tanpa mengikuti jadwal jam.
                    @elseif($paksaMasuk)
                        Absensi <span class="font-bold">MASUK DIBUKA untuk semua siswa</span> (bebas waktu). Absen pulang tetap mengikuti jadwal jam.
                    @else
                        Absensi <span class="font-bold">PULANG DIBUKA untuk semua siswa</span> (bebas waktu). Absen masuk tetap mengikuti jadwal jam.
                    @endif
                    Buka menu <span class="font-bold">Buka / Tutup Absensi</span> untuk mengubah.
                </span>
            </div>
        @elseif(isset($dibukaList) && count($dibukaList))
            <div class="rounded-xl border border-[#05b169]/40 bg-[#05b169]/5 px-4 py-3 text-sm font-medium text-[#05b169]">
                <span class="font-bold">Absensi dibuka (bebas waktu)</span> untuk {{ count($dibukaList) }} siswa berikut:
                <span class="mt-1.5 flex flex-wrap gap-1.5">
                    @foreach($dibukaList as $d)
                        <span class="inline-block rounded-full border border-[#05b169]/30 bg-white px-2 py-0.5 text-xs font-semibold text-[#05b169]">{{ $d->name }} ({{ $d->nisn }})</span>
                    @endforeach
                </span>
            </div>
        @endif

        {{-- INFO PENGATURAN AKTIF --}}
        <div class="rounded-xl border border-blue-100 bg-blue-50/50 px-4 py-3 text-sm text-gray-600">
            Pengaturan berlaku untuk <span class="font-bold text-gray-800">semua siswa</span>:
            jam masuk <span class="font-bold text-gray-800">{{ substr($pengaturanAbsensi['jam_masuk'],0,5) }}</span>,
            jam pulang <span class="font-bold text-gray-800">{{ substr($pengaturanAbsensi['jam_pulang'],0,5) }}</span>,
            batas absensi <span class="font-bold text-gray-800">{{ $pengaturanAbsensi['durasi_menit'] }} menit</span>.
            <span class="text-gray-500">(Siswa dengan jam khusus industri yang disetujui guru akan memakai jamnya sendiri.)</span>
        </div>

        {{-- STATISTIK REKAP --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Hadir</p><p class="text-2xl font-bold text-green-600">{{ $rekap['Hadir'] }}</p></div>
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Izin</p><p class="text-2xl font-bold text-blue-600">{{ $rekap['Izin'] }}</p></div>
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Sakit</p><p class="text-2xl font-bold text-amber-500">{{ $rekap['Sakit'] }}</p></div>
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Alpha</p><p class="text-2xl font-bold text-red-500">{{ $rekap['Alpha'] }}</p></div>
        </div>

        {{-- FILTER --}}
        <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama / NISN"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                <select name="kelas" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    @foreach($kelasList as $k)<option value="{{ $k }}" @selected(request('kelas') === $k)>{{ $k }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Jurusan</label>
                <select name="jurusan" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    @foreach($jurusanList as $jr)<option value="{{ $jr }}" @selected(request('jurusan') === $jr)>{{ $jr }}</option>@endforeach
                </select>
            </div>
            {{-- Filter status PKL siswa: aktif / belum / selesai --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status PKL</label>
                <select name="status_pkl" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua Status PKL</option>
                    <option value="aktif" @selected(request('status_pkl') === 'aktif')>Aktif</option>
                    <option value="belum" @selected(request('status_pkl') === 'belum')>Belum</option>
                    <option value="selesai" @selected(request('status_pkl') === 'selesai')>Selesai</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status Absensi</label>
                <select name="status" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                    <option value="Izin"  @selected(request('status') === 'Izin')>Izin</option>
                    <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                    <option value="Alpha" @selected(request('status') === 'Alpha')>Alpha</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                <input type="month" name="bulan" value="{{ request('bulan') }}"
                       class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                       class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
            <a href="{{ route('admin.monitoring.absensi') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
        </form>

        {{-- ================= TABEL DESKTOP (>= lg) ================= --}}
        <div class="hidden lg:block bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">NISN</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Jurusan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Jam Masuk</th>
                            <th class="px-4 py-3 text-center">Jam Pulang</th>
                            <th class="px-4 py-3 text-center">Validasi</th>
                            <th class="px-4 py-3 text-center w-44">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($absensi as $a)
                            @php
                                $badge = match($a->status) {
                                    'Hadir' => 'bg-green-50 text-green-700',
                                    'Izin'  => 'bg-blue-50 text-blue-700',
                                    'Sakit' => 'bg-amber-50 text-amber-700',
                                    'Alpha' => 'bg-red-50 text-red-600',
                                    default => 'bg-gray-50 text-gray-600',
                                };
                                $jamMasuk  = $a->jam_masuk  ? \Illuminate\Support\Str::substr($a->jam_masuk, 0, 5)  : '';
                                $jamPulang = $a->jam_pulang ? \Illuminate\Support\Str::substr($a->jam_pulang, 0, 5) : '';
                                $sv = $a->status_validasi ?? 'draft';
                                $svBadge = match($sv) {
                                    'disetujui' => 'bg-green-50 text-green-700',
                                    'diajukan'  => 'bg-amber-50 text-amber-700',
                                    default     => 'bg-gray-100 text-gray-500',
                                };
                                $svLabel = match($sv) {
                                    'disetujui' => 'Tervalidasi',
                                    'diajukan'  => 'Menunggu',
                                    default     => 'Draft',
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/40 align-top">
                                <td class="px-4 py-3 text-center text-gray-500">{{ $absensi->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $a->tanggal->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $a->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $a->siswa->nisn ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $a->siswa->kelas ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $a->siswa->jurusan ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $a->status }}</span>
                                    @if($a->telat_masuk)<span class="mt-1 block text-[10px] font-bold text-[#d98200]">Telat Masuk</span>@endif
                                </td>
                                <td class="px-4 py-3 text-center">{{ $jamMasuk ?: '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $jamPulang ?: '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $svBadge }}">{{ $svLabel }}</span>
                                    @if($a->foto_bukti)
                                        <a href="{{ asset('storage/' . $a->foto_bukti) }}" download target="_blank"
                                           class="mt-1 block text-[11px] font-bold text-[#2563EB] hover:underline">Bukti</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <button type="button"
                                                @click="edit(@js([
                                                    'id' => $a->id,
                                                    'siswa_id' => $a->siswa_id,
                                                    'tanggal' => optional($a->tanggal)->format('Y-m-d'),
                                                    'status' => $a->status,
                                                    'jam_masuk' => $jamMasuk,
                                                    'jam_pulang' => $jamPulang,
                                                    'status_validasi' => $sv,
                                                    'catatan_instruktur' => $a->catatan_instruktur,
                                                    'foto_bukti_url' => $a->foto_bukti ? asset('storage/'.$a->foto_bukti) : null,
                                                ]))"
                                                class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-[#2563EB] hover:bg-blue-50">Edit</button>
                                        <button type="button"
                                                @click="konfirmHapus(@js(route('admin.monitoring.absensi.destroy', $a->id)))"
                                                class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                                        @if($a->siswa_id)
                                        <a href="{{ route('cetak.absensi', $a->siswa_id) }}" target="_blank"
                                           class="rounded-lg border border-green-200 px-3 py-1.5 text-xs font-semibold text-[#05b169] hover:bg-green-50">Cetak PDF</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================= TABEL MOBILE (< lg) ================= --}}
        <div class="lg:hidden bg-white rounded-xl border border-blue-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 text-gray-600 text-left">
                    <tr>
                        <th class="px-3 py-3 text-center w-10">No</th>
                        <th class="px-3 py-3">Siswa</th>
                        <th class="px-3 py-3 text-center w-28">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($absensi as $a)
                        @php
                            $badgeM = match($a->status) {
                                'Hadir' => 'bg-green-50 text-green-700',
                                'Izin'  => 'bg-blue-50 text-blue-700',
                                'Sakit' => 'bg-amber-50 text-amber-700',
                                'Alpha' => 'bg-red-50 text-red-600',
                                default => 'bg-gray-50 text-gray-600',
                            };
                            $jamMasukM  = $a->jam_masuk  ? \Illuminate\Support\Str::substr($a->jam_masuk, 0, 5)  : '';
                            $jamPulangM = $a->jam_pulang ? \Illuminate\Support\Str::substr($a->jam_pulang, 0, 5) : '';
                            $svM = $a->status_validasi ?? 'draft';
                        @endphp
                        <tr class="hover:bg-blue-50/40 align-middle">
                            <td class="px-3 py-4 text-center text-gray-500">{{ $absensi->firstItem() + $loop->index }}</td>
                            <td class="px-3 py-4">
                                <div class="font-semibold text-gray-800 leading-snug break-words">{{ $a->siswa->name ?? '-' }}</div>
                                <div class="text-[11px] text-gray-500 mt-0.5">{{ $a->tanggal->format('d M Y') }}</div>
                                <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-[10px] font-medium {{ $badgeM }}">{{ $a->status }}</span>
                                @if($a->telat_masuk)<span class="mt-1 inline-block px-2 py-0.5 rounded-full bg-[#fff4e5] text-[10px] font-bold text-[#d98200]">Telat Masuk</span>@endif
                            </td>
                            <td class="px-3 py-4 text-center">
                                <button type="button"
                                        @click="lihatDetail(@js([
                                            'id' => $a->id,
                                            'siswa_id' => $a->siswa_id,
                                            'nama' => $a->siswa->name ?? '-',
                                            'nisn' => $a->siswa->nisn ?? '-',
                                            'cetak_url' => route('cetak.absensi', $a->siswa_id),
                                            'kelas' => $a->siswa->kelas ?? '-',
                                            'jurusan' => $a->siswa->jurusan ?? '-',
                                            'tanggal' => optional($a->tanggal)->format('Y-m-d'),
                                            'tanggal_label' => $a->tanggal->format('d M Y'),
                                            'status' => $a->status,
                                            'jam_masuk' => $jamMasukM,
                                            'jam_pulang' => $jamPulangM,
                                            'status_validasi' => $svM,
                                            'catatan_instruktur' => $a->catatan_instruktur,
                                            'foto_bukti_url' => $a->foto_bukti ? asset('storage/'.$a->foto_bukti) : null,
                                            'destroy_url' => route('admin.monitoring.absensi.destroy', $a->id),
                                        ]))"
                                        class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-bold text-white transition active:scale-95 hover:bg-blue-700">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{!! $absensi->links() !!}</div>

        {{-- ================= MODAL PENGATURAN ABSENSI ================= --}}
        <div x-show="pengaturanOpen" x-cloak
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
             @keydown.escape.window="pengaturanOpen=false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.outside="pengaturanOpen=false">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-gray-800">Pengaturan Absensi (Semua Siswa)</h3>
                    <button type="button" @click="pengaturanOpen=false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-gray-100">&times;</button>
                </div>
                <form method="POST" action="{{ route('admin.monitoring.absensi.pengaturan') }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">Jam Masuk</label>
                            <x-jam-picker name="absensi_jam_masuk" :value="substr($pengaturanAbsensi['jam_masuk'],0,5)" required
                                          selectClass="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">Jam Pulang</label>
                            <x-jam-picker name="absensi_jam_pulang" :value="substr($pengaturanAbsensi['jam_pulang'],0,5)" required
                                          selectClass="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Batas Absensi (menit)</label>
                        <input type="number" name="absensi_durasi_menit" min="1" max="1440" required value="{{ $pengaturanAbsensi['durasi_menit'] }}"
                               class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        <p class="mt-1 text-[11px] text-gray-500">Lama jendela absensi dibuka setelah jam masuk/pulang (mis. 30 menit).</p>
                    </div>

                    {{-- ===== JADWAL HARI KERJA ABSENSI (GLOBAL) ===== --}}
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Jadwal Hari Kerja Absensi</label>
                        <select name="absensi_hari_kerja" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            <option value="senin_jumat" @selected(($pengaturanAbsensi['hari_kerja'] ?? 'senin_jumat') === 'senin_jumat')>Senin - Jumat (Sabtu &amp; Minggu libur)</option>
                            <option value="senin_sabtu" @selected(($pengaturanAbsensi['hari_kerja'] ?? 'senin_jumat') === 'senin_sabtu')>Senin - Sabtu (hanya Minggu libur)</option>
                        </select>
                        <p class="mt-1 text-[11px] text-gray-500">Hari libur dilewati: tidak ada baris absensi dan <span class="font-semibold">tidak ditandai Alpha otomatis</span>. Siswa yang jadwalnya berbeda dapat diatur satu per satu lewat pencarian NISN pada tombol &ldquo;Atur Jumlah H/I/S/A&rdquo;.</p>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Simpan Pengaturan</button>
                        <button type="button" @click="pengaturanOpen=false" class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= MODAL CETAK REKAP (FILTER) ================= --}}
        <div x-show="cetakOpen" x-cloak
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
             @keydown.escape.window="cetakOpen=false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.outside="cetakOpen=false">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-gray-800">Cetak Rekap Absensi</h3>
                    <button type="button" @click="cetakOpen=false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-gray-100">&times;</button>
                </div>
                <p class="mb-4 text-xs text-gray-500">Cetak rekap kehadiran seluruh siswa dalam satu PDF. Gunakan filter di bawah untuk membatasi per kelas dan/atau status kehadiran.</p>
                <form method="GET" action="{{ route('cetak.absensi.semua') }}" target="_blank" class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Kelas</label>
                        <select name="kelas" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $k)<option value="{{ $k }}">{{ $k }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Status Kehadiran</label>
                        <select name="status" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            <option value="">Semua Status</option>
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Bulan (opsional)</label>
                        <input type="month" name="bulan" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 rounded-lg bg-[#cf202f] px-4 py-2.5 text-sm font-bold text-white hover:opacity-90">Cetak PDF</button>
                        <button type="button" @click="cetakOpen=false" class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= MODAL DETAIL (mobile) ================= --}}
        <div x-show="detailOpen" x-cloak
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4" @keydown.escape.window="detailOpen = false">
            <div x-show="detailOpen"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl text-left" @click.outside="detailOpen = false">
                <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-blue-100 bg-white px-5 py-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800" x-text="detailData.nama"></h3>
                        <p class="text-xs text-gray-500">NISN <span x-text="detailData.nisn"></span></p>
                        <p class="text-xs text-gray-500"><span x-text="detailData.kelas"></span> &bull; <span x-text="detailData.jurusan"></span></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-gray-100">&times;</button>
                </div>
                <div class="space-y-4 p-5">
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                              :class="{ 'bg-green-50 text-green-700': detailData.status==='Hadir','bg-blue-50 text-blue-700': detailData.status==='Izin','bg-amber-50 text-amber-700': detailData.status==='Sakit','bg-red-50 text-red-600': detailData.status==='Alpha' }" x-text="detailData.status"></span>
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold"
                              :class="{ 'bg-green-50 text-green-700': detailData.status_validasi==='disetujui','bg-amber-50 text-amber-700': detailData.status_validasi==='diajukan','bg-gray-100 text-gray-500': detailData.status_validasi!=='disetujui'&&detailData.status_validasi!=='diajukan' }"
                              x-text="detailData.status_validasi==='disetujui'?'Tervalidasi':(detailData.status_validasi==='diajukan'?'Menunggu':'Draft')"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Tanggal</p><p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.tanggal_label"></p></div>
                        <div><p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Jam Masuk</p><p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.jam_masuk || '-'"></p></div>
                        <div><p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Jam Pulang</p><p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.jam_pulang || '-'"></p></div>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Catatan Instruktur</p>
                        <template x-if="detailData.catatan_instruktur"><div class="mt-1 rounded-lg border-l-4 border-amber-300 bg-amber-50 p-2.5 text-xs font-medium italic text-gray-700" x-text="detailData.catatan_instruktur"></div></template>
                        <template x-if="!detailData.catatan_instruktur"><p class="mt-0.5 text-sm italic text-gray-400">-</p></template>
                    </div>
                    <template x-if="detailData.foto_bukti_url">
                        <div><p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Bukti Fisik</p>
                            <a :href="detailData.foto_bukti_url" download target="_blank" class="mt-1 inline-block"><img loading="lazy" decoding="async" :src="detailData.foto_bukti_url" alt="Bukti" class="max-h-48 rounded-lg border border-blue-100 object-cover"></a>
                        </div>
                    </template>
                </div>
                <div class="sticky bottom-0 z-10 flex flex-wrap gap-2 border-t border-blue-100 bg-white px-5 py-4">
                    <button type="button" @click="editDariDetail()" class="flex-1 min-w-[90px] rounded-lg bg-[#2563EB] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-blue-700">Edit</button>
                    <button type="button" @click="detailOpen=false; konfirmHapus(detailData.destroy_url)" class="flex-1 min-w-[90px] rounded-lg bg-red-600 px-3 py-2.5 text-xs font-bold text-white transition hover:bg-red-700">Hapus</button>
                    <a :href="detailData.cetak_url" target="_blank" class="flex-1 min-w-[90px] rounded-lg bg-[#05b169] px-3 py-2.5 text-center text-xs font-bold text-white transition hover:bg-[#049a5b]">Cetak PDF</a>
                </div>
            </div>
        </div>

        {{-- ================= MODAL TAMBAH / EDIT ================= --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4" @keydown.escape.window="open = false">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-md sm:rounded-2xl sm:p-6 max-h-[90vh] overflow-y-auto" @click.outside="open = false">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-gray-800" x-text="mode === 'create' ? 'Tambah Absensi' : 'Edit Absensi'"></h3>
                    <button type="button" @click="open = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-gray-100">&times;</button>
                </div>
                <form :action="actionUrl" method="POST" enctype="multipart/form-data" @submit="simpan($event)" class="space-y-3">
                    @csrf
                    <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="siswa_id" :value="siswaCocok ? siswaCocok.id : ''">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">NISN Siswa</label>
                        <input type="text" x-model="form.nisn" placeholder="Masukkan NISN siswa" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        <template x-if="siswaCocok"><p class="mt-1 text-xs font-semibold text-green-600">&#10003; <span x-text="siswaCocok.name"></span></p></template>
                        <template x-if="form.nisn.trim() !== '' && !siswaCocok"><p class="mt-1 text-xs font-semibold text-red-600">NISN tidak cocok</p></template>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Tanggal</label>
                        <input type="date" name="tanggal" x-model="form.tanggal" required class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Status Kehadiran</label>
                        <select name="status" x-model="form.status" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="mb-1 block text-xs font-semibold text-gray-600">Jam Masuk</label>
                            <div class="flex items-center gap-1.5">
                                <select :value="pecahJam(form.jam_masuk)" @change="form.jam_masuk = gabungJam($event.target.value, pecahMenit(form.jam_masuk))"
                                        class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                                    <option value="">Jam</option>
                                    <template x-for="h in jamOpsi" :key="'jm'+h"><option :value="h" x-text="h"></option></template>
                                </select>
                                <span class="font-bold text-gray-400">:</span>
                                <select :value="pecahMenit(form.jam_masuk)" @change="form.jam_masuk = gabungJam(pecahJam(form.jam_masuk), $event.target.value)"
                                        class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                                    <option value="">Menit</option>
                                    <template x-for="m in menitOpsi" :key="'mm'+m"><option :value="m" x-text="m"></option></template>
                                </select>
                            </div>
                            <input type="hidden" name="jam_masuk" :value="form.jam_masuk || ''"></div>
                        <div><label class="mb-1 block text-xs font-semibold text-gray-600">Jam Pulang</label>
                            <div class="flex items-center gap-1.5">
                                <select :value="pecahJam(form.jam_pulang)" @change="form.jam_pulang = gabungJam($event.target.value, pecahMenit(form.jam_pulang))"
                                        class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                                    <option value="">Jam</option>
                                    <template x-for="h in jamOpsi" :key="'jp'+h"><option :value="h" x-text="h"></option></template>
                                </select>
                                <span class="font-bold text-gray-400">:</span>
                                <select :value="pecahMenit(form.jam_pulang)" @change="form.jam_pulang = gabungJam(pecahJam(form.jam_pulang), $event.target.value)"
                                        class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                                    <option value="">Menit</option>
                                    <template x-for="m in menitOpsi" :key="'mp'+m"><option :value="m" x-text="m"></option></template>
                                </select>
                            </div>
                            <input type="hidden" name="jam_pulang" :value="form.jam_pulang || ''"></div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Status Validasi</label>
                        <select name="status_validasi" x-model="form.status_validasi" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            <option value="draft">Draft</option>
                            <option value="diajukan">Menunggu Validasi</option>
                            <option value="disetujui">Tervalidasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Catatan Instruktur</label>
                        <textarea name="catatan_instruktur" x-model="form.catatan_instruktur" rows="2" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Foto Bukti Fisik (opsional)</label>
                        <template x-if="form.foto_bukti_url">
                            <div class="mb-1 flex items-center gap-3">
                                <a :href="form.foto_bukti_url" download target="_blank" class="text-[11px] font-bold text-[#2563EB] hover:underline">Lihat bukti saat ini</a>
                                <label class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600"><input type="checkbox" name="hapus_foto_bukti" value="1"> Hapus foto</label>
                            </div>
                        </template>
                        <input type="file" name="foto_bukti" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-semibold">
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''" class="flex-1 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Simpan</button>
                        <button type="button" @click="open = false" class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= MODAL HAPUS ================= --}}
        <div x-show="hapusOpen" x-cloak
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="hapusOpen = false">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false">
                <h3 class="text-base font-bold text-gray-800">Hapus Data Absensi</h3>
                <p class="mt-1 text-sm text-gray-500">Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
                <form :action="hapusUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.absensiCrud = function () {
            const daftarSiswa = @js($siswaList);
            const tanggalDefault = @js($tanggalDefault);
            const storeUrl = @js(route('admin.monitoring.absensi.store'));
            const baseUrl = @js(url('admin/monitoring/absensi'));
            const kosong = () => ({ id: null, nisn: '', tanggal: tanggalDefault, status: 'Hadir', jam_masuk: '', jam_pulang: '', status_validasi: 'draft', catatan_instruktur: '', foto_bukti_url: null });

            return {
                open: false,
                mode: 'create',
                form: kosong(),
                hapusOpen: false,
                hapusUrl: '',
                detailOpen: false,
                detailData: {},
                pengaturanOpen: false,
                cetakOpen: false,

                init() {
                    this.$watch('open',       () => this.kunciScroll());
                    this.$watch('hapusOpen',  () => this.kunciScroll());
                    this.$watch('detailOpen', () => this.kunciScroll());
                    this.$watch('pengaturanOpen', () => this.kunciScroll());
                    this.$watch('cetakOpen',  () => this.kunciScroll());
                },
                kunciScroll() {
                    document.body.style.overflow = (this.open || this.hapusOpen || this.detailOpen || this.pengaturanOpen || this.cetakOpen) ? 'hidden' : '';
                },

                get siswaCocok() {
                    const nisn = String(this.form.nisn || '').trim();
                    if (!nisn) return null;
                    return daftarSiswa.find(s => String(s.nisn).trim() === nisn) || null;
                },
                get actionUrl() { return this.mode === 'create' ? storeUrl : baseUrl + '/' + this.form.id; },

                tambah() { this.mode = 'create'; this.form = kosong(); this.open = true; },
                edit(d) {
                    const s = daftarSiswa.find(x => String(x.id) === String(d.siswa_id));
                    this.mode = 'edit';
                    this.form = {
                        id: d.id,
                        nisn: s ? String(s.nisn) : '',
                        tanggal: d.tanggal,
                        status: d.status || 'Hadir',
                        jam_masuk: d.jam_masuk || '',
                        jam_pulang: d.jam_pulang || '',
                        status_validasi: d.status_validasi || 'draft',
                        catatan_instruktur: d.catatan_instruktur || '',
                        foto_bukti_url: d.foto_bukti_url || null,
                    };
                    this.open = true;
                },
                lihatDetail(d) { this.detailData = d; this.detailOpen = true; },
                editDariDetail() {
                    const d = this.detailData;
                    this.detailOpen = false;
                    this.edit({
                        id: d.id, siswa_id: d.siswa_id, tanggal: d.tanggal, status: d.status,
                        jam_masuk: d.jam_masuk, jam_pulang: d.jam_pulang, status_validasi: d.status_validasi,
                        catatan_instruktur: d.catatan_instruktur, foto_bukti_url: d.foto_bukti_url,
                    });
                },
                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },
                jamOpsi: Array.from({ length: 24 }, (_, i) => String(i).padStart(2, '0')),
                menitOpsi: Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0')),
                pecahJam(v)   { v = String(v || ''); return v.includes(':') ? v.split(':')[0].padStart(2, '0') : ''; },
                pecahMenit(v) { v = String(v || ''); return v.includes(':') ? (v.split(':')[1] || '').padStart(2, '0') : ''; },
                gabungJam(h, m) {
                    h = String(h || ''); m = String(m || '');
                    if (h === '' && m === '') return '';
                    return (h === '' ? '00' : h.padStart(2, '0')) + ':' + (m === '' ? '00' : m.padStart(2, '0'));
                },
            };
        };
    </script>

    {{-- ===== Alpine: pencarian & edit jam kerja siswa via NISN ===== --}}
    <script>
        document.addEventListener('alpine:init', () => {
            /* ===== Atur jumlah Hadir/Izin/Sakit/Alpha (per NISN atau semua siswa) ===== */
            Alpine.data('rekapMassal', (list, urlRekap, hariKerjaGlobal) => ({
                open: false,
                mode: 'nisn',
                nisn: '',
                info: null,          // hasil dari server: data + rekap absensi siswa
                memuat: false,
                pesanError: '',
                list: Array.isArray(list) ? list : [],
                urlRekap: urlRekap,
                hariKerjaGlobal: hariKerjaGlobal || 'senin_jumat',
                form: {
                    mulai: '',
                    selesai: '',
                    hariKerja: '',   // '' = ikut jadwal yang berlaku
                    hadir: 0,
                    izin: 0,
                    sakit: 0,
                    alpha: 0,
                    statusSisa: '',
                    resetTotal: false,
                },

                bukaModal() {
                    const hariIni = new Date();
                    const awalBulan = new Date(hariIni.getFullYear(), hariIni.getMonth(), 1);
                    const fmt = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

                    this.open = true;
                    this.nisn = '';
                    this.info = null;
                    this.memuat = false;
                    this.pesanError = '';
                    this.form.mulai = fmt(awalBulan);
                    this.form.selesai = fmt(hariIni);
                    this.form.hariKerja = '';
                    this.form.hadir = 0;
                    this.form.izin = 0;
                    this.form.sakit = 0;
                    this.form.alpha = 0;
                    this.form.statusSisa = '';
                    this.form.resetTotal = false;
                },

                /*
                 * Cari siswa lewat NISN, lalu tampilkan jumlah Hadir/Izin/Sakit/Alpha
                 * miliknya saat ini. Angka itu langsung dimuat ke kolom isian supaya
                 * admin tinggal mengedit seperlunya.
                 */
                async cari() {
                    const n = (this.nisn || '').trim();
                    this.info = null;

                    if (!n) {
                        this.pesanError = 'Masukkan NISN terlebih dahulu.';
                        return;
                    }

                    // Pengecekan cepat di daftar lokal agar salah ketik langsung ketahuan.
                    if (this.list.length && !this.list.some((x) => String(x.nisn) === n)) {
                        this.pesanError = 'NISN tidak ditemukan.';
                        return;
                    }

                    this.pesanError = '';
                    this.memuat = true;

                    try {
                        const url = this.urlRekap
                            + '?nisn=' + encodeURIComponent(n)
                            + '&tanggal_mulai=' + encodeURIComponent(this.form.mulai || '')
                            + '&tanggal_selesai=' + encodeURIComponent(this.form.selesai || '');

                        const res = await fetch(url, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const data = await res.json();

                        if (!res.ok || !data.ok) {
                            this.pesanError = data.pesan || 'Data siswa gagal dimuat.';
                            return;
                        }

                        this.info = data;
                        // Jadwal siswa ini ditampilkan sebagai pilihan awal.
                        this.form.hariKerja = data.siswa.hari_kerja || '';
                        this.isiDari('total');
                    } catch (e) {
                        this.pesanError = 'Tidak dapat menghubungi server. Coba lagi.';
                    } finally {
                        this.memuat = false;
                    }
                },

                /* Salin angka absensi siswa ke kolom isian (bisa diedit bebas). */
                isiDari(sumber) {
                    const asal = sumber === 'rentang' ? this.info?.rentang : this.info?.total;
                    if (!asal) return;

                    this.form.hadir = Number(asal.Hadir || 0);
                    this.form.izin  = Number(asal.Izin  || 0);
                    this.form.sakit = Number(asal.Sakit || 0);
                    this.form.alpha = Number(asal.Alpha || 0);
                },

                /* Nolkan semua jumlah: rekap siswa akan dikosongkan. */
                nolkan() {
                    this.form.hadir = 0;
                    this.form.izin = 0;
                    this.form.sakit = 0;
                    this.form.alpha = 0;
                    this.form.statusSisa = '';
                },

                /* Jadwal hari kerja yang sedang berlaku pada form ini. */
                get jadwalAktif() {
                    if (this.form.hariKerja) return this.form.hariKerja;
                    if (this.mode === 'nisn' && this.info?.siswa?.hari_kerja_efektif) {
                        return this.info.siswa.hari_kerja_efektif;
                    }
                    return this.hariKerjaGlobal;
                },

                get labelJadwalAktif() {
                    return this.jadwalAktif === 'senin_sabtu' ? 'Senin - Sabtu' : 'Senin - Jumat';
                },

                /*
                 * Jumlah HARI KERJA pada rentang tanggal terpilih.
                 * Minggu selalu dilewati; Sabtu dilewati bila jadwal Senin-Jumat.
                 */
                get hariTersedia() {
                    if (!this.form.mulai || !this.form.selesai) return 0;
                    const mulai = new Date(this.form.mulai + 'T00:00:00');
                    const selesai = new Date(this.form.selesai + 'T00:00:00');
                    if (isNaN(mulai) || isNaN(selesai) || selesai < mulai) return 0;

                    const sampaiSabtu = this.jadwalAktif === 'senin_sabtu';
                    let jumlah = 0;

                    for (let d = new Date(mulai); d <= selesai; d.setDate(d.getDate() + 1)) {
                        const hari = d.getDay(); // 0 = Minggu, 6 = Sabtu
                        if (hari === 0) continue;
                        if (hari === 6 && !sampaiSabtu) continue;
                        jumlah++;
                    }
                    return jumlah;
                },

                get totalDiminta() {
                    const n = (v) => (Number.isFinite(Number(v)) ? Number(v) : 0);
                    return n(this.form.hadir) + n(this.form.izin) + n(this.form.sakit) + n(this.form.alpha);
                },

                /*
                 * Mengisi hanya Hadir (yang lain 0) SAH. Mengisi semua 0 juga SAH:
                 * artinya rekap absensi dikosongkan menjadi 0.
                 */
                get bolehKirim() {
                    if (this.mode === 'nisn' && !this.info) return false;
                    if (!this.form.mulai || !this.form.selesai) return false;
                    if (this.totalDiminta > this.hariTersedia) return false;
                    return true;
                },

                kirim(e) {
                    if (!this.bolehKirim) {
                        e.preventDefault();
                        return;
                    }

                    const sasaran = this.mode === 'semua'
                        ? 'SEMUA siswa PKL periode berjalan'
                        : (this.info?.siswa?.name || 'siswa terpilih');

                    let pesan;
                    if (this.totalDiminta === 0 && this.form.statusSisa === '') {
                        pesan = 'Kosongkan rekap absensi ' + sasaran + ' menjadi 0? '
                            + (this.form.resetTotal
                                ? 'SELURUH riwayat absensi akan dihapus.'
                                : 'Absensi pada rentang tanggal tersebut akan dihapus.');
                    } else {
                        pesan = 'Terapkan jumlah ini ke ' + sasaran + '? Absensi lama '
                            + (this.form.resetTotal ? '(seluruh riwayat) ' : 'pada rentang tanggal tersebut ')
                            + 'akan ditimpa.';
                    }

                    if (!window.confirm(pesan)) {
                        e.preventDefault();
                    }
                },
            }));

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
                        this.pesanError = 'NISN tidak ditemukan.';
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

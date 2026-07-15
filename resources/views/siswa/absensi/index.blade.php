<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-black">Daftar Hadir PKL Saya</h1>
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <style>
        [x-cloak]{display:none!important;}

        /* Sembunyikan segmen AM/PM pada input type=time (WebKit: Chrome, Edge, Safari) */
        input.jam-24[type="time"]::-webkit-datetime-edit-ampm-field {
            display: none;
        }
        /* Rapikan agar tidak ada sisa spasi setelah menit */
        input.jam-24[type="time"]::-webkit-datetime-edit-hour-field,
        input.jam-24[type="time"]::-webkit-datetime-edit-minute-field {
            padding: 0 2px;
        }
        /* Sembunyikan spinner default agar tampilan bersih (opsional) */
        input.jam-24[type="time"]::-webkit-calendar-picker-indicator {
            opacity: .6;
        }
        /* Pastikan popup Flatpickr tampil di atas modal */
        .flatpickr-calendar { z-index: 99999 !important; }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <div class="bg-white min-h-screen">
        <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-xl border-2 border-[#05b169]/40 bg-[#05b169]/5 px-4 py-3 text-sm font-semibold text-[#05b169]">
                     {{ session('success') }} 
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-xl border-2 border-[#cf202f]/40 bg-[#cf202f]/5 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                     {{ session('error') }} 
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-xl border-2 border-[#cf202f]/40 bg-[#cf202f]/5 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li> {{ $error }} </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="rounded-2xl border-2 border-[#05b169]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-[#05b169]"> {{ $rekap['Hadir'] ?? 0 }} </p>
                    <p class="text-sm font-bold text-black mt-1">Hadir</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-[#0047d6]"> {{ $rekap['Izin'] ?? 0 }} </p>
                    <p class="text-sm font-bold text-black mt-1">Izin</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-[#d98200]"> {{ $rekap['Sakit'] ?? 0 }} </p>
                    <p class="text-sm font-bold text-black mt-1">Sakit</p>
                </div>
                <div class="rounded-2xl border-2 border-[#cf202f]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-3xl font-black text-[#cf202f]"> {{ $rekap['Alpha'] ?? 0 }} </p>
                    <p class="text-sm font-bold text-black mt-1">Alpha</p>
                </div>
            </div>

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3"
                 x-data="{
                    openTambah: false,
                    isiSekarang(ref) {
                        // Jam sekarang WITA (Asia/Makassar), 24 jam -> HH:MM
                        const now = new Date().toLocaleTimeString('en-GB', {
                            timeZone: 'Asia/Makassar', hour12: false,
                            hour: '2-digit', minute: '2-digit'
                        });
                        const el = this.$refs[ref];
                        if (el && el._flatpickr) { el._flatpickr.setDate(now, true); }
                        else if (el) { el.value = now; }
                    }
                 }"
                 x-on:keydown.escape.window="openTambah = false">

                <form method="GET" action="{{ route('siswa.absensi.index') }}" class="flex flex-wrap items-center gap-2">
                    <input type="month" name="bulan" value="{{ request('bulan') }}"
                           class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                    <button type="submit"
                            class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                    @if(request('bulan'))
                        <a href="{{ route('siswa.absensi.index') }}"
                           class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                    @endif
                </form>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="openTambah = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#049a5b]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Absensi
                    </button>
                    <a href="{{ route('cetak.absensi', request()->only('bulan')) }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:opacity-90"
                       style="background-color:#cf202f;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak PDF
                    </a>
                </div>

                <template x-teleport="body">
                    <div x-show="openTambah" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
                         role="dialog" aria-modal="true" aria-labelledby="judulTambahAbsensi">
                        <div class="absolute inset-0 bg-black/50" @click="openTambah = false"></div>
                        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                            <div class="flex items-center justify-between mb-4">
                                <h3 id="judulTambahAbsensi" class="text-lg font-bold text-black">Tambah Absensi</h3>
                                <button type="button" @click="openTambah = false" class="text-[#5b616e] hover:text-black">&times;</button>
                            </div>

                            <form method="POST" action="{{ route('siswa.absensi.store') }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                                    <select name="status" required
                                            class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                        <option value="Hadir" @selected(old('status') === 'Hadir')>Hadir</option>
                                        <option value="Izin"  @selected(old('status') === 'Izin')>Izin</option>
                                        <option value="Sakit" @selected(old('status') === 'Sakit')>Sakit</option>
                                        <option value="Alpha" @selected(old('status') === 'Alpha')>Alpha</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Masuk (WITA)</label>
                                        <input type="text" name="jam_masuk" x-ref="jam_masuk"
                                               x-init="flatpickr($el, { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, minuteIncrement: 1, allowInput: false })"
                                               readonly placeholder="--:--"
                                               value="{{ old('jam_masuk') }}"
                                               class="w-full cursor-pointer rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                        <button type="button" @click="isiSekarang('jam_masuk')"
                                                class="mt-1 text-xs font-bold text-[#0047d6] hover:underline">Isi jam sekarang</button>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Pulang (WITA)</label>
                                        <input type="text" name="jam_pulang" x-ref="jam_pulang"
                                               x-init="flatpickr($el, { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, minuteIncrement: 1, allowInput: false })"
                                               readonly placeholder="--:--"
                                               value="{{ old('jam_pulang') }}"
                                               class="w-full cursor-pointer rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                        <button type="button" @click="isiSekarang('jam_pulang')"
                                                class="mt-1 text-xs font-bold text-[#0047d6] hover:underline">Isi jam sekarang</button>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="openTambah = false"
                                            class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Batal</button>
                                    <button type="submit"
                                            class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049a5b]">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            <div class="rounded-xl border-2 border-[#0047d6]/15 bg-white shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 text-center font-bold w-12">No</th>
                            <th class="px-4 py-3 text-left font-bold">Tanggal</th>
                            <th class="px-4 py-3 text-center font-bold">Status</th>
                            <th class="px-4 py-3 text-center font-bold">Jam Masuk</th>
                            <th class="px-4 py-3 text-center font-bold">Jam Pulang</th>
                            <th class="px-4 py-3 text-center font-bold">Validasi</th>
                            <th class="px-4 py-3 text-center font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($absensis as $a)
                            @php
                                $badge = match ($a->status) {
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
                                    default     => 'Draft',
                                };
                            @endphp
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black"> {{ $loop->iteration }} </td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                     {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }} 
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $badge }}"> {{ $a->status }} </span>
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-black">
                                     {{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }} 
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-black">
                                     {{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '-' }} 
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}"> {{ $svLabel }} </span>
                                    @if($a->foto_bukti)
                                        <div class="mt-1">
                                            <a href="{{ asset('storage/'.$a->foto_bukti) }}" target="_blank" rel="noopener"
                                               class="text-xs font-bold text-[#0047d6] underline hover:text-[#0038aa]">Lihat Bukti</a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        {{-- ===== Cetak PDF per baris absensi ===== --}}
                                        <a href="{{ route('cetak.absensi', ['absensi_id' => $a->id]) }}"
                                           target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:opacity-90"
                                           style="background-color:#cf202f;">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            Cetak PDF
                                        </a>

                                        {{-- ===== Edit & Hapus (hanya saat status masih draft) ===== --}}
                                        @if($sv === 'draft')
                                            <div class="flex items-center justify-center gap-2">
                                                {{-- Tombol Edit --}}
                                                <div x-data="{ openEdit: false }" class="inline-block" x-on:keydown.escape.window="openEdit = false">
                                                    <button type="button" @click="openEdit = true"
                                                            class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold text-white transition hover:opacity-90"
                                                            style="background-color:#d98200;">
                                                        Edit
                                                    </button>
                                                    <template x-teleport="body">
                                                        <div x-show="openEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                                             role="dialog" aria-modal="true">
                                                            <div class="absolute inset-0 bg-black/50" @click="openEdit = false"></div>
                                                            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl text-left">
                                                                <div class="flex items-center justify-between mb-4">
                                                                    <h3 class="text-lg font-bold text-black">Edit Absensi</h3>
                                                                    <button type="button" @click="openEdit = false" class="text-[#5b616e] hover:text-black">&times;</button>
                                                                </div>

                                                                <form method="POST" action="{{ route('siswa.absensi.update', $a->id) }}" class="space-y-4">
                                                                    @csrf
                                                                    @method('PUT')

                                                                    <div>
                                                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                                                                        <input type="date" name="tanggal" required
                                                                               value="{{ \Carbon\Carbon::parse($a->tanggal)->format('Y-m-d') }}"
                                                                               class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                                    </div>

                                                                    <div>
                                                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                                                                        <select name="status" required
                                                                                class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                                            <option value="Hadir" @selected($a->status === 'Hadir')>Hadir</option>
                                                                            <option value="Izin"  @selected($a->status === 'Izin')>Izin</option>
                                                                            <option value="Sakit" @selected($a->status === 'Sakit')>Sakit</option>
                                                                            <option value="Alpha" @selected($a->status === 'Alpha')>Alpha</option>
                                                                        </select>
                                                                    </div>

                                                                    <div class="grid grid-cols-2 gap-3">
                                                                        <div>
                                                                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Masuk (WITA)</label>
                                                                            <input type="text" name="jam_masuk" 
                                                                                   x-init="flatpickr($el, { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, minuteIncrement: 1, allowInput: false })"
                                                                                   readonly placeholder="--:--"
                                                                                   value="{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '' }}"
                                                                                   class="w-full cursor-pointer rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                                        </div>
                                                                        <div>
                                                                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Pulang (WITA)</label>
                                                                            <input type="text" name="jam_pulang" 
                                                                                   x-init="flatpickr($el, { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, minuteIncrement: 1, allowInput: false })"
                                                                                   readonly placeholder="--:--"
                                                                                   value="{{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '' }}"
                                                                                   class="w-full cursor-pointer rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                                        </div>
                                                                    </div>

                                                                    <div class="flex justify-end gap-2 pt-2">
                                                                        <button type="button" @click="openEdit = false"
                                                                                class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Batal</button>
                                                                        <button type="submit"
                                                                                class="rounded-xl px-4 py-2 text-sm font-bold text-white transition hover:opacity-90"
                                                                                style="background-color:#d98200;">Simpan Perubahan</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>

                                                {{-- Tombol Hapus --}}
                                                <form method="POST" action="{{ route('siswa.absensi.destroy', $a->id) }}"
                                                      onsubmit="return confirm('Yakin ingin menghapus data absensi ini?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded-lg bg-[#cf202f] px-3 py-1.5 text-xs font-bold text-white transition hover:opacity-90">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        @endif

                                        {{-- ===== Aksi validasi / pengajuan (kode lama, tidak diubah) ===== --}}
                                        @if($sv === 'disetujui')
                                            <span class="text-xs font-bold text-[#05b169]">Tervalidasi</span>
                                        @elseif($sv === 'diajukan')
                                            <span class="text-xs font-bold text-[#d98200]">Menunggu Validasi</span>
                                        @else
                                            <div x-data="{ openAjukan: false }" class="inline-block" x-on:keydown.escape.window="openAjukan = false">
                                                <button type="button" @click="openAjukan = true"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                    Ajukan
                                                </button>
                                                <template x-teleport="body">
                                                    <div x-show="openAjukan" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                                         role="dialog" aria-modal="true">
                                                        <div class="absolute inset-0 bg-black/50" @click="openAjukan = false"></div>
                                                        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl text-left"
                                                             x-data="{ fileName: '', pilih(event){ const file = event.target.files[0]; if(!file) return; const dt = new DataTransfer(); dt.items.add(file); this.$refs.finalInput.files = dt.files; this.fileName = file.name; } }">
                                                            <div class="flex items-center justify-between mb-4">
                                                                <h3 class="text-lg font-bold text-black">Ajukan Bukti Fisik</h3>
                                                                <button type="button" @click="openAjukan = false" aria-label="Tutup" class="text-[#5b616e] hover:text-black">&times;</button>
                                                            </div>
                                                            <p class="text-xs text-[#5b616e] mb-4">
                                                                Cetak dokumen, minta paraf/tanda tangan instruktur, lalu unggah foto buktinya untuk divalidasi guru pembimbing.
                                                            </p>
                                                            <form method="POST" action="{{ route('siswa.absensi.ajukan', $a->id) }}" enctype="multipart/form-data" class="space-y-4"
                                                                  @submit="if(!$refs.finalInput.files.length){ $event.preventDefault(); alert('Silakan pilih foto bukti terlebih dahulu.'); }">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="file" name="foto_bukti" accept="image/*" x-ref="finalInput" class="hidden">
                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Foto Bukti Fisik</label>
                                                                    <div class="flex gap-2">
                                                                        <button type="button" @click="$refs.kamera.click()"
                                                                                class="flex-1 inline-flex items-center justify-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                                            Kamera
                                                                        </button>
                                                                        <button type="button" @click="$refs.galeri.click()"
                                                                                class="flex-1 inline-flex items-center justify-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                                            Galeri
                                                                        </button>
                                                                    </div>
                                                                    <input type="file" accept="image/*" capture="environment" x-ref="kamera" class="hidden" @change="pilih($event)">
                                                                    <input type="file" accept="image/*" x-ref="galeri" class="hidden" @change="pilih($event)">
                                                                    <p class="mt-2 text-xs font-semibold text-[#05b169]" x-show="fileName" x-cloak>
                                                                        Terpilih: <span x-text="fileName"></span>
                                                                    </p>
                                                                </div>

                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Catatan Instruktur</label>
                                                                    <textarea name="catatan_instruktur" rows="3" required
                                                                              placeholder="Tuliskan catatan/keterangan dari instruktur..."
                                                                              class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">{{ old('catatan_instruktur') }}</textarea>
                                                                </div>

                                                                <div class="flex justify-end gap-2 pt-2">
                                                                    <button type="button" @click="openAjukan = false"
                                                                            class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Batal</button>
                                                                    <button type="submit"
                                                                            class="rounded-xl bg-[#0047d6] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#0038aa]">Ajukan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">
                                    Belum ada data kehadiran.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($absensis, 'links'))
                <div class="mt-4">
                     {{ $absensis->withQueryString()->links() }} 
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Daftar Hadir PKL Saya</h2>
        </div>
    </x-slot>

    <style>
        [x-cloak]{display:none!important;}
        .abs-desktop{ display:none; }
        .abs-mobile { display:block; }
        @media (min-width:1024px){
            .abs-desktop{ display:block; }
            .abs-mobile { display:none; }
        }
    </style>

    @php
        $statusUsulan = $siswa->status_jam_usulan ?? 'none';
        $jamMasukEfektif  = $siswa->jamMasukEfektif();
        $jamPulangEfektif = $siswa->jamPulangEfektif();
        $pakaiKhusus = $siswa->pakaiJamKhusus();
    @endphp

    <div class="py-8 md:py-12 bg-white min-h-screen"
         x-data="absensiPage({
            jamMasuk: '{{ $jendela['jam_masuk'] }}',
            jamPulang: '{{ $jendela['jam_pulang'] }}',
            durasi: {{ (int) $jendela['durasi'] }},
            nowJam: '{{ $jendela['now']->format('H:i') }}',
            faseServer: '{{ $jendela['fase'] }}',
            terbukaServer: {{ $jendela['terbuka'] ? 'true' : 'false' }},
            paksa: {{ ($jendela['paksa'] ?? false) ? 'true' : 'false' }},
            sudahHadir: {{ ($absensiHariIni && $absensiHariIni->status === 'Hadir' && $absensiHariIni->jam_masuk) ? 'true' : 'false' }},
            sudahPulang: {{ ($absensiHariIni && $absensiHariIni->jam_pulang) ? 'true' : 'false' }}
         })"
         x-init="mulai()"
         x-on:keydown.escape.window="openAbsen=false; openJam=false">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12">

            <div class="mb-6">
                <a href="{{ route('siswa.dashboard') }}"
                   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- ===================== REKAP ===================== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="rounded-2xl border-2 border-[#05b169]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-xl sm:text-2xl md:text-3xl font-black text-[#05b169]">{{ $rekap['Hadir'] ?? 0 }}</p>
                    <p class="text-xs sm:text-sm font-bold text-black mt-1">Hadir</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-xl sm:text-2xl md:text-3xl font-black text-[#0047d6]">{{ $rekap['Izin'] ?? 0 }}</p>
                    <p class="text-xs sm:text-sm font-bold text-black mt-1">Izin</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-xl sm:text-2xl md:text-3xl font-black text-[#d98200]">{{ $rekap['Sakit'] ?? 0 }}</p>
                    <p class="text-xs sm:text-sm font-bold text-black mt-1">Sakit</p>
                </div>
                <div class="rounded-2xl border-2 border-[#cf202f]/40 bg-white p-4 text-center shadow-sm">
                    <p class="text-xl sm:text-2xl md:text-3xl font-black text-[#cf202f]">{{ $rekap['Alpha'] ?? 0 }}</p>
                    <p class="text-xs sm:text-sm font-bold text-black mt-1">Alpha</p>
                </div>
            </div>

            {{-- ===================== PENGATURAN JAM KERJA (USULAN KE GURU) ===================== --}}
            <div class="mb-6 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Jam Kerja Industri</h3>
                        <p class="text-xs font-medium text-[#5b616e] mt-0.5">
                            Jam dari admin: <span class="font-bold text-black">{{ substr($jamAdmin['masuk'],0,5) }} – {{ substr($jamAdmin['pulang'],0,5) }} WITA</span>.
                            Bila tidak sesuai template industri Anda, ajukan jam khusus ke guru pembimbing.
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-bold">
                            <span class="rounded-full bg-[#0047d6]/10 px-3 py-1 text-[#0047d6]">Jam berlaku: {{ substr($jamMasukEfektif,0,5) }} – {{ substr($jamPulangEfektif,0,5) }}</span>
                            @if($statusUsulan === 'diajukan')
                                <span class="rounded-full bg-[#d98200]/15 px-3 py-1 text-[#d98200]">Usulan menunggu validasi guru</span>
                            @elseif($statusUsulan === 'disetujui' && $pakaiKhusus)
                                <span class="rounded-full bg-[#05b169]/15 px-3 py-1 text-[#05b169]">Jam khusus disetujui</span>
                            @else
                                <span class="rounded-full bg-[#5b616e]/15 px-3 py-1 text-[#5b616e]">Mengikuti jam admin</span>
                            @endif
                        </div>
                        @if($statusUsulan === 'diajukan')
                            <p class="mt-2 text-xs font-medium text-[#5b616e]">Diajukan: {{ substr($siswa->jam_masuk_usulan,0,5) }} – {{ substr($siswa->jam_pulang_usulan,0,5) }}
                                @if($siswa->catatan_jam_usulan) · “{{ $siswa->catatan_jam_usulan }}” @endif</p>
                        @endif
                    </div>
                    <div>
                        <button type="button" @click="openJam=true"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pengaturan Jam
                        </button>
                    </div>
                </div>

                {{-- MODAL: Ajukan Jam Khusus --}}
                <template x-teleport="body">
                    <div x-show="openJam" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
                        <div class="absolute inset-0 bg-black/50" @click="openJam=false"></div>
                        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-black">Ajukan Jam Kerja Industri</h3>
                                <button type="button" @click="openJam=false" class="text-[#5b616e] hover:text-black text-xl leading-none">&times;</button>
                            </div>
                            <p class="mb-4 text-xs font-medium text-[#5b616e]">Usulan dikirim ke guru pembimbing untuk divalidasi. Selama menunggu, Anda tetap memakai jam yang berlaku sekarang.</p>
                            <form method="POST" action="{{ route('siswa.absensi.jam.ajukan') }}" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Masuk</label>
                                        <x-jam-picker name="jam_masuk_usulan" :value="substr($siswa->jam_masuk_usulan ?? $jamMasukEfektif,0,5)" required />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jam Pulang</label>
                                        <x-jam-picker name="jam_pulang_usulan" :value="substr($siswa->jam_pulang_usulan ?? $jamPulangEfektif,0,5)" required />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Catatan (opsional)</label>
                                    <textarea name="catatan_jam_usulan" rows="2" placeholder="Contoh: jam kerja industri 07:30 - 15:30"
                                              class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">{{ $siswa->catatan_jam_usulan }}</textarea>
                                </div>
                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="openJam=false" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Batal</button>
                                    <button type="submit" class="rounded-xl bg-[#0047d6] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#0038aa]">Ajukan ke Guru</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ===================== MENU ABSENSI ===================== --}}
            <div class="mb-6 rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm">
                <div class="mb-4">
                    <h3 class="text-lg font-bold tracking-tight text-black">Menu Absensi</h3>
                    <p class="text-xs font-medium text-[#5b616e]">Halaman absensi hanya dibuka <span class="font-bold">{{ (int) $jendela['durasi'] }} menit</span> saat jam masuk (pukul {{ substr($jamMasukEfektif,0,5) }}) lalu tertutup sampai jam pulang (pukul {{ substr($jamPulangEfektif,0,5) }}). Jika tidak absen sampai batas waktu, status otomatis <span class="font-bold text-[#cf202f]">Alpha</span>.</p>
                </div>

                <div class="mb-4 rounded-xl border-2 px-4 py-3" :class="terbuka ? 'border-[#05b169] bg-[#05b169]/10' : 'border-[#cf202f] bg-[#cf202f]/10'">
                    <div class="flex items-center gap-2 text-sm font-bold" :class="terbuka ? 'text-[#05b169]' : 'text-[#cf202f]'">
                        <span class="inline-block h-2.5 w-2.5 rounded-full" :class="terbuka ? 'bg-[#05b169]' : 'bg-[#cf202f]'"></span>
                        <span x-text="statusLabel"></span>
                    </div>
                    <p class="mt-1 text-xs font-semibold" :class="terbuka ? 'text-[#05b169]' : 'text-[#cf202f]'" x-text="countdownLabel"></p>
                </div>

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <form method="GET" action="{{ route('siswa.absensi.index') }}" class="flex flex-wrap items-center gap-2">
                        <input type="month" name="bulan" value="{{ request('bulan') }}"
                               class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <button type="submit" class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Filter</button>
                        @if(request('bulan'))
                            <a href="{{ route('siswa.absensi.index') }}" class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        @endif
                    </form>

                    <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                        {{-- Tombol Absen (buka form) --}}
                        <button type="button" @click="if (terbuka) openAbsen=true" :disabled="!terbuka"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-sm transition"
                                :class="terbuka ? 'bg-[#05b169] hover:bg-[#049a5b] cursor-pointer' : 'bg-[#5b616e]/40 cursor-not-allowed'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span x-text="terbuka ? 'Absen Sekarang' : 'Absensi Tertutup'"></span>
                        </button>

                        {{-- Tombol Absen Pulang cepat (jika sudah hadir & fase pulang) --}}
                        <form method="POST" action="{{ route('siswa.absensi.store') }}" x-show="terbuka && fase==='pulang' && sudahHadir && !sudahPulang" x-cloak>
                            @csrf
                            <input type="hidden" name="aksi" value="pulang">
                            <input type="hidden" name="status" value="Hadir">
                            <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                Absen Pulang
                            </button>
                        </form>

                        <a href="{{ route('cetak.absensi', request()->only('bulan')) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:opacity-90" style="background-color:#cf202f;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Cetak Rekap PDF
                        </a>
                    </div>
                </div>

                {{-- ===== MODAL ABSEN (status + foto wajib + catatan opsional) ===== --}}
                <template x-teleport="body">
                    <div x-show="openAbsen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
                        <div class="absolute inset-0 bg-black/50" @click="openAbsen=false"></div>
                        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-black">Form Absensi Hari Ini</h3>
                                <button type="button" @click="openAbsen=false" class="text-[#5b616e] hover:text-black text-xl leading-none">&times;</button>
                            </div>
                            <form method="POST" action="{{ route('siswa.absensi.store') }}" enctype="multipart/form-data" class="space-y-4"
                                  @submit="if (!terbuka) { $event.preventDefault(); alert('Halaman absensi sudah tertutup.'); }">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Kehadiran</label>
                                    <select name="status" x-model="status"
                                            class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                        <option value="Hadir">Hadir</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Izin">Izin</option>
                                    </select>
                                    <p class="mt-1 text-[11px] font-medium text-[#5b616e]">Alpha ditetapkan otomatis oleh sistem bila Anda tidak absen sampai batas waktu.</p>
                                </div>

                                <template x-if="status==='Hadir'">
                                    <div class="rounded-xl border-2 border-[#05b169]/25 bg-[#05b169]/5 px-3 py-2.5 text-xs font-medium text-[#5b616e]">
                                        Jam <span class="font-bold text-black" x-text="fase==='pulang' ? 'pulang' : 'masuk'"></span> akan dicatat otomatis: <span class="font-bold text-black" x-text="nowJam"></span> WITA.
                                    </div>
                                </template>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                        <span x-text="labelFoto"></span> <span class="text-[#cf202f]">*</span>
                                    </label>
                                    <input type="file" name="foto_bukti" accept="image/*" required
                                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black file:mr-3 file:rounded-lg file:border-0 file:bg-[#0047d6] file:px-3 file:py-1.5 file:text-white">
                                    <p class="mt-1 text-[11px] font-medium text-[#5b616e]">Format jpg/jpeg/png, maksimal 2MB.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Catatan (opsional)</label>
                                    <textarea name="catatan_instruktur" rows="2" placeholder="Catatan tambahan (opsional)"
                                              class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button type="button" @click="openAbsen=false" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Batal</button>
                                    <button type="submit" class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049a5b]">Kirim Absensi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ===================== RIWAYAT ===================== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">
                <div class="mb-6"><h3 class="text-lg font-bold tracking-tight text-black">Riwayat Kehadiran Saya</h3></div>

                @php
                    $badgeStatus = fn($s) => match ($s) {
                        'Hadir' => 'bg-[#05b169] text-white',
                        'Izin'  => 'bg-[#0047d6] text-white',
                        'Sakit' => 'bg-[#d98200] text-white',
                        'Alpha' => 'bg-[#cf202f] text-white',
                        default => 'bg-[#5b616e] text-white',
                    };
                    $svData = fn($sv) => match ($sv) {
                        'disetujui' => ['bg-[#05b169] text-white', 'Tervalidasi'],
                        'diajukan'  => ['bg-[#d98200] text-white', 'Menunggu Validasi'],
                        default     => ['bg-[#5b616e]/15 text-[#5b616e]', 'Draft'],
                    };
                @endphp

                {{-- DESKTOP --}}
                <div class="abs-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[880px] text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center font-bold w-12">No</th>
                                <th class="px-4 py-3 text-left font-bold w-40">Tanggal</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Masuk</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Pulang</th>
                                <th class="px-4 py-3 text-center font-bold w-48">Validasi</th>
                                <th class="px-4 py-3 text-center font-bold w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($absensis as $a)
                                @php
                                    [$svBadge, $svLabel] = $svData($a->status_validasi ?? 'draft');
                                    $jamMasuk  = $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '';
                                    $jamPulang = $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '';
                                    $tglLabel  = \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y');
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $tglLabel }}</td>
                                    <td class="px-4 py-3 text-center"><span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badgeStatus($a->status) }}">{{ $a->status }}</span>@if($a->telat_masuk)<span class="mt-1 block text-[10px] font-bold text-[#d98200]">Telat Masuk</span>@endif</td>
                                    <td class="px-4 py-3 text-center font-medium text-black">{{ $jamMasuk ?: '-' }}</td>
                                    <td class="px-4 py-3 text-center font-medium text-black">{{ $jamPulang ?: '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}">{{ $svLabel }}</span>
                                        @if($a->foto_bukti)
                                            <div class="mt-1"><a href="{{ asset('storage/'.$a->foto_bukti) }}" download target="_blank" rel="noopener" class="text-xs font-bold text-[#0047d6] underline hover:text-[#0038aa]">Lihat Bukti</a></div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            @if(($a->status_validasi ?? 'draft') === 'draft')
                                                <form method="POST" action="{{ route('siswa.absensi.destroy', $a->id) }}" onsubmit="return confirm('Hapus absensi ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-bold text-white transition hover:opacity-90" style="background-color:#cf202f;">Hapus</button>
                                                </form>
                                            @else
                                                <span class="text-xs font-medium text-[#5b616e]">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-10 text-center text-sm font-medium text-[#5b616e]">Belum ada data absensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE --}}
                <div class="abs-mobile space-y-3">
                    @forelse ($absensis as $a)
                        @php
                            [$svBadge, $svLabel] = $svData($a->status_validasi ?? 'draft');
                            $jamMasuk  = $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-';
                            $jamPulang = $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '-';
                            $tglLabel  = \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y');
                        @endphp
                        <div class="rounded-xl border-2 border-[#0047d6]/15 p-4">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-black">{{ $tglLabel }}</p>
                                <div class="text-right"><span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badgeStatus($a->status) }}">{{ $a->status }}</span>@if($a->telat_masuk)<span class="mt-1 block text-[10px] font-bold text-[#d98200]">Telat Masuk</span>@endif</div>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2 text-xs font-medium text-[#5b616e]">
                                <p>Masuk: <span class="font-bold text-black">{{ $jamMasuk }}</span></p>
                                <p>Pulang: <span class="font-bold text-black">{{ $jamPulang }}</span></p>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}">{{ $svLabel }}</span>
                                @if($a->foto_bukti)<a href="{{ asset('storage/'.$a->foto_bukti) }}" download target="_blank" rel="noopener" class="text-xs font-bold text-[#0047d6] underline">Lihat Bukti</a>@endif
                            </div>
                            @if(($a->status_validasi ?? 'draft') === 'draft')
                                <form method="POST" action="{{ route('siswa.absensi.destroy', $a->id) }}" class="mt-3" onsubmit="return confirm('Hapus absensi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-full rounded-xl px-4 py-2 text-xs font-bold text-white" style="background-color:#cf202f;">Hapus</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-xl border-2 border-[#0047d6]/15 p-6 text-center text-sm font-medium text-[#5b616e]">Belum ada data absensi.</div>
                    @endforelse
                </div>

                @if (method_exists($absensis, 'links'))
                    <div class="mt-4">{{ $absensis->links() }}</div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function absensiPage(cfg) {
            const toMin = (hhmm) => {
                const [h, m] = String(hhmm).split(':').map(Number);
                return (h || 0) * 60 + (m || 0);
            };
            return {
                jamMasuk: cfg.jamMasuk,
                jamPulang: cfg.jamPulang,
                durasi: cfg.durasi,
                nowJam: cfg.nowJam,
                fase: cfg.faseServer,
                terbuka: cfg.terbukaServer,
                paksa: cfg.paksa,
                sudahHadir: cfg.sudahHadir,
                sudahPulang: cfg.sudahPulang,
                status: 'Hadir',
                openAbsen: false,
                openJam: false,
                nowMin: 0,
                statusLabel: '',
                countdownLabel: '',
                get labelFoto() {
                    return this.status === 'Hadir'
                        ? 'Foto bukti berada di tempat industri'
                        : 'Foto bukti izin/sakit';
                },
                hitung() {
                    const m = toMin(this.jamMasuk), p = toMin(this.jamPulang), d = this.durasi;
                    const n = this.nowMin;
                    let fase = 'tutup', terbuka = false;
                    if (n >= m && n <= m + d) { fase = 'masuk'; terbuka = true; }
                    else if (n >= p && n <= p + d) { fase = 'pulang'; terbuka = true; }
                    this.fase = fase; this.terbuka = terbuka;
                    const hhmm = String(Math.floor(n / 60)).padStart(2, '0') + ':' + String(n % 60).padStart(2, '0');
                    this.nowJam = hhmm;
                    if (this.paksa) {
                        // Absensi dibuka manual oleh admin: selalu terbuka, tanpa jadwal.
                        this.terbuka = true;
                        this.fase = this.sudahHadir ? 'pulang' : 'masuk';
                        this.statusLabel = 'Absensi DIBUKA oleh admin';
                        this.countdownLabel = 'Absensi dibuka manual oleh admin (bebas waktu).';
                        return;
                    }
                    if (terbuka) {
                        const sisa = (fase === 'masuk' ? (m + d) : (p + d)) - n;
                        this.statusLabel = 'Absensi TERBUKA (fase ' + (fase === 'masuk' ? 'masuk' : 'pulang') + ')';
                        this.countdownLabel = 'Sisa waktu ± ' + sisa + ' menit.';
                    } else {
                        this.statusLabel = 'Absensi TERTUTUP';
                        if (n < m) this.countdownLabel = 'Absen masuk dibuka pukul ' + this.jamMasuk + ' WITA.';
                        else if (n < p) this.countdownLabel = 'Absen pulang dibuka pukul ' + this.jamPulang + ' WITA.';
                        else this.countdownLabel = 'Absensi berikutnya dibuka besok pukul ' + this.jamMasuk + ' WITA.';
                    }
                },
                mulai() {
                    this.nowMin = toMin(this.nowJam);
                    this.hitung();
                    setInterval(() => { this.nowMin = (this.nowMin + 1) % (24 * 60); this.hitung(); }, 60000);
                },
            };
        }
    </script>
</x-app-layout>

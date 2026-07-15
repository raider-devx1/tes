<x-app-layout title="Monitoring Absensi">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Monitoring &amp; Validasi Absensi</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Validasi bukti fisik kehadiran siswa bimbingan Anda.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('cetak.absensi.semua') }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:opacity-90"
                       style="background-color:#cf202f;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Semua PDF
                    </a>
                    <a href="{{ route('guru.dashboard') }}"
                       class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="rounded-xl border-2 border-[#05b169]/40 bg-[#05b169]/5 px-4 py-3 text-sm font-semibold text-[#05b169]">
                     {{ session('success') }} 
                </div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border-2 border-[#cf202f]/40 bg-[#cf202f]/5 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                     {{ session('error') }} 
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Hadir</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]"> {{ $rekap['Hadir'] ?? 0 }} </p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/30 bg-[#0047d6]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Izin</p>
                    <p class="mt-1 text-3xl font-bold text-[#0047d6]"> {{ $rekap['Izin'] ?? 0 }} </p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sakit</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]"> {{ $rekap['Sakit'] ?? 0 }} </p>
                </div>
                <div class="rounded-2xl border-2 border-[#cf202f]/30 bg-[#cf202f]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Alpha</p>
                    <p class="mt-1 text-3xl font-bold text-[#cf202f]"> {{ $rekap['Alpha'] ?? 0 }} </p>
                </div>
            </div>

            <form method="GET" action="{{ route('guru.monitoring.absensi') }}" class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 flex flex-wrap gap-3 items-end shadow-sm">
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

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-sm text-left">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold">Tanggal</th>
                                <th class="px-4 py-3 font-bold">Nama</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Jam Masuk</th>
                                <th class="px-4 py-3 text-center font-bold w-24">Jam Pulang</th>
                                <th class="px-4 py-3 text-center font-bold w-64">Validasi</th>
                                <th class="px-4 py-3 text-center font-bold w-20">Aksi</th>
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
                                    <td class="px-4 py-3 text-center font-semibold text-black"> {{ $absensi->firstItem() + $loop->index }} </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                         {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }} 
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words"> {{ $a->siswa->name ?? '-' }} </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black"> {{ $a->siswa->nisn ?? '-' }} </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badge }}"> {{ $a->status }} </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-medium text-black"> {{ $a->jam_masuk ?? '-' }} </td>
                                    <td class="px-4 py-3 text-center font-medium text-black"> {{ $a->jam_pulang ?? '-' }} </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $svBadge }}"> {{ $svLabel }} </span>

                                            @if($a->foto_bukti)
                                                <div class="flex flex-wrap items-center justify-center gap-2">
                                                    <a href="{{ asset('storage/'.$a->foto_bukti) }}" target="_blank" rel="noopener"
                                                       class="inline-flex items-center gap-1 rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                        Lihat Bukti
                                                    </a>
                                                    <a href="{{ asset('storage/'.$a->foto_bukti) }}"
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
                                                                    <button type="button" @click="openValidasi = false" class="text-[#5b616e] hover:text-black">&times;</button>
                                                                </div>
                                                                <div class="space-y-2 text-sm text-black mb-4">
                                                                    <p><span class="font-bold">Siswa:</span> {{ $a->siswa->name ?? '-' }} </p>
                                                                    <p><span class="font-bold">Tanggal:</span> {{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }} </p>
                                                                    <p><span class="font-bold">Status:</span> {{ $a->status }} </p>
                                                                    @if($a->catatan_instruktur)
                                                                        <p><span class="font-bold">Catatan Instruktur:</span> {{ $a->catatan_instruktur }} </p>
                                                                    @endif
                                                                </div>
                                                                <p class="text-xs text-[#5b616e] mb-4">
                                                                    Pastikan bukti fisik sudah diperiksa (gunakan tombol Lihat/Download Bukti) sebelum memvalidasi.
                                                                </p>
                                                                <div class="flex justify-end gap-2">
                                                                    <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="aksi" value="tolak">
                                                                        <button type="submit"
                                                                                class="rounded-xl border-2 border-[#cf202f]/40 bg-white px-4 py-2 text-sm font-bold text-[#cf202f] transition hover:bg-[#cf202f]/5">Tolak</button>
                                                                    </form>
                                                                    <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="aksi" value="valid">
                                                                        <button type="submit"
                                                                                class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049a5b]">Valid</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            @elseif($sv === 'disetujui')
                                                <span class="inline-flex w-full items-center justify-center rounded-full bg-[#05b169]/10 px-3 py-1.5 text-xs font-bold text-[#05b169]">
                                                    Tervalidasi
                                                </span>
                                            @else
                                                <span class="text-xs font-medium text-[#5b616e]">Belum diajukan</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('cetak.absensi', $a->siswa_id) }}"
                                           target="_blank" rel="noopener"
                                           title="Cetak PDF absensi siswa ini"
                                           class="inline-flex items-center justify-center rounded-lg px-2.5 py-1.5 text-white transition hover:opacity-90"
                                           style="background-color:#cf202f;">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
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
            </div>

            <div class="mt-4">
                {!! $absensi->links() !!}
            </div>
        </div>
    </div>
</x-app-layout>
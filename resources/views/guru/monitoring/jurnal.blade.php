<x-app-layout title="Monitoring Jurnal">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Monitoring & Validasi Jurnal Siswa</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Pantau jurnal siswa bimbingan Anda dan lakukan validasi bukti fisik.</p>
                </div>
                <a href="{{ route('guru.dashboard') }}"
                   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    Kembali ke Dashboard
                </a>
            </div>

            @if(session('success'))
                <div class="rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                     {{ session('success') }}  
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                     {{ session('error') }}  
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Jurnal</p>
                    <p class="mt-1 text-2xl font-bold text-black">  {{ $rekap['total'] ?? 0 }}  </p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Disetujui</p>
                    <p class="mt-1 text-2xl font-bold text-[#05b169]">  {{ $rekap['disetujui'] ?? 0 }}  </p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Diajukan</p>
                    <p class="mt-1 text-2xl font-bold text-[#d98200]">  {{ $rekap['diajukan'] ?? 0 }}  </p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Draft</p>
                    <p class="mt-1 text-2xl font-bold text-[#5b616e]">  {{ $rekap['draft'] ?? 0 }}  </p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-black">Jurnal Kegiatan Siswa Bimbingan</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak jurnal sesuai
                        <span class="font-bold text-black">filter tanggal</span> di bawah. Bila tanggal dikosongkan, otomatis mencetak jurnal <span class="font-bold text-black">hari ini</span> (1 siswa per halaman).
                    </p>
                </div>

                <a href="{{ route('cetak.jurnal.semua') }}" target="_blank"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                    </svg>
                    Cetak Semua PDF
                </a>
            </div>

            <form method="GET" action="{{ route('guru.monitoring.jurnal') }}"
                  class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 flex flex-wrap gap-3 items-end shadow-sm">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                    <select name="status"
                            class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua</option>
                        <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                        <option value="diajukan" @selected(request('status') === 'diajukan')>Diajukan</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                           class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                <a href="{{ route('guru.monitoring.jurnal') }}"
                   class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
            </form>

            <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full min-w-[1200px] text-sm text-left table-fixed">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                            <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                            <th class="px-4 py-3 font-bold w-40">Nama</th>
                            <th class="px-4 py-3 font-bold w-28">NISN</th>
                            <th class="px-4 py-3 font-bold w-[24%]">Unit Kerja</th>
                            <th class="px-4 py-3 font-bold w-[18%]">Catatan Instruktur</th>
                            <th class="px-4 py-3 font-bold w-32">Foto Kegiatan</th>
                            <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                            <th class="px-4 py-3 text-center font-bold w-44">Validasi</th>
                            <th class="px-4 py-3 text-center font-bold w-24">Cetak</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($jurnals as $jurnal)
                            @php
                                $badgeStatus = match($jurnal->status) {
                                    'disetujui' => 'bg-[#05b169] text-white',
                                    'diajukan'  => 'bg-[#d98200] text-white',
                                    'draft'     => 'bg-[#5b616e] text-white',
                                    default     => 'bg-[#5b616e] text-white',
                                };
                                $labelStatus = match($jurnal->status) {
                                    'disetujui' => 'Disetujui',
                                    'diajukan'  => 'Diajukan',
                                    'draft'     => 'Draft',
                                    default     => ucfirst((string) $jurnal->status),
                                };
                                $items = $jurnal->items;
                                $fotos = $jurnal->items->whereNotNull('dokumentasi')->values();
                            @endphp
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black">  {{ $jurnals->firstItem() + $loop->index }}  </td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">  {{ $jurnal->hari_tanggal->format('d/m/Y') }}  </td>
                                <td class="px-4 py-3 font-bold text-black break-words">  {{ $jurnal->siswa->name ?? '-' }}  </td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">  {{ $jurnal->siswa->nisn ?? '-' }}  </td>

                                <td class="px-4 py-3 text-black break-words">
                                    @if($items->count())
                                        <div x-data="{ open: false }">
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words">  {{ $items->first()->unit_kerja }}  </span>
                                            </div>

                                            @if($items->count() > 1)
                                                <button type="button" @click="open = !open"
                                                        class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                    <span x-show="!open">+  {{ $items->count() - 1 }}  unit kerja lainnya</span>
                                                    <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                         stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>

                                                <ol start="2" x-show="open" x-cloak x-transition
                                                    class="mt-2 list-decimal list-inside space-y-0.5 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                    @foreach($items->slice(1) as $it)
                                                        <li class="break-words">  {{ $it->unit_kerja }}  </li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-black break-words">
                                    @if($jurnal->catatan_instruktur)
                                        <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-xs font-medium italic text-black">
                                              {{ $jurnal->catatan_instruktur }}  
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($fotos->count())
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($fotos as $k => $it)
                                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                                    <span class="text-xs font-semibold text-black">Foto  {{ $k + 1 }}  </span>
                                                    <a href="{{ asset('storage/'.$it->dokumentasi) }}" target="_blank"
                                                       class="inline-flex items-center rounded-full bg-[#0047d6] px-2.5 py-1 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                        Lihat
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sm text-[#5b616e]">Tidak ada</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-bold  {{ $badgeStatus }} ">  {{ $labelStatus }}  </span>
                                </td>

                                <!-- ===================== VALIDASI (TOMBOL DIPISAH) ===================== -->
                                <td class="px-4 py-3 text-center">
                                    <div x-data="{ openValidasi: false }" class="flex flex-col items-center gap-1.5">

                                        @if($jurnal->foto_bukti)
                                            @php $extBukti = pathinfo($jurnal->foto_bukti, PATHINFO_EXTENSION); @endphp

                                            <!-- Lihat Bukti: buka foto di TAB BARU -->
                                            <a href="{{ asset('storage/'.$jurnal->foto_bukti) }}" target="_blank" rel="noopener"
                                               class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                Lihat Bukti
                                            </a>

                                            <!-- Download Bukti -->
                                            <a href="{{ asset('storage/'.$jurnal->foto_bukti) }}"
                                               download="bukti-jurnal-{{ $jurnal->siswa->nisn ?? $jurnal->id }}-{{ $jurnal->id . '.' . $extBukti }}"
                                               class="inline-flex w-full items-center justify-center gap-1 rounded-full border-2 border-[#0047d6] bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                Download Bukti
                                            </a>
                                        @endif

                                        <!-- Tombol Validasi / status -->
                                        @if($jurnal->status === 'diajukan')
                                            <button type="button" @click="openValidasi = true"
                                                    class="inline-flex w-full items-center justify-center gap-1 rounded-full bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049a5b]">
                                                Validasi
                                            </button>
                                        @elseif($jurnal->status === 'disetujui')
                                            <span class="inline-flex w-full items-center justify-center rounded-full bg-[#05b169]/10 px-3 py-1.5 text-xs font-bold text-[#05b169]">
                                                Tervalidasi
                                            </span>
                                        @else
                                            <span class="text-xs font-medium text-[#5b616e]">Belum diajukan</span>
                                        @endif

                                        <!-- Modal Validasi (TANPA gambar, hanya konfirmasi Valid/Tolak) -->
                                        @if($jurnal->status === 'diajukan')
                                            <div x-show="openValidasi" x-cloak
                                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                                 @keydown.escape.window="openValidasi = false">
                                                <div class="w-full max-w-md rounded-2xl bg-white shadow-xl text-left"
                                                     @click.outside="openValidasi = false">

                                                    <div class="flex items-center justify-between border-b-2 border-[#0047d6]/15 px-5 py-3">
                                                        <h3 class="text-base font-bold text-black">
                                                            Validasi Jurnal —  {{ $jurnal->siswa->name ?? '-' }}  
                                                        </h3>
                                                        <button type="button" @click="openValidasi = false"
                                                                class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                                    </div>

                                                    <div class="space-y-3 px-5 py-4">
                                                        <p class="text-sm font-medium text-black">
                                                            Jurnal tanggal <span class="font-bold"> {{ $jurnal->hari_tanggal->format('d/m/Y') }} </span>.
                                                            Pastikan Anda telah memeriksa bukti fisik melalui tombol
                                                            <span class="font-bold text-[#0047d6]">Lihat Bukti</span> sebelum menyetujui.
                                                        </p>

                                                        @if($jurnal->catatan_instruktur)
                                                            <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-3">
                                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Catatan Instruktur</p>
                                                                <p class="text-sm font-medium text-black">  {{ $jurnal->catatan_instruktur }}  </p>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="flex justify-end gap-2 border-t-2 border-[#0047d6]/15 px-5 py-3">
                                                        <form action="{{ route('guru.jurnal.validasi', $jurnal->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="aksi" value="tolak">
                                                            <button type="submit"
                                                                class="rounded-xl bg-[#cf202f]/10 px-4 py-2 text-sm font-bold text-[#cf202f] hover:bg-[#cf202f]/20">
                                                                Tolak
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('guru.jurnal.validasi', $jurnal->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="aksi" value="valid">
                                                            <button type="submit"
                                                                class="rounded-xl bg-[#05b169] px-5 py-2 text-sm font-bold text-white hover:bg-[#049a5b]">
                                                                Valid (Setujui)
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- ===================== CETAK ===================== -->
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cetak.jurnal', $jurnal->id) }}" target="_blank"
                                       class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                          {{ $jurnal->status === 'disetujui' ? 'PDF Final' : 'Cetak Draf' }}  
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada jurnal yang diisi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {!! $jurnals->links() !!}
            </div>

        </div>
    </div>
</x-app-layout>
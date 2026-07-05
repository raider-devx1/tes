<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Dokumen Siswa Bimbingan</h2>
            <a href="{{ route('guru.dashboard') }}"
           class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
            Kembali ke Dashboard
        </a>
        </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm font-medium text-[#5b616e]">Lihat &amp; unduh dokumen siswa bimbingan Anda sesuai hak akses.</p>

            <!-- ===== CARD REKAP INFORMASI (LAYOUT UTAMA) ===== -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Lengkap</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['lengkap'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sebagian</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['sebagian'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#cf202f]/30 bg-[#cf202f]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Belum</p>
                    <p class="mt-1 text-3xl font-bold text-[#cf202f]">{{ $rekap['belum'] }}</p>
                </div>
            </div>

            @php
                $suratTugas   = \App\Models\Pengaturan::ambil('surat_tugas');
                $aturanST     = \App\Models\Dokumen::ATURAN['surat_tugas'];
                $bolehLihatST = in_array(auth()->user()->role, $aturanST['lihat'], true);
                $bolehUnduhST = in_array(auth()->user()->role, $aturanST['download'], true);
            @endphp

            <!-- ===== KARTU SURAT TUGAS ===== -->
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-base font-bold text-black">Surat Tugas PKL</h3>
                        <p class="text-xs font-medium text-[#5b616e] mt-1">Berkas resmi dari Admin — berlaku sebagai acuan untuk <strong class="text-black">semua</strong> siswa bimbingan.</p>
                        @if($suratTugas)
                            <span class="inline-block mt-2 text-xs font-bold text-[#05b169]">● Tersedia</span>
                        @else
                            <span class="inline-block mt-2 text-xs font-semibold text-[#a8acb3]">○ Belum diunggah Admin</span>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        @if($suratTugas && $bolehLihatST)
                            <a href="{{ route('dokumen.surat-tugas.lihat') }}" target="_blank"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Lihat</a>
                        @endif
                        @if($suratTugas && $bolehUnduhST)
                            <a href="{{ route('dokumen.surat-tugas.download') }}"
                               class="inline-flex items-center rounded-xl bg-[#0047d6] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Download</a>
                        @endif
                        @if(!$suratTugas)
                            <span class="text-xs font-medium text-[#a8acb3] italic self-center">Menunggu unggahan Admin</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ===== FORM FILTER ===== -->
            <form method="GET" action="{{ route('guru.dokumen.index') }}" class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 flex flex-wrap gap-3 items-end shadow-sm">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari siswa</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama / NISN"
                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Dokumen</label>
                    <select name="status"
                            class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua</option>
                        <option value="lengkap" @selected(request('status') === 'lengkap')>Lengkap</option>
                        <option value="sebagian" @selected(request('status') === 'sebagian')>Sebagian</option>
                        <option value="belum" @selected(request('status') === 'belum')>Belum</option>
                    </select>
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                <a href="{{ route('guru.dokumen.index') }}"
                   class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
            </form>

            <!-- ===== TABEL DATA ===== -->
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm text-left">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold">Nama</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 font-bold w-28">Kelas</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Status</th>
                                <th class="px-4 py-3 font-bold">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse($siswa as $s)
                                @php
                                    $d = $s->dokumen;
                                    $punyaLaporan = $d && $d->laporan_akhir;
                                    $punyaSurat   = $d && $d->surat_penerimaan;
                                    if ($punyaLaporan && $punyaSurat) {
                                        $stLabel = 'Lengkap';
                                        $stClass = 'bg-[#05b169] text-white';
                                    } elseif ($punyaLaporan || $punyaSurat) {
                                        $stLabel = 'Sebagian';
                                        $stClass = 'bg-[#d98200] text-white';
                                    } else {
                                        $stLabel = 'Belum';
                                        $stClass = 'bg-[#cf202f] text-white';
                                    }
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $s->name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $s->nisn ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-black">{{ $s->kelas ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $stClass }}">{{ $stLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @include('partials.dokumen-aksi', ['siswa' => $s, 'exclude' => ['surat_tugas']])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada siswa bimbingan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== PAGINATION ===== -->
            <div class="mt-2">
                {!! $siswa->links() !!}
            </div>
        </div>
    </div>
</x-app-layout>
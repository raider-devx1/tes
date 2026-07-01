<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">Dokumen Siswa Bimbingan</h2>
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                &larr; Kembali
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-[#5b616e]">Lihat &amp; unduh dokumen siswa bimbingan Anda sesuai hak akses.</p>

            @php
                $suratTugas   = \App\Models\Pengaturan::ambil('surat_tugas');
                $aturanST     = \App\Models\Dokumen::ATURAN['surat_tugas'];
                $bolehLihatST = in_array(auth()->user()->role, $aturanST['lihat'], true);
                $bolehUnduhST = in_array(auth()->user()->role, $aturanST['download'], true);
            @endphp

            {{-- ===== KARTU SURAT TUGAS ===== --}}
            <div class="rounded-2xl border border-[#dee1e6] bg-white p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="font-semibold text-[#0a0b0d]">Surat Tugas PKL</h3>
                        <p class="text-xs text-[#7c828a] mt-1">Berkas resmi dari Admin — berlaku sebagai acuan untuk <strong class="text-[#5b616e]">semua</strong> siswa bimbingan.</p>
                        @if($suratTugas)
                            <span class="inline-block mt-2 text-xs font-semibold text-[#05b169]">● Tersedia</span>
                        @else
                            <span class="inline-block mt-2 text-xs text-[#a8acb3]">○ Belum diunggah Admin</span>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        @if($suratTugas && $bolehLihatST)
                            <a href="{{ route('dokumen.surat-tugas.lihat') }}" target="_blank"
                               class="inline-flex items-center rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Lihat</a>
                        @endif
                        @if($suratTugas && $bolehUnduhST)
                            <a href="{{ route('dokumen.surat-tugas.download') }}"
                               class="inline-flex items-center rounded-full bg-[#0052ff] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#003ecc]">Download</a>
                        @endif
                        @if(!$suratTugas)
                            <span class="text-xs text-[#a8acb3] italic self-center">Menunggu unggahan Admin</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===== KARTU FILTER ===== --}}
            <form method="GET" action="{{ route('guru.dokumen.index') }}" class="rounded-2xl border border-[#dee1e6] bg-white p-5 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Cari siswa</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama / NISN"
                           class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status Dokumen</label>
                    <select name="status"
                            class="rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                        <option value="">Semua</option>
                        <option value="lengkap" @selected(request('status') === 'lengkap')>Lengkap</option>
                        <option value="sebagian" @selected(request('status') === 'sebagian')>Sebagian</option>
                        <option value="belum" @selected(request('status') === 'belum')>Belum</option>
                    </select>
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">Filter</button>
                <a href="{{ route('guru.dokumen.index') }}"
                   class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Reset</a>
            </form>

            {{-- ===== KARTU TABEL ===== --}}
            <div class="rounded-2xl border border-[#dee1e6] bg-white overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Nama</th>
                                <th class="px-4 py-3 font-semibold">NISN</th>
                                <th class="px-4 py-3 font-semibold">Kelas</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                                <th class="px-4 py-3 font-semibold">Dokumen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse($siswa as $s)
                                @php
                                    $d = $s->dokumen;
                                    $punyaLaporan = $d && $d->laporan_akhir;
                                    $punyaSurat   = $d && $d->surat_penerimaan;
                                    if ($punyaLaporan && $punyaSurat) { 
                                        $stLabel = 'Lengkap';  
                                        $stClass = 'bg-[#05b169]/10 text-[#05b169]'; 
                                    } elseif ($punyaLaporan || $punyaSurat) { 
                                        $stLabel = 'Sebagian'; 
                                        $stClass = 'bg-[#f4b000]/10 text-[#f4b000]'; 
                                    } else { 
                                        $stLabel = 'Belum'; 
                                        $stClass = 'bg-[#cf202f]/10 text-[#cf202f]'; 
                                    }
                                @endphp
                                <tr class="align-top transition hover:bg-[#f7f7f7]">
                                    <td class="px-4 py-3 text-center text-[#7c828a]">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#0a0b0d]">{{ $s->name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">{{ $s->nisn ?? '-' }}</td>
                                    <td class="px-4 py-3 text-[#5b616e]">{{ $s->kelas ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $stClass }}">{{ $stLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @include('partials.dokumen-aksi', ['siswa' => $s, 'exclude' => ['surat_tugas']])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-[#a8acb3] italic">Belum ada siswa bimbingan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div class="mt-2">
                {!! $siswa->links() !!}
            </div>
        </div>
    </div>
</x-app-layout>
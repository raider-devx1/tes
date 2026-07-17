<x-app-layout title="Monitoring Jurnal Kegiatan">
    <style>
        [x-cloak]{display:none!important;}
        /* ===== Pergantian tampilan berbasis lebar layar ===== */
        .jrn-desktop{ display:none; }   /* HP: tabel lengkap disembunyikan */
        .jrn-mobile { display:block; }  /* HP: tabel ringkas tampil */
        @media (min-width:1024px){      /* laptop & PC (>=1024px) */
            .jrn-desktop{ display:block; }
            .jrn-mobile { display:none; }
        }
    </style>

    {{-- ============================================================= --}}
    {{-- min 360px  •  max 1920px  •  full kanan-kiri                   --}}
    {{-- ============================================================= --}}
    <div x-data="jurnalCrud()"
         x-effect="document.body.style.overflow = (open || hapusOpen || detailOpen || validasiOpen) ? 'hidden' : ''"
         class="py-6 md:py-8 bg-white">
        <div class="w-full max-w-[1920px] mx-auto space-y-6 px-4 sm:px-6 lg:px-8 2xl:px-12">

            {{-- ===================== HEADER ===================== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Monitoring Jurnal Kegiatan Siswa</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Kelola seluruh jurnal kegiatan siswa PKL (tambah, ubah, hapus, ubah status, validasi, cetak).</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="tambah()"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Jurnal
                    </button>
                    <button type="button" onclick="history.back()"
                            class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                        Kembali
                    </button>
                </div>
            </div>

            {{-- ===================== FLASH & ERROR ===================== --}}
            @if (session('success'))
                <div class="rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ===================== KARTU REKAP ===================== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Jurnal</p>
                    <p class="mt-1 text-2xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Disetujui</p>
                    <p class="mt-1 text-2xl font-bold text-[#05b169]">{{ $rekap['disetujui'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Diajukan</p>
                    <p class="mt-1 text-2xl font-bold text-[#d98200]">{{ $rekap['diajukan'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Draft</p>
                    <p class="mt-1 text-2xl font-bold text-[#5b616e]">{{ $rekap['draft'] }}</p>
                </div>
            </div>

            {{-- ===================== CETAK SEMUA ===================== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-black">Jurnal Kegiatan Seluruh Siswa</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak jurnal sesuai
                        <span class="font-bold text-black">filter tanggal</span>. Bila tanggal dikosongkan, otomatis mencetak jurnal <span class="font-bold text-black">hari ini</span> (1 siswa per halaman).
                    </p>
                </div>
                <a href="{{ route('cetak.jurnal.semua', ['tanggal' => request('tanggal')]) }}" target="_blank"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                    </svg>
                    Cetak Semua PDF
                </a>
            </div>

            {{-- ===================== FILTER ===================== --}}
            <form method="GET" action="{{ route('admin.monitoring.jurnal') }}"
                  class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 flex flex-wrap gap-3 items-end shadow-sm">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Kelas</label>
                    <select name="kelas" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $opsiKelas)
                            <option value="{{ $opsiKelas }}" @selected(request('kelas') === $opsiKelas)>{{ $opsiKelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jurusan</label>
                    <select name="jurusan" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList as $opsiJurusan)
                            <option value="{{ $opsiJurusan }}" @selected(request('jurusan') === $opsiJurusan)>{{ $opsiJurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                    <select name="status" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="diajukan" @selected(request('status') === 'diajukan')>Diajukan</option>
                        <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                           class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Filter</button>
                <a href="{{ route('admin.monitoring.jurnal') }}"
                   class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
            </form>

            {{-- ============================================================= --}}
            {{-- ==========  TAMPILAN LAPTOP / PC (TABEL LENGKAP, >=1024px) == --}}
            {{-- ============================================================= --}}
            <div class="jrn-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full min-w-[1250px] text-sm text-left table-fixed">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                            <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                            <th class="px-4 py-3 font-bold w-40">Nama</th>
                            <th class="px-4 py-3 font-bold w-28">NISN</th>
                            <th class="px-4 py-3 font-bold w-[24%]">Unit Kerja</th>
                            <th class="px-4 py-3 font-bold w-[15%]">Catatan Instruktur</th>
                            <th class="px-4 py-3 font-bold w-40">Foto</th>
                            <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                            <th class="px-4 py-3 text-center font-bold w-60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($jurnal as $item)
                            @php
                                $badgeStatus = match($item->status) {
                                    'disetujui' => 'bg-[#05b169] text-white',
                                    'diajukan'  => 'bg-[#d98200] text-white',
                                    default     => 'bg-[#5b616e] text-white',
                                };
                                $labelStatus = match($item->status) {
                                    'disetujui' => 'Disetujui',
                                    'diajukan'  => 'Diajukan',
                                    default     => 'Draft',
                                };
                                $daftarPekerjaan = $item->items;
                                $daftarFoto      = $item->items->whereNotNull('dokumentasi')->values();
                                $payload = [
                                    'id'                 => $item->id,
                                    'siswa_id'           => $item->siswa_id,
                                    'nama'               => $item->siswa->name ?? '-',
                                    'nisn'               => $item->siswa->nisn ?? '-',
                                    'tanggal_label'      => optional($item->hari_tanggal)->format('d M Y'),
                                    'hari_tanggal'       => optional($item->hari_tanggal)->format('Y-m-d'),
                                    'status'             => $item->status,
                                    'status_label'       => $labelStatus,
                                    'catatan_instruktur' => $item->catatan_instruktur,
                                    'foto_bukti_url'     => $item->foto_bukti ? asset('storage/'.$item->foto_bukti) : null,
                                    'pdf_url'            => route('cetak.jurnal', ['siswa_id' => $item->siswa_id, 'jurnal_id' => $item->id]),
                                    'items'              => $item->items->map(fn($it) => [
                                        'id'                    => $it->id,
                                        'unit_kerja'            => $it->unit_kerja,
                                        'existing_dokumentasi'  => $it->dokumentasi,
                                        'dokumentasi_url'       => $it->dokumentasi ? asset('storage/'.$it->dokumentasi) : null,
                                    ])->values(),
                                ];
                            @endphp
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black">{{ $jurnal->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ optional($item->hari_tanggal)->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-bold text-black break-words">{{ $item->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $item->siswa->nisn ?? '-' }}</td>
                                <td class="px-4 py-3 text-black break-words">
                                    @if($daftarPekerjaan->count())
                                        <div x-data="{ open: false }">
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words">{{ $daftarPekerjaan->first()->unit_kerja }}</span>
                                            </div>
                                            @if($daftarPekerjaan->count() > 1)
                                                <button type="button" @click="open = !open"
                                                        class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                    <span x-show="!open">+ {{ $daftarPekerjaan->count() - 1 }} unit kerja lainnya</span>
                                                    <span x-show="open" style="display:none;">Sembunyikan</span>
                                                </button>
                                                <ol start="2" x-show="open" x-cloak x-transition
                                                    class="mt-2 list-decimal list-inside space-y-0.5 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                    @foreach($daftarPekerjaan->slice(1) as $pekerjaan)
                                                        <li class="break-words">{{ $pekerjaan->unit_kerja }}</li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-black break-words">
                                    @if($item->catatan_instruktur)
                                        <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-xs font-medium italic text-black">
                                            {{ \Illuminate\Support\Str::limit($item->catatan_instruktur, 80) }}
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>
                                {{-- === KOLOM FOTO GABUNGAN (dokumentasi + bukti fisik) === --}}
                                <td class="px-4 py-3">
                                    @if($daftarFoto->count() || $item->foto_bukti)
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($daftarFoto as $indexFoto => $pekerjaan)
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <span class="text-xs font-semibold text-black">Foto {{ $indexFoto + 1 }}</span>
                                                    <a href="{{ asset('storage/' . $pekerjaan->dokumentasi) }}" target="_blank"
                                                       class="inline-flex items-center rounded-full bg-[#0047d6] px-2.5 py-1 text-xs font-bold text-white transition hover:bg-[#0038aa]">Lihat</a>
                                                </div>
                                            @endforeach
                                            @if($item->foto_bukti)
                                                <div class="flex flex-wrap items-center gap-1.5 border-t border-[#0047d6]/10 pt-1.5">
                                                    <span class="text-xs font-semibold text-[#d98200]">Bukti Fisik</span>
                                                    <a href="{{ asset('storage/' . $item->foto_bukti) }}" target="_blank"
                                                       class="inline-flex items-center rounded-full bg-[#d98200] px-2.5 py-1 text-xs font-bold text-white transition hover:opacity-90">Lihat</a>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-[#5b616e]">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badgeStatus }}">{{ $labelStatus }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                                        <button type="button" @click='lihatDetail(@json($payload))'
                                                class="rounded-lg bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">Detail</button>
                                        <button type="button" @click='bukaValidasi(@json($payload))'
                                                class="rounded-lg bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049a5b]">Validasi</button>
                                        <button type="button" @click='edit(@json($payload))'
                                                class="rounded-lg border-2 border-[#0047d6]/30 px-3 py-1.5 text-xs font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Edit</button>
                                        <a href="{{ $payload['pdf_url'] }}" target="_blank"
                                           class="rounded-lg bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">PDF</a>
                                        <button type="button" @click="konfirmHapus('{{ route('admin.monitoring.jurnal.destroy', $item->id) }}')"
                                                class="rounded-lg border-2 border-red-200 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data jurnal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============================================================= --}}
            {{-- ==========  TAMPILAN HP (TABEL RINGKAS, <1024px)  ========== --}}
            {{-- ==========  hanya Nama + tombol Lihat Detail       ========== --}}
            {{-- ============================================================= --}}
            <div class="jrn-mobile overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 font-bold">Nama</th>
                            <th class="px-4 py-3 text-center font-bold w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($jurnal as $item)
                            @php
                                $labelStatusM = match($item->status) {
                                    'disetujui' => 'Disetujui',
                                    'diajukan'  => 'Diajukan',
                                    default     => 'Draft',
                                };
                                $payloadM = [
                                    'id'                 => $item->id,
                                    'siswa_id'           => $item->siswa_id,
                                    'nama'               => $item->siswa->name ?? '-',
                                    'nisn'               => $item->siswa->nisn ?? '-',
                                    'tanggal_label'      => optional($item->hari_tanggal)->format('d M Y'),
                                    'hari_tanggal'       => optional($item->hari_tanggal)->format('Y-m-d'),
                                    'status'             => $item->status,
                                    'status_label'       => $labelStatusM,
                                    'catatan_instruktur' => $item->catatan_instruktur,
                                    'foto_bukti_url'     => $item->foto_bukti ? asset('storage/'.$item->foto_bukti) : null,
                                    'pdf_url'            => route('cetak.jurnal', ['siswa_id' => $item->siswa_id, 'jurnal_id' => $item->id]),
                                    'items'              => $item->items->map(fn($it) => [
                                        'id'                    => $it->id,
                                        'unit_kerja'            => $it->unit_kerja,
                                        'existing_dokumentasi'  => $it->dokumentasi,
                                        'dokumentasi_url'       => $it->dokumentasi ? asset('storage/'.$it->dokumentasi) : null,
                                    ])->values(),
                                ];
                            @endphp
                            <tr class="align-middle transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 font-bold text-black break-words">{{ $item->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click='lihatDetail(@json($payloadM))'
                                            class="inline-flex items-center rounded-lg bg-[#0047d6] px-3 py-2 text-xs font-bold text-white transition hover:bg-[#0038aa]">Lihat Detail</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data jurnal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ===================== PAGINATION ===================== --}}
            <div>{{ $jurnal->links() }}</div>
        </div>

        {{-- ===================================================================== --}}
        {{-- ===================== MODAL TAMBAH / EDIT =========================== --}}
        {{-- ===================================================================== --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
             @keydown.escape.window="open = false">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-5 sm:p-6 shadow-xl"
                 @click.outside="open = false">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-black" x-text="mode === 'create' ? 'Tambah Jurnal' : 'Edit Jurnal'"></h3>
                    <button type="button" @click="open = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                </div>
                <form :action="actionUrl" method="POST" enctype="multipart/form-data" @submit="simpan($event)" class="space-y-3">
                    @csrf
                    <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="siswa_id" :value="siswaCocok ? siswaCocok.id : ''">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">NISN Siswa</label>
                        <input type="text" x-model="form.nisn" placeholder="Masukkan NISN siswa"
                               class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <template x-if="siswaCocok">
                            <p class="mt-1 text-xs font-semibold text-[#05b169]">&#10003; <span x-text="siswaCocok.name"></span></p>
                        </template>
                        <template x-if="form.nisn.trim() !== '' && !siswaCocok">
                            <p class="mt-1 text-xs font-semibold text-[#cf202f]">NISN tidak cocok</p>
                        </template>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Tanggal</label>
                            <input type="date" name="hari_tanggal" x-model="form.hari_tanggal" required
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Status</label>
                            <select name="status" x-model="form.status"
                                    class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="draft">Draft</option>
                                <option value="diajukan">Diajukan</option>
                                <option value="disetujui">Disetujui</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black">Unit Kerja / Pekerjaan</label>
                            <button type="button" @click="tambahItem()"
                                    class="rounded-lg bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] hover:bg-[#0047d6]/20">Tambah unit kerja</button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(it, i) in form.items" :key="i">
                                <div class="rounded-xl border-2 border-[#0047d6]/15 p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-[#5b616e]">Pekerjaan <span x-text="i + 1"></span></span>
                                        <button type="button" @click="hapusItem(i)" x-show="form.items.length > 1"
                                                class="rounded-lg border-2 border-red-200 px-2.5 py-1 text-xs font-bold text-red-600 hover:bg-red-50">Hapus</button>
                                    </div>
                                    <input type="hidden" :name="'items[' + i + '][id]'" :value="it.id ?? ''">
                                    <input type="hidden" :name="'items[' + i + '][existing_dokumentasi]'" :value="it.existing_dokumentasi ?? ''">
                                    <textarea :name="'items[' + i + '][unit_kerja]'" x-model="it.unit_kerja" rows="2"
                                              placeholder="Contoh: Instalasi jaringan ruang server"
                                              class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Foto Dokumentasi (opsional)</label>
                                        <template x-if="it.dokumentasi_url">
                                            <a :href="it.dokumentasi_url" target="_blank" class="mb-1 inline-block text-[11px] font-bold text-[#0047d6] hover:underline">Lihat foto saat ini</a>
                                        </template>
                                        <input type="file" :name="'items[' + i + '][dokumentasi]'" accept="image/*"
                                               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef0f3] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-[#0a0b0d]">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Catatan Instruktur</label>
                        <textarea name="catatan_instruktur" x-model="form.catatan_instruktur" rows="2"
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Foto Bukti Fisik (lembar berparaf, opsional)</label>
                        <template x-if="form.foto_bukti_url">
                            <div class="mb-1 flex items-center gap-3">
                                <a :href="form.foto_bukti_url" target="_blank" class="text-[11px] font-bold text-[#0047d6] hover:underline">Lihat bukti saat ini</a>
                                <label class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#cf202f]">
                                    <input type="checkbox" name="hapus_foto_bukti" value="1"> Hapus foto
                                </label>
                            </div>
                        </template>
                        <input type="file" name="foto_bukti" accept="image/*"
                               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef0f3] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-[#0a0b0d]">
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Simpan</button>
                        <button type="button" @click="open = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- ===================== MODAL DETAIL (animasi smooth) ================= --}}
        {{-- ===================================================================== --}}
        <div x-show="detailOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
             @keydown.escape.window="detailOpen = false">
            <div x-show="detailOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl text-left"
                 @click.outside="detailOpen = false">
                <div class="flex items-center justify-between border-b-2 border-[#0047d6]/15 px-5 py-3 sticky top-0 bg-white">
                    <h3 class="text-base font-bold text-black">Detail Jurnal</h3>
                    <button type="button" @click="detailOpen = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Nama Siswa</p>
                            <p class="font-bold text-black break-words" x-text="detail.nama"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">NISN</p>
                            <p class="font-medium text-black" x-text="detail.nisn"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p>
                            <p class="font-medium text-black" x-text="detail.tanggal_label"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Status</p>
                            <span class="inline-block rounded-full px-3 py-1 text-xs font-bold"
                                  :class="detail.status === 'disetujui' ? 'bg-[#05b169] text-white' : (detail.status === 'diajukan' ? 'bg-[#d98200] text-white' : 'bg-[#5b616e] text-white')"
                                  x-text="detail.status_label"></span>
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Unit Kerja / Pekerjaan</p>
                        <template x-if="detail.items && detail.items.length">
                            <ol class="list-decimal list-inside space-y-2">
                                <template x-for="(it, i) in detail.items" :key="i">
                                    <li class="font-medium text-black break-words">
                                        <span x-text="it.unit_kerja"></span>
                                        <template x-if="it.dokumentasi_url">
                                            <a :href="it.dokumentasi_url" target="_blank"
                                               class="ml-1 inline-flex items-center rounded-full bg-[#0047d6] px-2 py-0.5 text-[11px] font-bold text-white transition hover:bg-[#0038aa]">Foto</a>
                                        </template>
                                    </li>
                                </template>
                            </ol>
                        </template>
                        <template x-if="!detail.items || !detail.items.length">
                            <p class="text-sm text-[#5b616e]">-</p>
                        </template>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Catatan Instruktur</p>
                        <template x-if="detail.catatan_instruktur">
                            <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-sm font-medium italic text-black" x-text="detail.catatan_instruktur"></div>
                        </template>
                        <template x-if="!detail.catatan_instruktur">
                            <p class="text-sm text-[#5b616e]">-</p>
                        </template>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Foto Bukti Fisik</p>
                        <template x-if="detail.foto_bukti_url">
                            <a :href="detail.foto_bukti_url" target="_blank"
                               class="inline-flex items-center gap-1 rounded-xl bg-[#d98200] px-4 py-2 text-sm font-bold text-white transition hover:opacity-90">Lihat Bukti Fisik</a>
                        </template>
                        <template x-if="!detail.foto_bukti_url">
                            <p class="text-sm text-[#5b616e]">Tidak ada</p>
                        </template>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <a :href="detail.pdf_url" target="_blank"
                           class="flex-1 min-w-[110px] text-center rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Cetak PDF</a>
                        <button type="button" @click="detailOpen = false; bukaValidasi(detail)"
                                class="flex-1 min-w-[110px] rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#049a5b]">Validasi</button>
                        <button type="button" @click="detailOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- ===================== MODAL VALIDASI (animasi smooth) ============== --}}
        {{-- ===================================================================== --}}
        <div x-show="validasiOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
             @keydown.escape.window="validasiOpen = false">
            <div x-show="validasiOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl text-left"
                 @click.outside="validasiOpen = false">
                <div class="flex items-center justify-between border-b-2 border-[#0047d6]/15 px-5 py-3">
                    <h3 class="text-base font-bold text-black">Validasi Jurnal &mdash; <span x-text="validasi.nama"></span></h3>
                    <button type="button" @click="validasiOpen = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                </div>
                <form :action="validasiUrl" method="POST" enctype="multipart/form-data" class="space-y-4 p-5">
                    @csrf
                    @method('PUT')
                    {{-- field wajib agar update tidak menghapus data --}}
                    <input type="hidden" name="siswa_id" :value="validasi.siswa_id">
                    <input type="hidden" name="hari_tanggal" :value="validasi.hari_tanggal">
                    {{-- pertahankan seluruh unit kerja yang sudah ada --}}
                    <template x-for="(it, i) in validasi.items" :key="i">
                        <div>
                            <input type="hidden" :name="'items[' + i + '][id]'" :value="it.id ?? ''">
                            <input type="hidden" :name="'items[' + i + '][unit_kerja]'" :value="it.unit_kerja">
                            <input type="hidden" :name="'items[' + i + '][existing_dokumentasi]'" :value="it.existing_dokumentasi ?? ''">
                        </div>
                    </template>

                    <div class="rounded-xl bg-[#0047d6]/5 p-3 text-sm">
                        <p class="font-semibold text-black"><span x-text="validasi.nama"></span> &middot; NISN <span x-text="validasi.nisn"></span></p>
                        <p class="text-xs font-medium text-[#5b616e]">Tanggal: <span x-text="validasi.tanggal_label"></span></p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Ubah Status</label>
                        <select name="status" x-model="validasi.status"
                                class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            <option value="draft">Draft</option>
                            <option value="diajukan">Diajukan</option>
                            <option value="disetujui">Disetujui</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Catatan Instruktur</label>
                        <textarea name="catatan_instruktur" x-model="validasi.catatan_instruktur" rows="3"
                                  placeholder="Tulis catatan/nilai dari instruktur..."
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Foto Bukti Fisik (opsional)</label>
                        <template x-if="validasi.foto_bukti_url">
                            <a :href="validasi.foto_bukti_url" target="_blank" class="mb-1 inline-block text-[11px] font-bold text-[#0047d6] hover:underline">Lihat bukti saat ini</a>
                        </template>
                        <input type="file" name="foto_bukti" accept="image/*"
                               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef0f3] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-[#0a0b0d]">
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#049a5b]">Simpan Validasi</button>
                        <button type="button" @click="validasiOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- ===================== MODAL HAPUS =================================== --}}
        {{-- ===================================================================== --}}
        <div x-show="hapusOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
             @keydown.escape.window="hapusOpen = false">
            <div x-show="hapusOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false">
                <h3 class="text-base font-bold text-black">Hapus Jurnal</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Yakin ingin menghapus jurnal ini beserta seluruh unit kerja &amp; fotonya? Tindakan ini tidak dapat dibatalkan.</p>
                <form :action="hapusUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#cf202f] px-4 py-2 text-sm font-bold text-white hover:bg-[#b01926]">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================================================================== --}}
    {{-- ===================== ALPINE COMPONENT ============================= --}}
    {{-- ===================================================================== --}}
    <script>
        window.jurnalCrud = function () {
            const daftarSiswa = @js($siswaList);
            const today = @js(date('Y-m-d'));
            const storeUrl = @js(route('admin.monitoring.jurnal.store'));
            const baseUrl = @js(url('admin/monitoring/jurnal'));

            const kosong = () => ({
                id: null, nisn: '', hari_tanggal: today, status: 'draft',
                catatan_instruktur: '', foto_bukti_url: null,
                items: [{ id: null, unit_kerja: '', existing_dokumentasi: '', dokumentasi_url: null }],
            });

            return {
                // ---- state ----
                open: false,
                mode: 'create',
                form: kosong(),
                hapusOpen: false,
                hapusUrl: '',
                detailOpen: false,
                detail: {},
                validasiOpen: false,
                validasi: {
                    id: null, siswa_id: null, nama: '', nisn: '', tanggal_label: '',
                    hari_tanggal: '', status: 'diajukan', catatan_instruktur: '',
                    foto_bukti_url: null, items: [],
                },

                // ---- computed ----
                get siswaCocok() {
                    const nisn = String(this.form.nisn || '').trim();
                    if (!nisn) return null;
                    return daftarSiswa.find(s => String(s.nisn).trim() === nisn) || null;
                },
                get actionUrl() { return this.mode === 'create' ? storeUrl : baseUrl + '/' + this.form.id; },
                get validasiUrl() { return this.validasi.id ? baseUrl + '/' + this.validasi.id : '#'; },

                // ---- tambah / edit ----
                tambah() { this.mode = 'create'; this.form = kosong(); this.open = true; },
                edit(d) {
                    const s = daftarSiswa.find(x => String(x.id) === String(d.siswa_id));
                    let items = Array.isArray(d.items) ? d.items.map(it => ({
                        id: it.id,
                        unit_kerja: it.unit_kerja || '',
                        existing_dokumentasi: it.existing_dokumentasi || '',
                        dokumentasi_url: it.dokumentasi_url || null,
                    })) : [];
                    if (items.length === 0) items = [{ id: null, unit_kerja: '', existing_dokumentasi: '', dokumentasi_url: null }];
                    this.mode = 'edit';
                    this.form = {
                        id: d.id,
                        nisn: s ? String(s.nisn) : String(d.nisn || ''),
                        hari_tanggal: d.hari_tanggal,
                        status: d.status || 'draft',
                        catatan_instruktur: d.catatan_instruktur || '',
                        foto_bukti_url: d.foto_bukti_url || null,
                        items: items,
                    };
                    this.detailOpen = false;
                    this.open = true;
                },
                tambahItem() { this.form.items.push({ id: null, unit_kerja: '', existing_dokumentasi: '', dokumentasi_url: null }); },
                hapusItem(i) { this.form.items.splice(i, 1); },
                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },

                // ---- hapus ----
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },

                // ---- detail ----
                lihatDetail(d) { this.detail = d; this.detailOpen = true; },

                // ---- validasi ----
                bukaValidasi(d) {
                    this.validasi = {
                        id: d.id,
                        siswa_id: d.siswa_id,
                        nama: d.nama || '',
                        nisn: d.nisn || '',
                        tanggal_label: d.tanggal_label || '',
                        hari_tanggal: d.hari_tanggal || '',
                        status: d.status === 'draft' ? 'disetujui' : (d.status || 'disetujui'),
                        catatan_instruktur: d.catatan_instruktur || '',
                        foto_bukti_url: d.foto_bukti_url || null,
                        items: Array.isArray(d.items) ? d.items.map(it => ({
                            id: it.id,
                            unit_kerja: it.unit_kerja || '',
                            existing_dokumentasi: it.existing_dokumentasi || '',
                        })) : [],
                    };
                    this.validasiOpen = true;
                },
            };
        };
    </script>
</x-app-layout>

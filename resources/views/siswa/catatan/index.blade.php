<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Catatan Kegiatan</h2>
    </x-slot>

    <style>
        [x-cloak]{display:none!important}
        /* =====================================================================
            RESPONSIVE: minimal 360px  ->  maksimal 1920px (konten full kiri-kanan)
            - < 1024px  (HP 360px & Tablet 768px) : tampil KARTU (.cat-mobile)
            - >= 1024px (Laptop 1366px & PC 1920px): tampil TABEL (.cat-desktop)
            Container memakai max-w-[1920px] agar konten melebar penuh hingga 1920px.
        ===================================================================== */
        .cat-desktop{ display:none; }   /* default: HP & Tablet -> tabel disembunyikan */
        .cat-mobile { display:block; }  /* default: HP & Tablet -> kartu tampil */
        @media (min-width:1024px){      /* laptop & PC (>=1024px) */
            .cat-desktop{ display:block; }  /* tabel tampil */
            .cat-mobile { display:none; }   /* kartu disembunyikan */
        }
    </style>

    <div class="py-8 md:py-12 bg-white">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12">

            {{-- ===== TOMBOL KEMBALI (paling atas) ===== --}}
            <div class="mb-6">
                <a href="{{ route('siswa.dashboard') }}"
                   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            {{-- ===== CARD MENU: TAMBAH & CETAK ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm mb-6">
                 <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Menu Catatan</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Tambahkan catatan baru atau cetak seluruh riwayat jurnal kamu.</p>
                    </div>
                <div class="flex flex-col sm:flex-row sm:flex-wrap sm:justify-between gap-3">
                    <a href="{{ route('siswa.catatan.create') }}"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Catatan
                    </a>
                    <a href="{{ route('cetak.catatan') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-[#0047d6] bg-white px-6 py-3.5 text-base font-bold text-[#0047d6] shadow-sm transition hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Semua (PDF)
                    </a>
                </div>
                </div>
            </div>

            {{-- ===== CARD UTAMA: FILTER + DATA ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">
                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-xl border-2 border-red-500 bg-red-500/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 rounded-xl border-2 border-red-500 bg-red-500/10 px-4 py-3 text-sm font-semibold text-black">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="GET" action="{{ route('siswa.catatan.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                               class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                        <select name="status"
                                class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            <option value="">Semua Status</option>
                            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                            <option value="diajukan" @selected(request('status') === 'diajukan')>Diajukan</option>
                            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                    <a href="{{ route('siswa.catatan.index') }}"
                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                </form>

                {{-- ============================================================= --}}
                {{-- ==========  TAMPILAN LAPTOP / PC (TABEL, >=1024px)  ========= --}}
                {{-- ============================================================= --}}
                <div class="cat-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[960px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3 font-bold w-40">Nama Pekerjaan</th>
                                <th class="px-4 py-3 font-bold w-[20%]">Perencanaan</th>
                                <th class="px-4 py-3 font-bold w-[20%]">Pelaksanaan / Hasil</th>
                                <th class="px-4 py-3 font-bold w-[16%]">Catatan Instruktur</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($catatan as $item)
                                @php
                                    $statusBadge = match($item->status) {
                                        'disetujui' => 'bg-[#05b169] text-white',
                                        'diajukan'  => 'bg-[#0047d6] text-white',
                                        default     => 'bg-[#d98200] text-white',
                                    };
                                    $statusLabel = match($item->status) {
                                        'disetujui' => 'Disetujui',
                                        'diajukan'  => 'Diajukan',
                                        default     => 'Draft',
                                    };
                                    $extBukti = $item->foto_bukti ? pathinfo($item->foto_bukti, PATHINFO_EXTENSION) : '';
                                    $tglLabel = $item->created_at->translatedFormat('d M Y');
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $loop->iteration + ($catatan->firstItem() - 1) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                        {{ $tglLabel }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $item->nama_pekerjaan }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">{{ $item->perencanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">{{ $item->pelaksanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">
                                        @if($item->catatan_instruktur)
                                            {{ $item->catatan_instruktur }}
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $statusBadge }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-stretch gap-1.5">
                                            <a href="{{ route('cetak.catatan', ['catatan_id' => $item->id]) }}" target="_blank"
                                               class="inline-flex items-center justify-center rounded-xl bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa] focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                Cetak Draf PDF
                                            </a>
                                            @if($item->status !== 'disetujui')
                                                <div x-data="{ openAjukan: false, preview: '' }" class="w-full">
                                                    <button type="button" @click="openAjukan = true"
                                                            class="w-full inline-flex items-center justify-center rounded-xl bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049458] focus:outline-none focus:ring-2 focus:ring-[#05b169]/30">
                                                        {{ $item->status === 'diajukan' ? 'Ajukan Ulang' : 'Ajukan' }}
                                                    </button>
                                                    <div x-show="openAjukan" x-cloak
                                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                                         @keydown.escape.window="openAjukan = false">
                                                        <div @click.outside="openAjukan = false" x-transition
                                                             class="w-full max-w-lg rounded-2xl bg-white shadow-xl text-left">
                                                            <div class="flex items-center justify-between border-b px-5 py-3">
                                                                <h3 class="font-bold text-black">Ajukan Bukti Fisik</h3>
                                                                <button type="button" @click="openAjukan = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                                                            </div>
                                                            <form action="{{ route('siswa.catatan.ajukan', $item->id) }}" method="POST" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="max-h-[65vh] overflow-auto p-5 space-y-4">
                                                                    <p class="rounded-lg bg-[#0047d6]/5 p-3 text-xs font-medium text-[#5b616e]">
                                                                        Cetak draf, minta paraf/catatan instruktur di lembar fisik, foto lembar tersebut, lalu unggah di sini.
                                                                    </p>
                                                                    <div>
                                                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                                                            Catatan / Nilai dari Instruktur <span class="text-red-500">*</span>
                                                                        </label>
                                                                        <textarea name="catatan_instruktur" rows="3" required
                                                                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"
                                                                                  placeholder="Ketik ulang catatan/nilai yang ditulis instruktur...">{{ old('catatan_instruktur', $item->catatan_instruktur) }}</textarea>
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                                                            Foto Bukti Fisik (lembar berparaf) <span class="text-red-500">*</span>
                                                                        </label>
                                                                        <input type="file" name="foto_bukti"
                                                                               accept="image/*" capture="environment" required
                                                                               @change="preview = URL.createObjectURL($event.target.files[0])"
                                                                               class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#0047d6] file:text-white hover:file:bg-[#0038aa] file:cursor-pointer">
                                                                        <p class="mt-1 text-xs text-[#5b616e]">Di HP, kamera belakang akan langsung aktif. Maks 2MB (jpg/png).</p>
                                                                        <template x-if="preview">
                                                                            <img :src="preview" class="mt-3 h-40 rounded-lg border object-cover" alt="Preview bukti">
                                                                        </template>
                                                                        @if($item->foto_bukti)
                                                                            <div class="mt-3">
                                                                                <p class="text-xs text-[#5b616e] mb-1">Bukti sebelumnya:</p>
                                                                                <img src="{{ asset('storage/' . $item->foto_bukti) }}" class="h-32 rounded-lg border object-cover" alt="Bukti lama">
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="flex justify-end gap-2 border-t px-5 py-3">
                                                                    <button type="button" @click="openAjukan = false"
                                                                            class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                                                                    <button type="submit"
                                                                            class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049458]">Kirim Pengajuan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="{{ route('siswa.catatan.edit', $item->id) }}"
                                                   class="inline-flex items-center justify-center rounded-xl bg-[#d98200] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#b56d00] focus:outline-none focus:ring-2 focus:ring-[#d98200]/30">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('siswa.catatan.destroy', $item) }}"
                                                      onsubmit="return confirm('Hapus catatan ini? Data yang dihapus tidak dapat dikembalikan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="w-full text-xs px-3 py-1.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 font-bold">Hapus</button>
                                                </form>
                                            @else
                                                <span class="text-center text-xs italic text-[#5b616e]">Terkunci (disetujui)</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada catatan kegiatan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ============================================================= --}}
                {{-- ============  TAMPILAN HP (KARTU RINGKAS, <1024px)  ========= --}}
                {{-- ============================================================= --}}
                <div class="cat-mobile space-y-3">
                    @forelse ($catatan as $item)
                        @php
                            $statusBadge = match($item->status) {
                                'disetujui' => 'bg-[#05b169] text-white',
                                'diajukan'  => 'bg-[#0047d6] text-white',
                                default     => 'bg-[#d98200] text-white',
                            };
                            $statusLabel = match($item->status) {
                                'disetujui' => 'Disetujui',
                                'diajukan'  => 'Diajukan',
                                default     => 'Draft',
                            };
                            $extBukti = $item->foto_bukti ? pathinfo($item->foto_bukti, PATHINFO_EXTENSION) : '';
                            $tglLabel = $item->created_at->translatedFormat('d M Y');
                        @endphp
                      <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm"
     x-data="{ detail: false, openAjukan: false }"
     x-effect="document.body.style.overflow = (detail || openAjukan) ? 'hidden' : ''">
                            {{-- Ringkas: NAMA PEKERJAAN + tanggal + status (kiri) + LIHAT DETAIL (kanan) --}}
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold text-black break-words">{{ $item->nama_pekerjaan }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-medium text-[#5b616e]">{{ $tglLabel }}</span>
                                        <span class="inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $statusBadge }}">{{ $statusLabel }}</span>
                                    </div>
                                </div>

                                <button type="button" @click="detail = true"
                                        class="inline-flex flex-shrink-0 items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Lihat Detail
                                </button>
                            </div>

                            {{-- ===== POP-UP DETAIL ===== --}}
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
                                        <h3 class="text-base font-bold text-black">Detail Catatan</h3>
                                        <button type="button" @click="detail = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                    </div>

                                    <div class="space-y-4 px-5 py-4">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p>
                                                <p class="text-sm font-medium text-black">{{ $tglLabel }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Status</p>
                                                @if($item->status === 'disetujui')
                                                    <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-2.5 py-1 text-xs font-bold text-[#05b169]">Disetujui</span>
                                                @elseif($item->status === 'diajukan')
                                                    <span class="inline-flex items-center rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6]">Diajukan</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-[#d98200]/10 px-2.5 py-1 text-xs font-bold text-[#d98200]">Draft</span>
                                                @endif
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Pekerjaan</p>
                                                <p class="text-sm font-medium text-black break-words">{{ $item->nama_pekerjaan }}</p>
                                            </div>
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Perencanaan</p>
                                            <p class="text-sm font-medium text-black break-words">{{ $item->perencanaan_kegiatan ?: '-' }}</p>
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Hasil / Pelaksanaan</p>
                                            <p class="text-sm font-medium text-black break-words">{{ $item->pelaksanaan_kegiatan ?: '-' }}</p>
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Catatan Instruktur</p>
                                            @if($item->catatan_instruktur)
                                                <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-sm font-medium italic text-black">
                                                    {{ $item->catatan_instruktur }}
                                                </div>
                                            @else
                                                <p class="text-sm text-[#5b616e]">-</p>
                                            @endif
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Bukti Fisik</p>
                                            @if($item->foto_bukti)
                                                <div class="flex flex-wrap gap-2">
                                                    <a href="{{ asset('storage/' . $item->foto_bukti) }}" target="_blank" rel="noopener"
                                                       class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049458]">
                                                        Lihat Bukti
                                                    </a>
                                                    <a href="{{ asset('storage/' . $item->foto_bukti) }}"
                                                       download="bukti-{{ $item->user->nisn ?? $item->user_id }}-{{ $item->id . '.' . $extBukti }}"
                                                       class="inline-flex items-center rounded-full border-2 border-[#05b169] bg-white px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/5">
                                                        Download Bukti
                                                    </a>
                                                </div>
                                            @else
                                                <p class="text-sm italic text-[#5b616e]">Belum ada bukti</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="sticky bottom-0 space-y-2 border-t-2 border-[#0047d6]/15 bg-white px-5 py-4">
                                        @if($item->status !== 'disetujui')
                                           <button type="button" @click="detail = false; setTimeout(() => openAjukan = true, 250)"
        class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#05b169] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#049a5b]">
    Ajukan
    {{ $item->status === 'diajukan' ? 'Ajukan Ulang' : 'Ajukan' }}
</button>
                                         
                                        @endif
                                        <div class="flex gap-2">
                                            <a href="{{ route('cetak.catatan', ['catatan_id' => $item->id]) }}" target="_blank"
                                               class="flex flex-1 items-center justify-center rounded-xl bg-[#0047d6] px-3 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-[#0038aa]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                                </svg>
                                                Cetak PDF
                                            </a>
                                            @if($item->status !== 'disetujui')
                                                <a href="{{ route('siswa.catatan.edit', $item->id) }}"
                                                   class="flex flex-1 items-center justify-center rounded-xl bg-[#0047d6]/10 px-3 py-2.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('siswa.catatan.destroy', $item) }}"
                                                      class="flex-1"
                                                      onsubmit="return confirm('Hapus catatan ini? Data yang dihapus tidak dapat dikembalikan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="flex w-full items-center justify-center rounded-xl bg-[#cf202f]/10 px-3 py-2.5 text-xs font-bold text-[#cf202f] transition hover:bg-[#cf202f]/20">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ===== MODAL AJUKAN (mobile) ===== --}}
                            @if($item->status !== 'disetujui')
                               <div x-show="openAjukan" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4"
     @keydown.escape.window="openAjukan = false">
    <div x-show="openAjukan"
         x-transition:enter="transition ease-out duration-300 delay-[50ms]"
         x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
         class="w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-xl text-left"
         @click.outside="openAjukan = false">
                                        <div class="flex items-center justify-between border-b px-5 py-3">
                                            <h3 class="font-bold text-black">Ajukan Bukti Fisik</h3>
                                            <button type="button" @click="openAjukan = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                                        </div>
                                        <form action="{{ route('siswa.catatan.ajukan', $item->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="max-h-[65vh] overflow-auto p-5 space-y-4">
                                                <p class="rounded-lg bg-[#0047d6]/5 p-3 text-xs font-medium text-[#5b616e]">
                                                    Cetak draf, minta paraf/catatan instruktur di lembar fisik, foto lembar tersebut, lalu unggah di sini.
                                                </p>
                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                                        Catatan / Nilai dari Instruktur <span class="text-red-500">*</span>
                                                    </label>
                                                    <textarea name="catatan_instruktur" rows="3" required
                                                              class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"
                                                              placeholder="Ketik ulang catatan/nilai yang ditulis instruktur...">{{ old('catatan_instruktur', $item->catatan_instruktur) }}</textarea>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">
                                                        Foto Bukti Fisik (lembar berparaf) <span class="text-red-500">*</span>
                                                    </label>
                                                    <input type="file" name="foto_bukti"
                                                           accept="image/*" capture="environment" required
                                                           @change="preview = URL.createObjectURL($event.target.files[0])"
                                                           class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#0047d6] file:text-white hover:file:bg-[#0038aa] file:cursor-pointer">
                                                    <p class="mt-1 text-xs text-[#5b616e]">Di HP, kamera belakang akan langsung aktif. Maks 2MB (jpg/png).</p>
                                                    <template x-if="preview">
                                                        <img :src="preview" class="mt-3 h-40 rounded-lg border object-cover" alt="Preview bukti">
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="flex justify-end gap-2 border-t px-5 py-3">
                                                <button type="button" @click="openAjukan = false"
                                                        class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                                                <button type="submit"
                                                        class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049458]">Kirim Pengajuan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white px-4 py-8 text-center font-medium text-[#5b616e] italic">
                            Belum ada catatan kegiatan.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {!! $catatan->links() !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
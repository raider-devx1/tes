<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">Jurnal Kegiatan Harian</h2>
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-[#dee1e6] bg-white p-4 sm:p-6 md:p-8">

                <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
                    <h3 class="text-lg font-semibold tracking-tight text-[#0a0b0d]">Riwayat Jurnal Saya</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('siswa.jurnal.create') }}"
                           class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                            + Tambah Jurnal
                        </a>
                        <a href="{{ route('cetak.jurnal') }}" target="_blank"
                           class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                            Cetak Semua PDF
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="mb-4 rounded-2xl border border-[#05b169]/30 bg-[#05b169]/10 px-4 py-3 text-sm font-medium text-[#05b169]">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 rounded-2xl border border-[#cf202f]/30 bg-[#cf202f]/10 px-4 py-3 text-sm font-medium text-[#cf202f]">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('siswa.jurnal.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                               class="rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status</label>
                        <select name="status"
                                class="rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(request('status') === 'pending')>Menunggu</option>
                            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                            <option value="revisi" @selected(request('status') === 'revisi')>Revisi</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">Filter</button>
                    <a href="{{ route('siswa.jurnal.index') }}"
                       class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Reset</a>
                </form>

                {{-- ===== TABEL JURNAL ===== --}}
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full min-w-[900px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold w-28">Tanggal</th>
                                <th class="px-4 py-3 font-semibold w-[38%]">Unit Kerja</th>
                                <th class="px-4 py-3 font-semibold w-1/5">Catatan Instruktur</th>
                                <th class="px-4 py-3 font-semibold w-36">Foto</th>
                                <th class="px-4 py-3 text-center font-semibold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-semibold w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse($jurnals as $jurnal)
                            <tr class="align-top transition hover:bg-[#f7f7f7]">
                                <td class="px-4 py-3 text-center text-[#7c828a]">{{ $jurnals->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">
                                     {{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->translatedFormat('d M Y') }}
                                </td>

                                {{-- ===== UNIT KERJA: tampilkan pekerjaan pertama, sisanya bisa dibuka ===== --}}
                                <td class="px-4 py-3 text-[#5b616e] break-words">
                                    @php $items = $jurnal->items; @endphp
                                    @if($items->count())
                                        <div x-data="{ open: false }">
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-semibold text-[#0a0b0d]">1.</span>
                                                <span class="break-words">{{ $items->first()->unit_kerja }}</span>
                                            </div>

                                            @if($items->count() > 1)
                                                <button type="button" @click="open = !open"
                                                        class="mt-1 inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-2.5 py-1 text-xs font-semibold text-[#0052ff] transition hover:bg-[#dee1e6]">
                                                    <span x-show="!open">+ {{ $items->count() - 1 }} pekerjaan lainnya</span>
                                                    <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                         stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                                    </svg>
                                                </button>

                                                <ol start="2" x-show="open" x-cloak x-transition
                                                    class="mt-2 list-decimal list-inside space-y-0.5 border-t border-[#eef0f3] pt-2">
                                                    @foreach($items->slice(1) as $it)
                                                        <li class="break-words">{{ $it->unit_kerja }}</li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[#a8acb3]">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-[#5b616e] break-words">
                                    @if($jurnal->catatan_instruktur)
                                        <div class="rounded-lg border-l-2 border-[#f4b000] bg-[#f4b000]/5 p-2 text-xs italic text-[#5b616e]">
                                             {{ $jurnal->catatan_instruktur }}
                                        </div>
                                    @else
                                        <span class="text-[#a8acb3]">-</span>
                                    @endif
                                </td>

                                {{-- ===== FOTO: tombol Lihat + Download ===== --}}
                                <td class="px-4 py-3 text-center">
                                    @php $fotos = $jurnal->items->whereNotNull('dokumentasi')->values(); @endphp
                                    @if($fotos->count())
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($fotos as $k => $it)
                                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                                    <span class="text-xs text-[#7c828a]">Foto {{ $k + 1 }}</span>
                                                    <a href="{{ asset('storage/' . $it->dokumentasi) }}" target="_blank"
                                                       class="inline-flex items-center rounded-full bg-[#0052ff]/10 px-2.5 py-1 text-xs font-semibold text-[#0052ff] transition hover:bg-[#0052ff]/20">
                                                        Lihat
                                                    </a>
                                                    <a href="{{ asset('storage/' . $it->dokumentasi) }}"
                                                       download="Foto_Jurnal_{{ $jurnal->id }}_{{ $k + 1 }}"
                                                       class="inline-flex items-center rounded-full bg-[#05b169]/10 px-2.5 py-1 text-xs font-semibold text-[#05b169] transition hover:bg-[#05b169]/20">
                                                        Download
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sm text-[#a8acb3]">Tidak ada</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    @if($jurnal->status_persetujuan == 'pending')
                                        <span class="inline-flex items-center rounded-full bg-[#f4b000]/10 px-3 py-1 text-xs font-semibold text-[#f4b000]">Menunggu</span>
                                    @elseif($jurnal->status_persetujuan == 'disetujui')
                                        <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-3 py-1 text-xs font-semibold text-[#05b169]">Disetujui</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-[#cf202f]/10 px-3 py-1 text-xs font-semibold text-[#cf202f]">Revisi</span>
                                    @endif
                                </td>

                                <!-- ===== AKSI ===== -->
<td class="px-4 py-3">
    <div class="flex flex-wrap items-center justify-center gap-2">
        {{-- Membungkus route cetak PDF --}}
        <a href="{{ route('cetak.jurnal', ['jurnal_id' => $jurnal->id]) }}" target="_blank"
           class="inline-flex items-center rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
            PDF
        </a>

        {{-- Membungkus route edit jurnal --}}
        <a href="{{ route('siswa.jurnal.edit', $jurnal->id) }}"
           class="inline-flex items-center rounded-full bg-[#0052ff]/10 px-3 py-1.5 text-xs font-semibold text-[#0052ff] transition hover:bg-[#0052ff]/20">
            Edit
        </a>

        {{-- Membungkus route destroy/hapus pada form action --}}
        <form action="{{ route('siswa.jurnal.destroy', $jurnal->id) }}" method="POST"
              onsubmit="return confirm('Hapus jurnal ini? Data tidak dapat dikembalikan.');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center rounded-full bg-[#cf202f]/10 px-3 py-1.5 text-xs font-semibold text-[#cf202f] transition hover:bg-[#cf202f]/20">
                Hapus
            </button>
        </form>
    </div>
</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-[#a8acb3] italic">Belum ada jurnal yang diisi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $jurnals->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
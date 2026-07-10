<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Catatan Kegiatan</h2>
            
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                <div class="flex flex-col sm:flex-row sm:flex-wrap sm:justify-between gap-3 mb-6">
                    <a href="{{ route('siswa.catatan.create') }}"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                         Tambah Catatan
                    </a>
                    <a href="{{ route('cetak.catatan') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-[#0047d6] bg-white px-6 py-3.5 text-base font-bold text-[#0047d6] shadow-sm transition hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        Cetak Semua (PDF)
                    </a>
                </div>

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

                {{-- ===== FORM FILTER ===== --}}
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
                            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                            <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Filter</button>
                    <a href="{{ route('siswa.catatan.index') }}"
                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                </form>

                {{-- ===== TABEL DATA ===== --}}
                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
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
                                <th class="px-4 py-3 text-center font-bold w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($catatan as $item)
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $loop->iteration + ($catatan->firstItem() - 1) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                         {{ $item->created_at->translatedFormat('d M Y') }}
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
                                        @if($item->is_approved)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Disetujui</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Menunggu</span>
                                        @endif
                                    </td>

                                    {{-- ===== KOLOM AKSI ===== --}}
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-stretch gap-1.5">
                                            <a href="{{ route('cetak.catatan', ['catatan_id' => $item->id]) }}" target="_blank"
                                               class="inline-flex items-center justify-center rounded-xl bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa] focus:outline-none focus:ring-2 focus:ring-[#0047d6]/30">
                                                Cetak PDF
                                            </a>

                                            @if(! $item->is_approved)
                                                <a href="{{ route('siswa.catatan.edit', $item->id) }}"
                                                   class="inline-flex items-center justify-center rounded-xl bg-[#d98200] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#b56d00] focus:outline-none focus:ring-2 focus:ring-[#d98200]/30">
                                                    Edit
                                                </a>

                                               <form method="POST" action="{{ route('siswa.catatan.destroy', $item) }}"
      data-confirm="Hapus {{ $item->judul ?? 'catatan ini' }}?"
      data-confirm-text="Catatan kegiatan yang dihapus tidak dapat dikembalikan."
      data-confirm-yes="Ya, hapus">
    @csrf
    @method('DELETE')
    <button class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-medium">Hapus</button>
</form>
                                            @else
                                                <span class="text-center text-xs italic text-[#5b616e]">Terkunci</span>
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

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $catatan->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
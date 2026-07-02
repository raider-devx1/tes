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
            <div class="rounded-3xl border border-[#dee1e6] bg-white p-6 md:p-8">

                <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
                    <h3 class="text-lg font-semibold tracking-tight text-[#0a0b0d]">Riwayat Jurnal Saya</h3>
                    <div class="flex gap-2">
                        <a href="{{ route('siswa.jurnal.create') }}"
                           class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                            + Tambah Jurnal
                        </a>
                        <a href="{{ route('cetak.jurnal') }}" target="_blank"
                           class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                            Cetak PDF
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
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Tanggal</th>
                                <th class="px-4 py-3 font-semibold">Unit Kerja</th>
                                <th class="px-4 py-3 font-semibold w-1/3">Deskripsi Pekerjaan</th>
                                <th class="px-4 py-3 font-semibold">Foto</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                                <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse($jurnals as $jurnal)
                            <tr class="align-top transition hover:bg-[#f7f7f7]">
                                <td class="px-4 py-3 text-center text-[#7c828a]">{{ $jurnals->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">
                                    {{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-[#5b616e]">{{ $jurnal->unit_kerja }}</td>
                                <td class="px-4 py-3 text-[#5b616e]">
                                    {{ $jurnal->deskripsi_pekerjaan }}
                                    @if($jurnal->catatan_instruktur)
                                        <div class="mt-2 rounded-lg border-l-2 border-[#f4b000] bg-[#f4b000]/5 p-2 text-xs italic text-[#5b616e]">
                                            <strong class="text-[#0a0b0d]">Catatan Instruktur:</strong> {{ $jurnal->catatan_instruktur }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($jurnal->dokumentasi)
                                        <a href="{{ asset('storage/'.$jurnal->dokumentasi) }}" target="_blank" class="text-sm font-semibold text-[#0052ff] hover:underline">Lihat Foto</a>
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
                                <td class="px-4 py-3 text-center">
                                    @if($jurnal->status_persetujuan == 'pending')
                                        <form action="{{ route('siswa.jurnal.destroy', $jurnal->id) }}" method="POST" onsubmit="return confirm('Hapus jurnal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-full bg-[#cf202f]/10 px-3 py-1.5 text-xs font-semibold text-[#cf202f] transition hover:bg-[#cf202f]/20">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-[#a8acb3]">-</span>
                                    @endif
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
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
             <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">Lembar Observasi PKL</h2>
           
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-[#dee1e6] bg-white p-6 md:p-8">

                <div class="flex justify-end mb-6">
                    <a href="{{ route('cetak.observasi') }}" target="_blank"
                       class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                        Cetak PDF
                    </a>
                </div>

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('siswa.observasi.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
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
                            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                            <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">Filter</button>
                    <a href="{{ route('siswa.observasi.index') }}"
                       class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Reset</a>
                </form>

                {{-- ===== TABEL OBSERVASI ===== --}}
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Tanggal</th>
                                <th class="px-4 py-3 font-semibold">Guru Pembimbing</th>
                                <th class="px-4 py-3 font-semibold">Permasalahan</th>
                                <th class="px-4 py-3 font-semibold">Solusi Pemecahan</th>
                                <th class="px-4 py-3 text-center font-semibold">Status Instruktur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse ($observasi as $item)
                                <tr class="align-top transition hover:bg-[#f7f7f7]">
                                    <td class="px-4 py-3 text-center text-[#7c828a]">{{ $observasi->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">
                                        {{ \Carbon\Carbon::parse($item->hari_tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-[#0a0b0d]">{{ $item->guru->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-[#5b616e]">{{ $item->permasalahan }}</td>
                                    <td class="px-4 py-3 text-[#5b616e]">{{ $item->solusi }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->is_approved)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-3 py-1 text-xs font-semibold text-[#05b169]">Disetujui</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#f4b000]/10 px-3 py-1 text-xs font-semibold text-[#f4b000]">Menunggu</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-[#a8acb3] italic">Belum ada observasi dari guru pembimbing.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $observasi->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
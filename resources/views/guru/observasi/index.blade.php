<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">
                Lembar Observasi
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('guru.observasi.create') }}"
                   class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                    + Tambah Observasi
                </a>
                <button type="button" onclick="history.back()"
                        class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                    &larr; Kembali
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-[#dee1e6] bg-white p-6 md:p-8">

                @if (session('success'))
                    <div class="mb-4 rounded-2xl border border-[#05b169]/30 bg-[#05b169]/10 px-4 py-3 text-sm font-medium text-[#05b169]">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('guru.observasi.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">
                                Cari (Nama / NISN)
                            </label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
                        </div>

                        <div class="w-full md:w-56">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status</label>
                            <select name="status"
                                    class="w-full rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                                <option value="">-- Semua Status --</option>
                                <option value="1" @selected(request('status') === '1')>Disetujui</option>
                                <option value="0" @selected(request('status') === '0')>Menunggu</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                                Cari
                            </button>
                            <a href="{{ route('guru.observasi.index') }}"
                               class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL OBSERVASI ===== --}}
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Tanggal</th>
                                <th class="px-4 py-3 font-semibold">Siswa</th>
                                <th class="px-4 py-3 font-semibold">NISN</th>
                                <th class="px-4 py-3 font-semibold">Pekerjaan/Projek</th>
                                <th class="px-4 py-3 font-semibold">Permasalahan</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                                <th class="px-4 py-3 text-center font-semibold">Cetak</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
    @forelse ($observasi as $obs)
        <tr class="align-top transition hover:bg-[#f7f7f7]">
            <td class="px-4 py-3 text-center text-[#7c828a]">{{ $observasi->firstItem() + $loop->index }}</td>
            <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">
                {{ \Carbon\Carbon::parse($obs->hari_tanggal)->translatedFormat('d M Y') }}
            </td>
            <td class="px-4 py-3 font-semibold text-[#0a0b0d]">{{ $obs->user->name }}</td>
            <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">{{ $obs->user->nisn ?? '-' }}</td>
            <td class="px-4 py-3 text-[#5b616e]">{{ $obs->pekerjaan_projek }}</td>
            <td class="px-4 py-3 text-[#5b616e]">
                <span class="inline-flex items-center rounded-full bg-[#eef0f3] px-2.5 py-0.5 text-xs font-semibold text-[#0a0b0d]">
                    {{ $obs->items->count() }} poin
                </span>
                @if ($obs->items->count())
                    <div class="mt-1 text-xs text-[#7c828a]">
                        {{ \Illuminate\Support\Str::limit($obs->items->first()->permasalahan, 60) }} 
                    </div>
                @endif
            </td>
            <td class="px-4 py-3 text-center">
                @if ($obs->is_approved)
                    <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-3 py-1 text-xs font-semibold text-[#05b169]">Disetujui</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-[#f4b000]/10 px-3 py-1 text-xs font-semibold text-[#f4b000]">Menunggu</span>
                @endif
            </td>
            <td class="px-4 py-3 text-center">
                <a href="{{ route('cetak.observasi', $obs->user_id) }}" target="_blank"
                   class="inline-flex items-center rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">PDF</a>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="px-4 py-8 text-center text-[#a8acb3] italic">
                Belum ada data observasi.
            </td>
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
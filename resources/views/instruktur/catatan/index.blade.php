<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
             <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">
                 Persetujuan Catatan Kegiatan
             </h2>
            
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                &larr; Kembali
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-[#dee1e6] bg-white p-6 md:p-8">

                @if(session('success'))
                    <div class="mb-4 rounded-2xl border border-[#05b169]/30 bg-[#05b169]/10 px-4 py-3 text-sm font-medium text-[#05b169]">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('instruktur.catatan.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
                        </div>

                        <div class="w-full md:w-56">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status</label>
                            <select name="status"
                                    class="w-full rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                                <option value="">-- Semua Status --</option>
                                <option value="disetujui" @selected(request('status') === 'disetujui')>Sudah Disetujui</option>
                                <option value="belum"     @selected(request('status') === 'belum')>Belum (Menunggu)</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                                Cari
                            </button>
                            <a href="{{ route('instruktur.catatan.index') }}"
                               class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL CATATAN ===== --}}
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                                <th class="px-4 py-3 font-semibold">NISN</th>
                                <th class="px-4 py-3 w-1/6 font-semibold">Pekerjaan</th>
                                <th class="px-4 py-3 w-1/5 font-semibold">Perencanaan</th>
                                <th class="px-4 py-3 w-1/5 font-semibold">Hasil</th>
                                <th class="px-4 py-3 w-1/4 text-center font-semibold">Aksi / Catatan Instruktur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse ($catatan as $item)
                                <tr class="align-top transition hover:bg-[#f7f7f7]">
                                    <td class="px-4 py-3 text-center text-[#7c828a]">{{ $catatan->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-semibold text-[#0a0b0d]">{{ $item->user->name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]">{{ $item->user->nisn ?? '-' }}</td>
                                    <td class="px-4 py-3 text-[#5b616e]">{{ $item->nama_pekerjaan }}</td>
                                    <td class="px-4 py-3 text-[#5b616e]">{{ $item->perencanaan_kegiatan }}</td>
                                    <td class="px-4 py-3 text-[#5b616e]">{{ $item->pelaksanaan_kegiatan }}</td>
                                    <td class="px-4 py-3">
                                        @if($item->is_approved)
                                            <div class="mb-2 rounded-full bg-[#05b169]/10 px-3 py-1 text-center text-xs font-semibold text-[#05b169]">Telah Disetujui</div>
                                            <p class="text-xs text-[#5b616e]"><strong class="text-[#0a0b0d]">Catatan:</strong> {{ $item->catatan_instruktur }}</p>
                                        @else
                                            <form action="{{ route('instruktur.catatan.approve', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <textarea name="catatan_instruktur" rows="2" placeholder="Masukkan catatan / evaluasi..."
                                                          class="mb-2 w-full rounded-lg border-[#dee1e6] text-xs focus:border-[#0052ff] focus:ring-[#0052ff]"></textarea>
                                                <button type="submit"
                                                        class="w-full rounded-full bg-[#0052ff] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#003ecc]">
                                                    Setujui &amp; Simpan
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('cetak.catatan', $item->user_id) }}" target="_blank"
                                           class="mt-2 inline-block w-full rounded-full bg-[#eef0f3] px-3 py-1.5 text-center text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Cetak PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-[#a8acb3] italic">Belum ada catatan dari siswa.</td>
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
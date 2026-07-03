<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

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

                <!-- Filter + Cetak Semua -->
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

                <!-- Tombol Cetak Semua PDF (di atas tabel) -->
                <div class="mb-4 flex justify-end">
                    <a href="{{ route('cetak.catatan.semua') }}" target="_blank"
                       class="inline-flex items-center gap-2 rounded-full bg-[#e11d48] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#be123c]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                        </svg>
                        Cetak Semua PDF
                    </a>
                </div>

                <!-- Tabel -->
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-4 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                                <th class="px-4 py-3 font-semibold">NISN</th>
                                <th class="px-4 py-3 font-semibold">Pekerjaan</th>
                                <th class="px-4 py-3 font-semibold">Perencanaan</th>
                                <th class="px-4 py-3 font-semibold">Hasil</th>
                                <th class="px-4 py-3 w-32 text-center font-semibold">Status</th>
                                <th class="px-4 py-3 w-56 font-semibold">Catatan</th>
                                <th class="px-4 py-3 w-44 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse ($catatan as $item)
                                <tr class="align-top transition hover:bg-[#f7f7f7]">
                                    <td class="px-4 py-3 text-center text-[#7c828a]"> {{ $catatan->firstItem() + $loop->index }} </td>
                                    <td class="px-4 py-3 font-semibold text-[#0a0b0d]"> {{ $item->user->name ?? '-' }} </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]"> {{ $item->user->nisn ?? '-' }} </td>
                                    <td class="px-4 py-3 text-[#5b616e]"> {{ $item->nama_pekerjaan }} </td>
                                    <td class="px-4 py-3 text-[#5b616e]"> {{ $item->perencanaan_kegiatan }} </td>
                                    <td class="px-4 py-3 text-[#5b616e]"> {{ $item->pelaksanaan_kegiatan }} </td>

                                    <!-- Kolom STATUS -->
                                    <td class="px-4 py-3 text-center">
                                        @if($item->is_approved)
                                            <span class="inline-block rounded-full bg-[#05b169]/10 px-3 py-1 text-xs font-semibold text-[#05b169]">Telah Disetujui</span>
                                        @else
                                            <span class="inline-block rounded-full bg-[#f4b000]/10 px-3 py-1 text-xs font-semibold text-[#b98900]">Menunggu</span>
                                        @endif
                                    </td>

                                    <!-- Kolom CATATAN -->
                                    <td class="px-4 py-3 text-[#5b616e]">
                                        @if($item->catatan_instruktur)
                                             {{ $item->catatan_instruktur }} 
                                        @else
                                            <span class="italic text-[#a8acb3]">Belum ada catatan</span>
                                        @endif
                                    </td>

                                    <!-- Kolom AKSI -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-stretch gap-2" x-data="{ openCatatan: false }">

                                            <!-- Setujui / Batalkan -->
                                            @if($item->is_approved)
                                                <form action="{{ route('instruktur.catatan.batal', $item->id) }}" method="POST"
                                                      data-confirm="Batalkan persetujuan catatan ini?"
                                                      data-confirm-text="Catatan akan kembali berstatus menunggu."
                                                      data-confirm-yes="Ya, batalkan">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit"
                                                            class="w-full inline-flex items-center justify-center rounded-full bg-[#f4b000]/10 px-3 py-1.5 text-xs font-semibold text-[#b98900] transition hover:bg-[#f4b000]/20">
                                                        Batalkan Persetujuan
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('instruktur.catatan.approve', $item->id) }}" method="POST"
                                                      data-confirm="Setujui catatan ini?"
                                                      data-confirm-icon="question"
                                                      data-confirm-yes="Ya, setujui">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit"
                                                            class="w-full inline-flex items-center justify-center rounded-full bg-[#0052ff] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#003ecc]">
                                                        Setujui
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Tambah / Edit Catatan (buka modal) -->
                                            <button type="button" @click="openCatatan = true"
                                                    class="w-full inline-flex items-center justify-center rounded-full bg-[#0052ff]/10 px-3 py-1.5 text-xs font-semibold text-[#0052ff] transition hover:bg-[#0052ff]/20">
                                                {{ $item->catatan_instruktur ? 'Edit Catatan' : 'Tambah Catatan' }} 
                                            </button>

                                            <!-- Cetak PDF (hanya SATU catatan ini) -->
                                            <a href="{{ route('cetak.catatan', ['siswa_id' => $item->user_id, 'catatan_id' => $item->id]) }}" target="_blank"
                                               class="w-full inline-flex items-center justify-center rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                                                Cetak PDF
                                            </a>

                                            <!-- ===== MODAL FORM CATATAN ===== -->
                                            <div x-show="openCatatan" x-cloak
                                                 @keydown.escape.window="openCatatan = false"
                                                 class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                <div class="absolute inset-0 bg-black/40" @click="openCatatan = false"></div>

                                                <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl text-left" @click.stop>
                                                    <h3 class="text-lg font-semibold text-[#0a0b0d]">Catatan Instruktur</h3>
                                                    <p class="mt-0.5 mb-4 text-xs text-[#7c828a]">
                                                        Untuk: <strong class="text-[#0a0b0d]"> {{ $item->user->name ?? '-' }} </strong>
                                                    </p>

                                                    <form action="{{ route('instruktur.catatan.komentar', $item->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <textarea name="catatan_instruktur" rows="5" required
                                                                  placeholder="Tulis catatan / evaluasi untuk siswa..."
                                                                  class="w-full rounded-xl border-[#dee1e6] bg-[#f7f7f7] px-4 py-3 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">{{ $item->catatan_instruktur }}</textarea>

                                                        <div class="mt-4 flex justify-end gap-2">
                                                            <button type="button" @click="openCatatan = false"
                                                                    class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                                                                Batal
                                                            </button>
                                                            <button type="submit"
                                                                    class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                                                                Simpan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <!-- ===== /MODAL ===== -->

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-[#a8acb3] italic">Belum ada catatan dari siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {!! $catatan->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
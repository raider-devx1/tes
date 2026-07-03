<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">Rekap &amp; Penilaian (Guru Pembimbing)</h2>
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

                <p class="text-sm text-[#5b616e] mb-6">
                    Nilai Instruktur (skala 1–5) diisi oleh instruktur industri.
                    <strong class="text-[#0a0b0d]">Nilai Guru</strong> &amp; <strong class="text-[#0a0b0d]">Nilai Laporan</strong> (skala 0–100) diisi oleh Anda.
                    Nilai Akhir = 50% Instruktur + 20% Guru + 30% Laporan.
                </p>

                <!-- Filter -->
                <form method="GET" action="{{ route('guru.nilai.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Status Penilaian</label>
                            <select name="status"
                                    class="w-full rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                                <option value="">-- Semua Status --</option>
                                <option value="sudah" @selected(request('status') === 'sudah')>Sudah Dinilai</option>
                                <option value="belum" @selected(request('status') === 'belum')>Belum Dinilai</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">Cari</button>
                            <a href="{{ route('guru.nilai.index') }}"
                               class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Tombol Cetak Semua PDF di atas tabel (1 siswa per halaman) -->
                <div class="mb-4 flex justify-end">
                    <a href="{{ route('cetak.nilai.semua') }}" target="_blank"
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
                                <th class="px-4 py-3 font-semibold">Siswa</th>
                                <th class="px-4 py-3 font-semibold">NISN</th>
                                <th class="px-4 py-3 text-center font-semibold">Instruktur (/5)</th>
                                <th class="px-4 py-3 text-center font-semibold bg-[#0052ff]/5 text-[#0052ff]">Nilai Akhir</th>
                                <th class="px-4 py-3 text-center font-semibold">Status</th>
                                <th class="px-4 py-3 text-center font-semibold w-64">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse($siswa as $item)
                                @php
                                    $n = $item->nilai;
                                    $sudahDinilai = $n && $n->nilai_guru !== null;
                                @endphp
                                <tr class="align-top transition hover:bg-[#f7f7f7]" x-data="{ openNilai: false }">
                                    <td class="px-4 py-3 text-center text-[#7c828a]"> {{ $siswa->firstItem() + $loop->index }} </td>
                                    <td class="px-4 py-3 font-semibold text-[#0a0b0d]"> {{ $item->name }} </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-[#5b616e]"> {{ $item->nisn ?? '-' }} </td>
                                    <td class="px-4 py-3 text-center text-[#5b616e]"> {{ optional($n)->rata_rata ?? '-' }} </td>
                                    <td class="px-4 py-3 text-center font-bold text-[#0052ff] bg-[#0052ff]/5"> {{ optional($n)->nilai_akhir ?? '-' }} </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3 text-center">
                                        @if($sudahDinilai)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-3 py-1 text-xs font-semibold text-[#05b169]">Sudah Dinilai</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#f4b000]/10 px-3 py-1 text-xs font-semibold text-[#f4b000]">Belum Dinilai</span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-stretch gap-2">
                                            <button type="button" @click="openNilai = true"
                                                    class="w-full inline-flex items-center justify-center rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $sudahDinilai ? 'bg-[#eef0f3] text-[#0a0b0d] hover:bg-[#dee1e6]' : 'bg-[#0052ff] text-white hover:bg-[#003ecc]' }}">
                                                {{ $sudahDinilai ? 'Perbarui Nilai' : 'Isi Nilai' }}
                                            </button>

                                            <a href="{{ route('cetak.nilai', $item->id) }}" target="_blank"
                                               class="w-full inline-flex items-center justify-center rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                                                Cetak PDF
                                            </a>
                                        </div>

                                        <!-- MODAL: form nilai guru + laporan + catatan -->
                                        <div x-show="openNilai" x-cloak
                                             @keydown.escape.window="openNilai = false"
                                             class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                            <div class="absolute inset-0 bg-black/40" @click="openNilai = false"></div>

                                            <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl text-left" @click.stop>
                                                <h3 class="text-lg font-semibold text-[#0a0b0d]">Lembar Penilaian Guru</h3>
                                                <p class="mt-0.5 mb-4 text-xs text-[#7c828a]">
                                                    Siswa: <strong class="text-[#0a0b0d]"> {{ $item->name }} </strong>
                                                </p>

                                                <form action="{{ route('guru.nilai.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $item->id }}">

                                                    <div class="grid grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="block text-xs font-semibold text-[#5b616e] mb-1">Nilai Guru (0–100)</label>
                                                            <input type="number" name="nilai_guru" min="0" max="100" step="0.01" required
                                                                   value="{{ old('nilai_guru', optional($n)->nilai_guru) }}"
                                                                   class="w-full rounded-lg border-[#dee1e6] text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-semibold text-[#5b616e] mb-1">Nilai Laporan (0–100)</label>
                                                            <input type="number" name="nilai_laporan" min="0" max="100" step="0.01" required
                                                                   value="{{ old('nilai_laporan', optional($n)->nilai_laporan) }}"
                                                                   class="w-full rounded-lg border-[#dee1e6] text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="block text-xs font-semibold text-[#5b616e] mb-1">Catatan Guru (opsional)</label>
                                                        <textarea name="catatan_guru" rows="3" placeholder="Catatan / rekomendasi..."
                                                                  class="w-full rounded-xl border-[#dee1e6] bg-[#f7f7f7] px-4 py-3 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">{{ old('catatan_guru', optional($n)->catatan_guru) }}</textarea>
                                                    </div>

                                                    <div class="mt-4 flex justify-end gap-2">
                                                        <button type="button" @click="openNilai = false"
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-[#a8acb3] italic">Tidak ada data siswa PKL yang Anda bimbing / cocok dengan pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {!! $siswa->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
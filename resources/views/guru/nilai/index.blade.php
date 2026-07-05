<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Rekap &amp; Penilaian (Guru Pembimbing)</h2>
            <a href="{{ route('guru.dashboard') }}"
           class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
            Kembali ke Dashboard
        </a>
        </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- ===== CARD REKAP INFORMASI ===== -->
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Dinilai</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['sudah'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Belum Dinilai</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['belum'] ?? 0 }}</p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- ===== BANNER & CETAK SEMUA PDF ===== -->
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Daftar Penilaian Siswa Bimbingan</h3>
                        <p class="text-xs font-medium text-[#5b616e]">
                            Nilai Instruktur (skala 1–5) diisi instruktur industri. <span class="font-bold text-black">Nilai Guru</span> &amp; <span class="font-bold text-black">Nilai Laporan</span> (0–100) diisi oleh Anda. Nilai Akhir = 50% Instruktur + 20% Guru + 30% Laporan.
                        </p>
                    </div>

                    <a href="{{ route('cetak.nilai.semua') }}" target="_blank"
                       class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                        </svg>
                        Cetak Semua PDF
                    </a>
                </div>

                <!-- ===== FORM FILTER ===== -->
                <form method="GET" action="{{ route('guru.nilai.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Penilaian</label>
                            <select name="status"
                                    class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="sudah" @selected(request('status') === 'sudah')>Sudah Dinilai</option>
                                <option value="belum" @selected(request('status') === 'belum')>Belum Dinilai</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Cari</button>
                            <a href="{{ route('guru.nilai.index') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- ===== TABEL DATA ===== -->
                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold">Siswa</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Instruktur (/5)</th>
                                <th class="px-4 py-3 text-center font-bold w-32 bg-[#0038aa]">Nilai Akhir</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-64">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse($siswa as $item)
                                @php
                                    $n = $item->nilai;
                                    $sudahDinilai = $n && $n->nilai_guru !== null;
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5" x-data="{ openNilai: false }">
                                    <td class="px-4 py-3 text-center font-semibold text-black">
                                         {{ $siswa->firstItem() + $loop->index }} 
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words">
                                         {{ $item->name }} 
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">
                                         {{ $item->nisn ?? '-' }} 
                                    </td>
                                     <td class="px-4 py-3 text-center font-medium text-black">
                                        {{ $n && $n->rata_rata !== null ? number_format($n->rata_rata, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-[#0047d6] bg-[#0047d6]/5">
                                        {{ $n->nilai_akhir ?? '-' }}
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3 text-center">
                                        @if($sudahDinilai)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Sudah Dinilai</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Belum Dinilai</span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-stretch gap-2">
                                            <button type="button" @click="openNilai = true"
                                                    class="w-full inline-flex items-center justify-center rounded-full px-3 py-1.5 text-xs font-bold transition {{ $sudahDinilai ? 'bg-[#0047d6]/10 text-[#0047d6] hover:bg-[#0047d6]/20' : 'bg-[#0047d6] text-white hover:bg-[#0038aa]' }}">
                                                {{ $sudahDinilai ? 'Edit Nilai' : 'Isi Nilai' }}
                                            </button>

                                            <a href="{{ route('cetak.nilai', $item->id) }}" target="_blank"
                                               class="w-full inline-flex items-center justify-center rounded-full bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                Cetak PDF
                                            </a>
                                        </div>

                                        <!-- Modal Form Input Nilai -->
                                        <div x-show="openNilai" x-cloak
                                             @keydown.escape.window="openNilai = false"
                                             class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                            <div class="absolute inset-0 bg-black/50" @click="openNilai = false"></div>

                                            <div class="relative w-full max-w-lg rounded-2xl border-2 border-[#0047d6]/15 bg-white p-6 shadow-xl text-left" @click.stop>
                                                <h3 class="text-lg font-bold text-black">Lembar Penilaian Guru</h3>
                                                <p class="mt-0.5 mb-4 text-xs font-medium text-[#5b616e]">
                                                    Siswa: <strong class="text-black">{{ $item->name }}</strong>
                                                </p>

                                                <form action="{{ route('guru.nilai.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $item->id }}">

                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="block text-xs font-bold text-black mb-1">Nilai Guru (0–100)</label>
                                                            <input type="number" name="nilai_guru" min="0" max="100" step="0.01" required
                                                                   value="{{ $n->nilai_guru ?? '' }}"
                                                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-bold text-black mb-1">Nilai Laporan (0–100)</label>
                                                            <input type="number" name="nilai_laporan" min="0" max="100" step="0.01" required
                                                                   value="{{ $n->nilai_laporan ?? '' }}"
                                                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="block text-xs font-bold text-black mb-1">Catatan Guru (opsional)</label>
                                                        <textarea name="catatan_guru" rows="3" placeholder="Catatan / rekomendasi..."
                                                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-3 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">{{ $n->catatan_guru ?? '' }}</textarea>
                                                    </div>

                                                    <div class="mt-4 flex justify-end gap-2">
                                                        <button type="button" @click="openNilai = false"
                                                                class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                            Batal
                                                        </button>
                                                        <button type="submit"
                                                                class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
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
                                    <td colspan="7" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data siswa PKL yang Anda bimbing / cocok dengan pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ===== PAGINATION ===== -->
                <div class="mt-4">
                    {!! $siswa->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
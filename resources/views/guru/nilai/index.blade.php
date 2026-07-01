<x-app-layout>
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

                {{-- ===== FORM FILTER ===== --}}
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

                {{-- ===== TABEL REKAP NILAI ===== --}}
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-3 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-3 py-3 font-semibold">Siswa</th>
                                <th class="px-3 py-3 font-semibold">NISN</th>
                                <th class="px-3 py-3 text-center font-semibold">Instruktur (/5)</th>
                                <th class="px-3 py-3 text-center font-semibold">Nilai Guru (0–100)</th>
                                <th class="px-3 py-3 text-center font-semibold">Nilai Laporan (0–100)</th>
                                <th class="px-3 py-3 font-semibold">Catatan Guru</th>
                                <th class="px-3 py-3 text-center font-semibold bg-[#0052ff]/5 text-[#0052ff]">Nilai Akhir</th>
                                <th class="px-3 py-3 text-center font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse($siswa as $item)
                                @php $n = $item->nilai; @endphp
                                <tr class="align-top transition hover:bg-[#f7f7f7]">
                                    <td class="px-3 py-3 text-center text-[#7c828a]">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-3 font-semibold text-[#0a0b0d]">{{ $item->name }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-[#5b616e]">{{ $item->nisn ?? '-' }}</td>
                                    <td class="px-3 py-3 text-center text-[#5b616e]">{{ optional($n)->rata_rata ?? '-' }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <input type="number" form="form-guru-{{ $item->id }}" name="nilai_guru" min="0" max="100" step="0.01"
                                               value="{{ old('nilai_guru', optional($n)->nilai_guru) }}"
                                               class="w-20 rounded-lg border-[#dee1e6] text-center text-sm focus:border-[#0052ff] focus:ring-[#0052ff]" required>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <input type="number" form="form-guru-{{ $item->id }}" name="nilai_laporan" min="0" max="100" step="0.01"
                                               value="{{ old('nilai_laporan', optional($n)->nilai_laporan) }}"
                                               class="w-20 rounded-lg border-[#dee1e6] text-center text-sm focus:border-[#0052ff] focus:ring-[#0052ff]" required>
                                    </td>
                                    <td class="px-3 py-3">
                                        <textarea form="form-guru-{{ $item->id }}" name="catatan_guru" rows="1" placeholder="Opsional..."
                                                  class="w-full rounded-lg border-[#dee1e6] text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">{{ old('catatan_guru', optional($n)->catatan_guru) }}</textarea>
                                    </td>
                                    <td class="px-3 py-3 text-center font-bold text-[#0052ff] bg-[#0052ff]/5">
                                        {{ optional($n)->nilai_akhir ?? '-' }}
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <form id="form-guru-{{ $item->id }}" action="{{ route('guru.nilai.store') }}" method="POST" class="mb-2 block">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $item->id }}">
                                            <button type="submit"
                                                    class="w-full rounded-full bg-[#0052ff] px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-[#003ecc]">Simpan</button>
                                        </form>
                                        <a href="{{ route('cetak.nilai', $item->id) }}" target="_blank"
                                           class="block w-full rounded-full bg-[#eef0f3] px-3 py-1.5 text-center text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Cetak PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-6 text-center text-[#a8acb3] italic">Tidak ada data siswa PKL yang Anda bimbing / cocok dengan pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $siswa->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">Penilaian Akhir PKL</h2>
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
                <form method="GET" action="{{ route('instruktur.nilai.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
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
                            <a href="{{ route('instruktur.nilai.index') }}"
                               class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Reset</a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL PENILAIAN ===== --}}
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-sm text-[#5b616e]">
                                <th class="border border-[#eef0f3] p-2 text-center w-12 font-semibold" rowspan="2">No</th>
                                <th class="border p-2 font-semibold" rowspan="2">Siswa</th>
                                <th class="border p-2 font-semibold" rowspan="2">NISN</th>
                                <th class="border border-[#eef0f3] p-2 text-center font-semibold" colspan="4">Kriteria Nilai (1 - 5)</th>
                                <th class="border p-2 font-semibold" rowspan="2">Catatan Instruktur</th>
                                <th class="border p-2 text-center font-semibold" rowspan="2">Aksi</th>
                            </tr>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="border border-[#eef0f3] p-2 text-center w-20 font-semibold">Soft Skills</th>
                                <th class="border border-[#eef0f3] p-2 text-center w-20 font-semibold">Hard Skills</th>
                                <th class="border border-[#eef0f3] p-2 text-center w-24 font-semibold">Pengembangan</th>
                                <th class="border border-[#eef0f3] p-2 text-center w-28 font-semibold">Kewirausahaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswa as $item)
                                @php
                                    // Data nilai siswa ini (jika sudah ada)
                                    $n = $item->nilai;
                                    $sudahDinilai = $n && $n->rata_rata !== null;
                                @endphp
                                <tr class="align-top transition hover:bg-[#f7f7f7]">
                                    <td class="border border-[#eef0f3] p-2 text-center text-[#7c828a]">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="border p-2 font-semibold text-[#0a0b0d]">{{ $item->name }}</td>
                                    <td class="border p-2 whitespace-nowrap text-[#5b616e]">{{ $item->nisn ?? '-' }}</td>

                                    <td class="border border-[#eef0f3] p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="soft_skill" min="1" max="5" value="{{ old('soft_skill', optional($n)->soft_skill) }}"
                                               class="w-16 rounded-lg border-[#dee1e6] text-center text-sm focus:border-[#0052ff] focus:ring-[#0052ff]" required>
                                    </td>
                                    <td class="border border-[#eef0f3] p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="hard_skill" min="1" max="5" value="{{ old('hard_skill', optional($n)->hard_skill) }}"
                                               class="w-16 rounded-lg border-[#dee1e6] text-center text-sm focus:border-[#0052ff] focus:ring-[#0052ff]" required>
                                    </td>
                                    <td class="border border-[#eef0f3] p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="pengembangan_hard_skill" min="1" max="5" value="{{ old('pengembangan_hard_skill', optional($n)->pengembangan_hard_skill) }}"
                                               class="w-16 rounded-lg border-[#dee1e6] text-center text-sm focus:border-[#0052ff] focus:ring-[#0052ff]" required>
                                    </td>
                                    <td class="border border-[#eef0f3] p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="kewirausahaan" min="1" max="5" value="{{ old('kewirausahaan', optional($n)->kewirausahaan) }}"
                                               class="w-16 rounded-lg border-[#dee1e6] text-center text-sm focus:border-[#0052ff] focus:ring-[#0052ff]" required>
                                    </td>
                                    <td class="border p-2">
                                        <textarea form="form-nilai-{{ $item->id }}" name="catatan_rekomendasi" rows="1" placeholder="Opsional..."
                                                  class="w-full rounded-lg border-[#dee1e6] text-sm focus:border-[#0052ff] focus:ring-[#0052ff]">{{ old('catatan_rekomendasi', optional($n)->catatan_rekomendasi) }}</textarea>
                                    </td>

                                    <td class="border p-2 text-center whitespace-nowrap">
                                        <form id="form-nilai-{{ $item->id }}" action="{{ route('instruktur.nilai.store') }}" method="POST" class="mb-2 block">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $item->id }}">
                                            @if($sudahDinilai)
                                                <div class="mb-2 rounded-full bg-[#05b169]/10 px-2 py-1 text-center text-xs font-semibold text-[#05b169]">&#10003; Sudah Disimpan</div>
                                                <button type="submit"
                                                        class="w-full rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Perbarui</button>
                                            @else
                                                <button type="submit"
                                                        class="w-full rounded-full bg-[#0052ff] px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-[#003ecc]">Simpan</button>
                                            @endif
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
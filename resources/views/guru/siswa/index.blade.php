<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">
            Daftar Siswa Bimbingan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-[#dee1e6] bg-white p-6 md:p-8">

                <h3 class="text-lg font-semibold text-[#0a0b0d] mb-6">Siswa PKL Anda</h3>

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('guru.siswa.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">
                                Cari (Nama / NISN / Kelas / Jurusan / Instruktur)
                            </label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik kata kunci..."
                                   class="w-full rounded-full border-[#dee1e6] bg-[#f7f7f7] px-5 py-2.5 text-sm text-[#0a0b0d] placeholder-[#a8acb3] focus:border-[#0052ff] focus:ring-[#0052ff]">
                        </div>

                        <div class="w-full md:w-64">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-[#7c828a] mb-1">Periode PKL</label>
                            <select name="periode_id"
                                    class="w-full rounded-xl border-[#dee1e6] bg-white px-3 py-2.5 text-sm text-[#0a0b0d] focus:border-[#0052ff] focus:ring-[#0052ff]">
                                <option value="">-- Semua Periode --</option>
                                @foreach($periodes as $periode)
                                    <option value="{{ $periode->id }}" @selected(request('periode_id') == $periode->id)>
                                        {{ $periode->nama }} {{ $periode->tahun_ajaran ? '('.$periode->tahun_ajaran.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-full bg-[#0052ff] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                                Cari
                            </button>
                            <a href="{{ route('guru.siswa.index') }}"
                               class="inline-flex items-center rounded-full bg-[#eef0f3] px-5 py-2.5 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL DAFTAR SISWA ===== --}}
                <div class="overflow-x-auto rounded-2xl border border-[#eef0f3]">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-[#f7f7f7] text-xs uppercase tracking-wide text-[#7c828a]">
                                <th class="px-3 py-3 text-center w-12 font-semibold">No</th>
                                <th class="px-3 py-3 font-semibold">Nama Siswa</th>
                                <th class="px-3 py-3 font-semibold">NISN</th>
                                <th class="px-3 py-3 font-semibold">Kelas</th>
                                <th class="px-3 py-3 font-semibold">Jurusan</th>
                                <th class="px-3 py-3 font-semibold">Nama Instruktur</th>
                                <th class="px-3 py-3 font-semibold">Tempat Industri</th>
                                <th class="px-3 py-3 font-semibold">Periode PKL</th>
                                <th class="px-3 py-3 text-center font-semibold">Aksi Monitoring</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#eef0f3]">
                            @forelse($siswas as $siswa)
                                <tr class="align-top transition hover:bg-[#f7f7f7]">
                                    <td class="px-3 py-3 text-center text-[#7c828a]">{{ $siswas->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-3 font-semibold text-[#0a0b0d]">{{ $siswa->name }}</td>
                                    <td class="px-3 py-3 whitespace-nowrap text-[#5b616e]">{{ $siswa->nisn ?? '-' }}</td>
                                    <td class="px-3 py-3 text-[#5b616e]">{{ $siswa->kelas ?? '-' }}</td>
                                    <td class="px-3 py-3 text-[#5b616e]">{{ $siswa->jurusan ?? '-' }}</td>
                                    <td class="px-3 py-3 text-[#5b616e]">{{ optional($siswa->instruktur)->name ?? '-' }}</td>
                                    <td class="px-3 py-3 text-[#5b616e]">{{ optional($siswa->perusahaan)->nama_perusahaan ?? '-' }}</td>
                                    <td class="px-3 py-3 text-[#5b616e]">
                                        {{ optional($siswa->periode)->nama ?? '-' }}
                                        @if($siswa->periode && $siswa->periode->tahun_ajaran)
                                            <span class="block text-xs text-[#a8acb3]">{{ $siswa->periode->tahun_ajaran }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap justify-center gap-2">
                                            <a href="{{ route('guru.catatan.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Catatan</a>
                                            <a href="{{ route('guru.observasi.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Observasi</a>
                                            <a href="{{ route('guru.nilai.index', ['q' => $siswa->nisn]) }}"
                                               class="rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Rekap Nilai</a>
                                            <a href="{{ route('guru.monitoring.jurnal', ['siswa_id' => $siswa->id]) }}"
                                               class="rounded-full bg-[#eef0f3] px-3 py-1.5 text-xs font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">Jurnal</a>
                                            <a href="{{ route('guru.monitoring.absensi', ['siswa_id' => $siswa->id]) }}"
                                               class="rounded-full bg-[#0052ff] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#003ecc]">Absensi</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-[#a8acb3] italic">
                                        Tidak ada siswa yang cocok dengan pencarian / belum ada siswa bimbingan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $siswas->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
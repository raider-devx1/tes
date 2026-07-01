<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Daftar Siswa Bimbingan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h3 class="text-lg font-bold mb-4">Siswa PKL Anda</h3>

                {{-- ===== FILTER PENCARIAN ===== --}}
                <form method="GET" action="{{ route('guru.siswa.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Cari (Nama / NISN / Kelas / Jurusan / Instruktur)
                            </label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik kata kunci..."
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="w-full md:w-64">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Periode PKL</label>
                            <select name="periode_id"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Periode --</option>
                                @foreach($periodes as $periode)
                                    <option value="{{ $periode->id }}" @selected(request('periode_id') == $periode->id)>
                                        {{ $periode->nama }}{{ $periode->tahun_ajaran ? ' (' . $periode->tahun_ajaran . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                                Cari
                            </button>
                            <a href="{{ route('guru.siswa.index') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-md transition inline-block text-center">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL ===== --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-xs uppercase text-gray-700">
                                <th class="border p-3 text-center w-12">No</th>
                                <th class="border p-3">Nama Siswa</th>
                                <th class="border p-3">NISN</th>
                                <th class="border p-3">Kelas</th>
                                <th class="border p-3">Jurusan</th>
                                <th class="border p-3">Nama Instruktur</th>
                                <th class="border p-3">Tempat Industri</th>
                                <th class="border p-3">Periode PKL</th>
                                <th class="border p-3 text-center">Aksi Monitoring</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswas as $siswa)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="border p-3 text-center">{{ $siswas->firstItem() + $loop->index }}</td>
                                    <td class="border p-3 font-semibold text-gray-900">{{ $siswa->name }}</td>
                                    <td class="border p-3 text-gray-600">{{ $siswa->nisn ?? '-' }}</td>
                                    <td class="border p-3 text-gray-600">{{ $siswa->kelas ?? '-' }}</td>
                                    <td class="border p-3 text-gray-600">{{ $siswa->jurusan ?? '-' }}</td>
                                    <td class="border p-3 text-gray-600">{{ $siswa->instruktur->name ?? '-' }}</td>
                                    <td class="border p-3 text-gray-600">{{ $siswa->perusahaan->nama_perusahaan ?? '-' }}</td>
                                    <td class="border p-3 text-gray-600">
                                        {{ $siswa->periode->nama ?? '-' }}
                                        @if($siswa->periode && $siswa->periode->tahun_ajaran)
                                            <span class="block text-xs text-gray-400">{{ $siswa->periode->tahun_ajaran }}</span>
                                        @endif
                                    </td>
                                   <td class="border p-3">
    <div class="flex flex-wrap justify-center gap-2">
        <a href="{{ route('guru.catatan.index', ['q' => $siswa->nisn]) }}"
           class="bg-purple-500 hover:bg-purple-700 text-white text-xs py-1 px-3 rounded shadow transition text-center">
            Catatan
        </a>
        <a href="{{ route('guru.observasi.index', ['q' => $siswa->nisn]) }}"
           class="bg-amber-500 hover:bg-amber-700 text-white text-xs py-1 px-3 rounded shadow transition text-center">
            Observasi
        </a>
        <a href="{{ route('guru.nilai.index', ['q' => $siswa->nisn]) }}"
           class="bg-indigo-500 hover:bg-indigo-700 text-white text-xs py-1 px-3 rounded shadow transition text-center">
            Rekap Nilai
        </a>
        <a href="{{ route('guru.monitoring.jurnal', ['siswa_id' => $siswa->id]) }}"
           class="bg-blue-500 hover:bg-blue-700 text-white text-xs py-1 px-3 rounded shadow transition text-center">
            Jurnal
        </a>
        <a href="{{ route('guru.monitoring.absensi', ['siswa_id' => $siswa->id]) }}"
           class="bg-green-500 hover:bg-green-700 text-white text-xs py-1 px-3 rounded shadow transition text-center">
            Absensi
        </a>
    </div>
</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="border p-4 text-center text-gray-400 italic">
                                        Tidak ada siswa yang cocok dengan pencarian / belum ada siswa bimbingan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {!! $siswas->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout title="Dokumen Siswa">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dokumen Siswa PKL</h2>
            <p class="text-sm text-gray-500">Pantau & unduh dokumen yang diunggah siswa (hanya-baca). Surat Tugas dikelola global.</p>
        </div>

        {{-- ===== KARTU REKAP ===== --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Total Siswa</p>
                <p class="text-2xl font-bold text-gray-800">{{ $rekap['totalSiswa'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Laporan Akhir</p>
                <p class="text-2xl font-bold text-[#2563EB]">{{ $rekap['laporan'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Surat Penerimaan</p>
                <p class="text-2xl font-bold text-[#2563EB]">{{ $rekap['suratPenerimaan'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Lengkap</p>
                <p class="text-2xl font-bold text-green-600">{{ $rekap['lengkap'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Surat Tugas (Global)</p>
                <p class="text-lg font-bold text-gray-700">{{ $rekap['suratTugas'] }}</p>
                <a href="{{ route('admin.dokumen.surat-tugas') }}" class="text-[11px] text-[#2563EB] hover:underline">Kelola →</a>
            </div>
        </div>

        {{-- ===== FILTER ===== --}}
        <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Nama / NISN"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                <select name="kelas" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    @foreach ($kelasList as $k)
                        <option value="{{ $k }}" @selected($kelas === $k)>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Jurusan</label>
                <select name="jurusan" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    @foreach ($jurusanList as $j)
                        <option value="{{ $j }}" @selected($jurusan === $j)>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    <option value="lengkap" @selected($status === 'lengkap')>Lengkap</option>
                    <option value="sebagian" @selected($status === 'sebagian')>Sebagian</option>
                    <option value="belum" @selected($status === 'belum')>Belum</option>
                </select>
            </div>
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700 transition">Filter</button>
                <a href="{{ route('admin.dokumen.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition inline-block text-center">Reset</a>
            </div>
        </form>

        {{-- ===== TABEL ===== --}}
        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Jurusan</th>
                            <th class="px-4 py-3 text-center">Laporan Akhir</th>
                            <th class="px-4 py-3 text-center">Surat Penerimaan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($siswa as $s)
                            @php
                                $d   = $s->dokumen;
                                $ada = collect([$d?->laporan_akhir, $d?->surat_penerimaan])->filter()->count();
                                [$stLabel, $stClass] = $ada === 2
                                    ? ['Lengkap', 'bg-green-50 text-green-700']
                                    : ($ada === 0 ? ['Belum', 'bg-red-50 text-red-600'] : ['Sebagian', 'bg-amber-50 text-amber-700']);
                            @endphp
                            <tr class="hover:bg-blue-50/40 transition">
                                <td class="px-4 py-3 text-center text-gray-500">{{ $siswa->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $s->name }}
                                    <div class="text-xs text-gray-400">NISN: {{ $s->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $s->kelas ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $s->jurusan ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($d?->laporan_akhir)
                                        <a href="{{ route('dokumen.lihat', [$s->id, 'laporan_akhir']) }}" target="_blank" class="text-[#2563EB] hover:underline font-medium">Lihat PDF</a>
                                    </td>
                                    @else
                                        <span class="text-gray-300">–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($d?->surat_penerimaan)
                                        <a href="{{ route('dokumen.lihat', [$s->id, 'surat_penerimaan']) }}" target="_blank" class="text-[#2563EB] hover:underline font-medium">Lihat PDF</a>
                                    @else
                                        <span class="text-gray-300">–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $stClass }}">{{ $stLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400 italic">Tidak ada data siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== PAGINATION ===== --}}
        <div>
            {!! $siswa->links() !!}
        </div>
    </div>
</x-app-layout>
<x-app-layout title="Dokumen Siswa">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dokumen Siswa PKL</h2>
            <p class="text-sm text-gray-500">Pantau & unduh dokumen yang diunggah siswa (hanya-baca). Surat Tugas dikelola global.</p>
        </div>

        {{-- Rekapitulasi Berkas --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Total Siswa</p>
                <p class="text-2xl font-bold text-gray-800">{{ $rekap['totalSiswa'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Laporan Akhir</p>
                <p class="text-2xl font-bold text-[#2563EB]">{{ $rekap['laporan'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Surat Penerimaan</p>
                <p class="text-2xl font-bold text-[#2563EB]">{{ $rekap['suratPenerimaan'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Lengkap</p>
                <p class="text-2xl font-bold text-green-600">{{ $rekap['lengkap'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Surat Tugas (Global)</p>
                <p class="text-lg font-bold text-gray-700">
                    {{ $rekap['suratTugas'] ?? '-' }}
                </p>
                <a href="{{ route('admin.dokumen.surat-tugas.index') }}" class="text-[11px] text-[#2563EB] hover:underline">Kelola →</a>
            </div>
        </div>

        {{-- Filter Pencarian --}}
        <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Nama / NISN"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
            <a href="{{ route('admin.dokumen.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
        </form>

        {{-- Tabel Utama Monitoring --}}
        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3">Siswa</th>
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
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $s->name }}
                                    <div class="text-xs text-gray-400">NISN: {{ $s->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($d?->laporan_akhir)
                                        <a href="{{ route('dokumen.lihat', [$s->id, 'laporan_akhir']) }}" target="_blank" class="text-[#2563EB] hover:underline">Lihat PDF</a>
                                    @else
                                        <span class="text-gray-300">–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($d?->surat_penerimaan)
                                        <a href="{{ route('dokumen.lihat', [$s->id, 'surat_penerimaan']) }}" target="_blank" class="text-[#2563EB] hover:underline">Lihat PDF</a>
                                    @else
                                        <span class="text-gray-300">–</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $stClass }}">{{ $stLabel }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada data siswa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Navigasi Halaman --}}
        <div>
            {!! $siswa->links() !!}
        </div>
    </div>
</x-app-layout>
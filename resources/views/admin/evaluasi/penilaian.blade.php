<x-app-layout title="Penilaian PKL">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Penilaian PKL</h2>
            <p class="text-sm text-gray-500">Rekap nilai seluruh siswa PKL — termasuk yang belum dinilai (hanya-baca).</p>
        </div>

        {{-- FILTER --}}
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
                    <option value="sudah" @selected($status === 'sudah')>Sudah Dinilai</option>
                    <option value="belum" @selected($status === 'belum')>Belum Dinilai</option>
                </select>
            </div>
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
                <a href="{{ route('admin.evaluasi.penilaian') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            </div>
        </form>

        {{-- TABEL --}}
        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Jurusan</th>
                            <th class="px-4 py-3 text-center">Soft</th>
                            <th class="px-4 py-3 text-center">Hard</th>
                            <th class="px-4 py-3 text-center">Pengemb.</th>
                            <th class="px-4 py-3 text-center">Wirausaha</th>
                            <th class="px-4 py-3 text-center">Rata (1-5)</th>
                            <th class="px-4 py-3 text-center">N. Guru</th>
                            <th class="px-4 py-3 text-center">N. Laporan</th>
                            <th class="px-4 py-3 text-center">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($siswa as $s)
                            @php
                                $n      = $s->nilai; // bisa null kalau belum dinilai
                                $akhir  = $n?->nilai_akhir;
                                $sudah  = $n && ! is_null($akhir);
                                $gradeBadge = ! $sudah ? 'bg-gray-100 text-gray-500'
                                    : ($akhir >= 85 ? 'bg-green-50 text-green-700'
                                    : ($akhir >= 70 ? 'bg-blue-50 text-blue-700'
                                    : ($akhir >= 60 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600')));
                            @endphp
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-center text-gray-500">
    {{  $siswa->firstItem() + $loop->index }}
</td>
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $s->name }}
                                    <div class="text-xs text-gray-400">NISN: {{ $s->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $s->kelas ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $s->jurusan ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $n?->soft_skill ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $n?->hard_skill ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $n?->pengembangan_hard_skill ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $n?->kewirausahaan ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-medium">
                                    {{ is_null($n?->rata_rata) ? '-' : number_format($n->rata_rata, 2) }}
                                </td>
                                <td class="px-4 py-3 text-center">{{ $n?->nilai_guru ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $n?->nilai_laporan ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $gradeBadge }}">
                                        {{ ! $sudah ? 'Belum' : number_format($akhir, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="px-4 py-8 text-center text-gray-400">Tidak ada siswa PKL.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div>
            {!! $siswa->links() !!}
        </div>
    </div>
</x-app-layout>
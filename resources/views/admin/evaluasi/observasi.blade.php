<x-app-layout title="Observasi Guru">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Observasi Guru Pembimbing</h2>
            <p class="text-sm text-gray-500">Pantau hasil observasi guru terhadap siswa PKL (hanya-baca).</p>
        </div>

        {{-- ===== KARTU INFORMASI ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium text-gray-500">Total Observasi</p>
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">🔍</span>
        </div>
        <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['total'] }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium text-gray-500">Disetujui</p>
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-green-50 text-green-600">✅</span>
        </div>
        <p class="mt-2 text-2xl font-bold text-green-600">{{ $rekap['disetujui'] }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium text-gray-500">Belum Disetujui</p>
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600">⏳</span>
        </div>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ $rekap['belum'] }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
        <div class="flex items-center justify-between">
            <p class="text-xs font-medium text-gray-500">Guru Terlibat</p>
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-50 text-[#2563EB]">🧑‍🏫</span>
        </div>
        <p class="mt-2 text-2xl font-bold text-[#2563EB]">{{ $rekap['guru'] }}</p>
    </div>
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
                    <option value="1" @selected($status === '1')>Disetujui</option>
                    <option value="0" @selected($status === '0')>Belum</option>
                </select>
            </div>
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
                <a href="{{ route('admin.evaluasi.observasi') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            </div>
        </form>

        {{-- TABEL --}}
        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3 w-12 text-center">No</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Jurusan</th>
                            <th class="px-4 py-3">Guru</th>
                            <th class="px-4 py-3">Pekerjaan / Projek</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($observasi as $o)
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-center text-gray-500">
    {{ $observasi->firstItem() + $loop->index }}
</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($o->hari_tanggal)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $o->user->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $o->user->kelas ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $o->user->jurusan ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $o->guru->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $o->pekerjaan_projek ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($o->is_approved)
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">Disetujui</span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Belum</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada data observasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINATION (15/halaman) --}}
        <div>
            {!! $observasi->links() !!}
        </div>
    </div>
</x-app-layout>
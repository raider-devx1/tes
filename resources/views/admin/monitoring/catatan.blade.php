<x-app-layout title="Monitoring Catatan">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Monitoring Catatan Kegiatan</h2>
            <p class="text-sm text-gray-500">Pantau catatan kegiatan siswa PKL (hanya-baca).</p>
        </div>

        {{-- Filter --}}
        <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Nama / NISN"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                <select name="kelas" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k }}" {{ $kelas === $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Jurusan</label>
                <select name="jurusan" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    @foreach($jurusanList as $jr)
                        <option value="{{ $jr }}" {{ $jurusan === $jr ? 'selected' : '' }}>{{ $jr }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="approved" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    <option value="1" {{ $approved === '1' ? 'selected' : '' }}>Disetujui</option>
                    <option value="0" {{ $approved === '0' ? 'selected' : '' }}>Belum</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
            <a href="{{ route('admin.monitoring.catatan') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
        </form>

        {{-- Tabel Data --}}
        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Jurusan</th>
                            <th class="px-4 py-3">Nama Pekerjaan</th>
                            <th class="px-4 py-3">Perencanaan</th>
                            <th class="px-4 py-3">Pelaksanaan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($catatan as $c)
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-center text-gray-500">{{ ($catatan->currentPage() - 1) * $catatan->perPage() + $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $c->user->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $c->user->kelas ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $c->user->jurusan ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $c->nama_pekerjaan }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ \Illuminate\Support\Str::limit($c->perencanaan_kegiatan, 60) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ \Illuminate\Support\Str::limit($c->pelaksanaan_kegiatan, 60) }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($c->is_approved)
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">Disetujui</span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Belum</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada data catatan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div>
            {!! $catatan->links() !!}
        </div>
    </div>
</x-app-layout>
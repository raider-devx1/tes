<x-app-layout title="Observasi Guru">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Observasi Guru Pembimbing</h2>
            <p class="text-sm text-gray-500">Pantau hasil observasi guru terhadap siswa PKL (hanya-baca).</p>
        </div>

        <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Nama / NISN"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
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
            <a href="{{ route('admin.evaluasi.observasi') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
        </form>

        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Guru</th>
                            <th class="px-4 py-3">Pekerjaan / Projek</th>
                            <th class="px-4 py-3">Permasalahan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($observasi as $o)
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($o->hari_tanggal)->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $o->user->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $o->guru->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ \Illuminate\Support\Str::limit($o->pekerjaan_projek, 50) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ \Illuminate\Support\Str::limit($o->permasalahan, 50) }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($o->is_approved)
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">Disetujui</span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Belum</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada data observasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {!! $observasi->links() !!}
        </div>
    </div>
</x-app-layout>
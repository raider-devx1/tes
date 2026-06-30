<x-app-layout title="Data Industri Mitra">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Master Data — Industri Mitra</h2>
            <p class="text-sm text-gray-500">Kelola perusahaan/instansi tempat pelaksanaan PKL.</p>
        </div>
        <a href="{{ route('admin.industri.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
            + Tambah Industri
        </a>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">

        <form method="GET" class="mb-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / bidang / alamat..."
                   class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            <button class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
            @if($q)
                <a href="{{ route('admin.industri.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-blue-100">
                        <th class="py-3 px-3">Nama Perusahaan</th>
                        <th class="py-3 px-3">Bidang Usaha</th>
                        <th class="py-3 px-3">Pembimbing</th>
                        <th class="py-3 px-3">Kontak</th>
                        <th class="py-3 px-3 text-center">Kuota (Terisi)</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($industri as $i)
                        <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                            <td class="py-3 px-3">
                                <div class="font-medium text-gray-800">{{ $i->nama_perusahaan }}</div>
                                <div class="text-xs text-gray-400">{{ $i->alamat }}</div>
                            </td>
                            <td class="py-3 px-3 text-gray-600">{{ $i->bidang_usaha ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $i->pembimbing_industri ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-600">
                                <div>{{ $i->telepon ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $i->email }}</div>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @php $penuh = $i->kuota > 0 && $i->siswa_count >= $i->kuota; @endphp
                                <span class="text-xs px-2 py-1 rounded-full {{ $penuh ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                    {{ $i->siswa_count }} / {{ $i->kuota }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.industri.edit', $i) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.industri.destroy', $i) }}" onsubmit="return confirm('Hapus industri ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-gray-400">Belum ada data industri.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {!! $industri->links() !!}
        </div>
    </div>

</x-app-layout>
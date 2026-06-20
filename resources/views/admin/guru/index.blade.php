<x-app-layout title="Akun Guru Pembimbing">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Master Data — Guru Pembimbing</h2>
            <p class="text-sm text-gray-500">Kelola akun guru pembimbing PKL.</p>
        </div>
        <a href="{{ route('admin.guru.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
            + Tambah Guru
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">

        <form method="GET" class="mb-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / email / NIP..."
                   class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            <button class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
            @if($q)
                <a href="{{ route('admin.guru.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-blue-100">
                        <th class="py-3 px-3">Nama</th>
                        <th class="py-3 px-3">Email</th>
                        <th class="py-3 px-3">NIP</th>
                        <th class="py-3 px-3">No. HP</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guru as $g)
                        <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                            <td class="py-3 px-3 font-medium text-gray-800">{{ $g->name }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $g->email }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $g->nip ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $g->no_hp ?? '-' }}</td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.guru.edit', $g) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.guru.destroy', $g) }}" onsubmit="return confirm('Hapus akun guru ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada akun guru pembimbing.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {!! $guru->links() !!}
        </div>
    </div>

</x-app-layout>
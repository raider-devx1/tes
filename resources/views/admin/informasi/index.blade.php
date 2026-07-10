<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kelola Informasi PKL
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex justify-between items-center mb-6">
                        <a href="{{ route('admin.informasi.create') }}"
                           class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                             Tambah Informasi
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 border">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 border text-center">Urutan</th>
                                    <th class="px-4 py-3 border">Judul</th>
                                    <th class="px-4 py-3 border">Konten</th>
                                    <th class="px-4 py-3 border text-center">Lampiran</th>
                                    <th class="px-4 py-3 border text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($informasi as $item)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 border text-center">{{ $item->urutan }}</td>
                                        <td class="px-4 py-3 border font-medium text-gray-900">{{ $item->judul }}</td>
                                        <td class="px-4 py-3 border">{!! \Illuminate\Support\Str::limit(strip_tags($item->konten), 80) !!}</td>
                                        <td class="px-4 py-3 border text-center">
                                            @if(!empty($item->file))
                                                <a href="{{ asset('storage/' . $item->file) }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                                            </td>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 border text-center whitespace-nowrap">
                                            <a href="{{ route('admin.informasi.edit', $item) }}"
                                               class="text-blue-600 hover:underline">Edit</a>
                                           <form action="{{ route('admin.informasi.destroy', $item) }}" method="POST" class="inline"
      data-confirm="Hapus informasi ini?"
      data-confirm-text="Data informasi beserta lampirannya akan dihapus permanen."
      data-confirm-yes="Ya, hapus">
    @csrf
    @method('DELETE')

    <!-- Tombol submit untuk trigger form, contoh: -->
    <button type="submit" class="text-red-600 hover:underline ml-2">Hapus</button>
</form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center">Belum ada informasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
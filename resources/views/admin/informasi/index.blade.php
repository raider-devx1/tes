<x-app-layout>
    <x-page-header title="Informasi & Panduan" subtitle="Konten Panduan PKL">
        <x-slot:action><a href="{{ route('admin.informasi.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Tambah</a></x-slot:action>
    </x-page-header>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Judul</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Urutan</th><th class="px-4 py-3">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($informasis as $i)
                    <tr><td class="px-4 py-3 font-medium text-slate-800">{{ $i->judul }}</td><td class="px-4 py-3 capitalize text-slate-500">{{ $i->kategori }}</td><td class="px-4 py-3 text-slate-500">{{ $i->urutan }}</td>
                        <td class="px-4 py-3"><div class="flex gap-3"><a href="{{ route('admin.informasi.edit', $i) }}" class="text-sm text-indigo-600 hover:underline">Edit</a><form method="POST" action="{{ route('admin.informasi.destroy', $i) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Hapus</button></form></div></td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Belum ada informasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>

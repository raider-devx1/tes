<x-app-layout>
    <x-page-header :title="$informasi->exists ? 'Edit Informasi' : 'Tambah Informasi'" />
    <form method="POST" action="{{ $informasi->exists ? route('admin.informasi.update', $informasi) : route('admin.informasi.store') }}" class="max-w-2xl space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
        @if($informasi->exists) @method('PUT') @endif
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Judul</label><input type="text" name="judul" value="{{ old('judul', $informasi->judul) }}" required class="w-full rounded-lg border-slate-300 text-sm"></div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="mb-1 block text-sm font-medium text-slate-700">Kategori</label><input type="text" name="kategori" value="{{ old('kategori', $informasi->kategori) }}" placeholder="mis. Umum / Laporan / Presentasi" required class="w-full rounded-lg border-slate-300 text-sm"></div>
            <div><label class="mb-1 block text-sm font-medium text-slate-700">Urutan</label><input type="number" name="urutan" value="{{ old('urutan', $informasi->urutan ?? 0) }}" class="w-full rounded-lg border-slate-300 text-sm"></div>
        </div>
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Konten</label><textarea name="konten" rows="8" required class="w-full rounded-lg border-slate-300 text-sm">{{ old('konten', $informasi->konten) }}</textarea></div>
        <div class="flex gap-3"><button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button><a href="{{ route('admin.informasi.index') }}" class="rounded-lg border border-slate-300 px-5 py-2 text-sm font-medium text-slate-600">Batal</a></div>
    </form>
</x-app-layout>

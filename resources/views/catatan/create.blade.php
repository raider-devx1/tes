<x-app-layout>
    <x-page-header title="Tambah Catatan Kegiatan" />
    <form method="POST" action="{{ route('catatan.store') }}" class="max-w-2xl space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Nama Pekerjaan</label><input type="text" name="nama_pekerjaan" value="{{ old('nama_pekerjaan') }}" required class="w-full rounded-lg border-slate-300 text-sm"></div>
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Perencanaan</label><textarea name="perencanaan" rows="4" required class="w-full rounded-lg border-slate-300 text-sm">{{ old('perencanaan') }}</textarea></div>
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Pelaksanaan / Hasil</label><textarea name="pelaksanaan" rows="4" required class="w-full rounded-lg border-slate-300 text-sm">{{ old('pelaksanaan') }}</textarea></div>
        <div class="flex gap-3"><button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button><a href="{{ route('catatan.index') }}" class="rounded-lg border border-slate-300 px-5 py-2 text-sm font-medium text-slate-600">Batal</a></div>
    </form>
</x-app-layout>

<x-app-layout>
    <x-page-header title="Tambah Jurnal Kegiatan" subtitle="Isi kegiatan harian PKL Anda" />
    <form method="POST" action="{{ route('jurnal.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Hari / Tanggal</label><input type="date" name="hari_tanggal" value="{{ old('hari_tanggal', date('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Unit Kerja / Divisi</label><input type="text" name="unit_kerja" value="{{ old('unit_kerja') }}" required class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></div>
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi Pekerjaan</label><textarea name="deskripsi_pekerjaan" rows="5" required class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('deskripsi_pekerjaan') }}</textarea></div>
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Dokumentasi Foto (opsional)</label><input type="file" name="dokumentasi" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700"></div>
        <div class="flex gap-3"><button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button><a href="{{ route('jurnal.index') }}" class="rounded-lg border border-slate-300 px-5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a></div>
    </form>
</x-app-layout>

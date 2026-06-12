<x-app-layout>
    <x-page-header title="Tambah Lembar Observasi" />
    <form method="POST" action="{{ route('observasi.store') }}" class="max-w-2xl space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">@csrf
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Siswa</label>
            <select name="siswa_id" required class="w-full rounded-lg border-slate-300 text-sm"><option value="">-- Pilih Siswa --</option>@foreach($siswas as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
        </div>
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Hari / Tanggal</label><input type="date" name="hari_tanggal" value="{{ old('hari_tanggal', date('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 text-sm"></div>
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Permasalahan</label><textarea name="permasalahan" rows="4" required class="w-full rounded-lg border-slate-300 text-sm">{{ old('permasalahan') }}</textarea></div>
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Solusi</label><textarea name="solusi" rows="4" required class="w-full rounded-lg border-slate-300 text-sm">{{ old('solusi') }}</textarea></div>
        <div class="flex gap-3"><button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button><a href="{{ route('observasi.index') }}" class="rounded-lg border border-slate-300 px-5 py-2 text-sm font-medium text-slate-600">Batal</a></div>
    </form>
</x-app-layout>

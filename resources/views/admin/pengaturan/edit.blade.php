<x-app-layout>
    <x-page-header title="Pengaturan" subtitle="Identitas sekolah untuk dokumen & cetak" />
    <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="max-w-2xl space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
        @foreach(['nama_sekolah' => 'Nama Sekolah', 'tahun_ajaran' => 'Tahun Ajaran', 'kepala_sekolah' => 'Kepala Sekolah', 'nip_kepala' => 'NIP Kepala Sekolah'] as $field => $label)
            <div><label class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label><input type="text" name="{{ $field }}" value="{{ $pengaturan[$field] ?? '' }}" class="w-full rounded-lg border-slate-300 text-sm"></div>
        @endforeach
        <div><label class="mb-1 block text-sm font-medium text-slate-700">Alamat Sekolah</label><textarea name="alamat_sekolah" rows="3" class="w-full rounded-lg border-slate-300 text-sm">{{ $pengaturan['alamat_sekolah'] ?? '' }}</textarea></div>
        <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
    </form>
</x-app-layout>

<x-app-layout>
    <x-page-header title="Dokumen PKL" subtitle="Surat tugas, surat penerimaan, laporan final" />
    @if(auth()->user()->isSiswa())
        <form method="POST" action="{{ route('dokumen.store') }}" enctype="multipart/form-data" class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-4">@csrf
            <select name="jenis" required class="rounded-lg border-slate-300 text-sm"><option value="surat_tugas">Surat Tugas</option><option value="surat_penerimaan">Surat Penerimaan</option><option value="laporan_final">Laporan Final</option><option value="lainnya">Lainnya</option></select>
            <input type="text" name="judul" placeholder="Judul dokumen" required class="rounded-lg border-slate-300 text-sm">
            <input type="file" name="file" required class="text-sm text-slate-500 file:mr-2 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:text-indigo-700">
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Unggah</button>
        </form>
    @endif
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($dokumens as $d)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-indigo-500">{{ str_replace('_', ' ', $d->jenis) }}</p>
                <p class="font-medium text-slate-800">{{ $d->judul }}</p>
                <p class="text-xs text-slate-400">{{ $d->siswa->name }} &middot; {{ $d->created_at->format('d M Y') }}</p>
                <div class="mt-3 flex items-center gap-3">
                    <a href="{{ asset('storage/' . $d->path) }}" target="_blank" class="text-sm text-indigo-600 hover:underline">Lihat</a>
                    @if(auth()->user()->isSiswa() && $d->siswa_id === auth()->id())
                        <form method="POST" action="{{ route('dokumen.destroy', $d) }}" onsubmit="return confirm('Hapus dokumen ini?')">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Hapus</button></form>
                    @endif
                </div>
            </div>
        @empty
            <p class="col-span-full rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-slate-400">Belum ada dokumen.</p>
        @endforelse
    </div>
</x-app-layout>

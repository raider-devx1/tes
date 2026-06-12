<x-app-layout>
    <x-page-header title="Data Industri" subtitle="Perusahaan mitra PKL" />
    <details class="mb-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <summary class="cursor-pointer text-sm font-medium text-indigo-600">+ Tambah Industri</summary>
        <form method="POST" action="{{ route('admin.perusahaan.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf
            <input type="text" name="nama" placeholder="Nama industri" required class="rounded-lg border-slate-300 text-sm">
            <input type="text" name="bidang" placeholder="Bidang usaha" class="rounded-lg border-slate-300 text-sm">
            <input type="text" name="pembimbing_industri" placeholder="Pembimbing industri" class="rounded-lg border-slate-300 text-sm">
            <input type="text" name="telepon" placeholder="Telepon" class="rounded-lg border-slate-300 text-sm">
            <textarea name="alamat" placeholder="Alamat" class="sm:col-span-2 rounded-lg border-slate-300 text-sm"></textarea>
            <div class="sm:col-span-2"><button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button></div>
        </form>
    </details>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Bidang</th><th class="px-4 py-3">Pembimbing</th><th class="px-4 py-3">Siswa</th><th class="px-4 py-3">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($perusahaans as $p)
                    <tr><td class="px-4 py-3 font-medium text-slate-800">{{ $p->nama }}</td><td class="px-4 py-3 text-slate-500">{{ $p->bidang ?? '-' }}</td><td class="px-4 py-3 text-slate-500">{{ $p->pembimbing_industri ?? '-' }}</td><td class="px-4 py-3 text-slate-500">{{ $p->siswas_count }}</td>
                        <td class="px-4 py-3"><form method="POST" action="{{ route('admin.perusahaan.destroy', $p) }}" onsubmit="return confirm('Hapus industri ini?')">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Hapus</button></form></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada data industri.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $perusahaans->links() }}</div>
</x-app-layout>

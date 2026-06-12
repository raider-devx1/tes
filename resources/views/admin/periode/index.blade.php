<x-app-layout>
    <x-page-header title="Periode PKL" subtitle="Atur periode pelaksanaan" />
    <details class="mb-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <summary class="cursor-pointer text-sm font-medium text-indigo-600">+ Tambah Periode</summary>
        <form method="POST" action="{{ route('admin.periode.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf
            <input type="text" name="nama" placeholder="Nama periode (mis. PKL Gel. 1 2026)" required class="sm:col-span-2 rounded-lg border-slate-300 text-sm">
            <input type="date" name="tanggal_mulai" required class="rounded-lg border-slate-300 text-sm">
            <input type="date" name="tanggal_selesai" required class="rounded-lg border-slate-300 text-sm">
            <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="aktif" value="1" class="rounded border-slate-300"> Jadikan periode aktif</label>
            <div class="sm:col-span-2"><button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button></div>
        </form>
    </details>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Mulai</th><th class="px-4 py-3">Selesai</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($periodes as $p)
                    <tr><td class="px-4 py-3 font-medium text-slate-800">{{ $p->nama }}</td><td class="px-4 py-3 text-slate-500">{{ $p->tanggal_mulai->format('d M Y') }}</td><td class="px-4 py-3 text-slate-500">{{ $p->tanggal_selesai->format('d M Y') }}</td>
                        <td class="px-4 py-3">@if($p->aktif)<span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Aktif</span>@else<span class="text-xs text-slate-400">Nonaktif</span>@endif</td>
                        <td class="px-4 py-3"><div class="flex gap-3">
                            @unless($p->aktif)<form method="POST" action="{{ route('admin.periode.aktifkan', $p) }}">@csrf @method('PUT')<button class="text-sm text-indigo-600 hover:underline">Aktifkan</button></form>@endunless
                            <form method="POST" action="{{ route('admin.periode.destroy', $p) }}" onsubmit="return confirm('Hapus periode ini?')">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Hapus</button></form>
                        </div></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada periode.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $periodes->links() }}</div>
</x-app-layout>

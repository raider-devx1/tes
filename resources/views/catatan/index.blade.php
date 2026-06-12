<x-app-layout>
    <x-page-header title="Catatan Kegiatan" subtitle="Rencana & pelaksanaan pekerjaan">
        @if(auth()->user()->isSiswa())<x-slot:action><a href="{{ route('catatan.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Tambah Catatan</a></x-slot:action>@endif
    </x-page-header>
    <div class="space-y-4">
        @forelse($catatans as $c)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2"><h3 class="font-semibold text-slate-800">{{ $c->nama_pekerjaan }}</h3><x-status-badge :status="$c->status_persetujuan" /></div>
                <p class="text-xs text-slate-400">{{ $c->siswa->name }}</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-slate-50 p-3"><p class="text-xs font-semibold text-slate-500">Perencanaan</p><p class="text-sm text-slate-700">{{ $c->perencanaan }}</p></div>
                    <div class="rounded-lg bg-slate-50 p-3"><p class="text-xs font-semibold text-slate-500">Pelaksanaan / Hasil</p><p class="text-sm text-slate-700">{{ $c->pelaksanaan }}</p></div>
                </div>
                @if($c->catatan_instruktur)<p class="mt-2 rounded-lg bg-indigo-50 px-3 py-2 text-sm text-indigo-700"><span class="font-medium">Catatan Instruktur:</span> {{ $c->catatan_instruktur }}</p>@endif
                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                    @if(auth()->user()->isInstruktur() && $c->status_persetujuan !== 'disetujui')
                        <form method="POST" action="{{ route('catatan.approve', $c) }}" class="flex flex-wrap items-center gap-2">@csrf @method('PUT')
                            <input type="text" name="catatan_instruktur" placeholder="Catatan (opsional)" class="rounded-lg border-slate-300 text-sm">
                            <button name="status_persetujuan" value="disetujui" class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white">Setujui</button>
                            <button name="status_persetujuan" value="revisi" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white">Revisi</button>
                        </form>
                    @endif
                    @if(auth()->user()->isSiswa() && $c->status_persetujuan === 'pending')
                        <form method="POST" action="{{ route('catatan.destroy', $c) }}" onsubmit="return confirm('Hapus catatan ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">Hapus</button></form>
                    @endif
                </div>
            </div>
        @empty
            <p class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-slate-400">Belum ada catatan kegiatan.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $catatans->links() }}</div>
</x-app-layout>

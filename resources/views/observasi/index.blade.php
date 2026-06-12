<x-app-layout>
    <x-page-header title="Lembar Observasi" subtitle="Permasalahan & solusi selama PKL">
        @if(auth()->user()->isGuru())<x-slot:action><a href="{{ route('observasi.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Tambah Observasi</a></x-slot:action>@endif
    </x-page-header>
    <div class="space-y-4">
        @forelse($observasis as $o)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2"><h3 class="font-semibold text-slate-800">{{ $o->siswa->name }}</h3><x-status-badge :status="$o->status_persetujuan" /></div>
                <p class="text-xs text-slate-400">{{ $o->hari_tanggal->format('d M Y') }} &middot; Pembimbing: {{ $o->guru->name }}</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-red-50 p-3"><p class="text-xs font-semibold text-red-500">Permasalahan</p><p class="text-sm text-slate-700">{{ $o->permasalahan }}</p></div>
                    <div class="rounded-lg bg-green-50 p-3"><p class="text-xs font-semibold text-green-600">Solusi</p><p class="text-sm text-slate-700">{{ $o->solusi }}</p></div>
                </div>
                <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3">
                    @if(auth()->user()->isInstruktur() && $o->status_persetujuan !== 'disetujui')
                        <form method="POST" action="{{ route('observasi.approve', $o) }}">@csrf @method('PUT')<button class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white">Setujui</button></form>
                    @endif
                    @if(auth()->user()->isGuru())
                        <form method="POST" action="{{ route('observasi.destroy', $o) }}" onsubmit="return confirm('Hapus observasi ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">Hapus</button></form>
                    @endif
                </div>
            </div>
        @empty
            <p class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-slate-400">Belum ada lembar observasi.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $observasis->links() }}</div>
</x-app-layout>

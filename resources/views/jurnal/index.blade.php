<x-app-layout>
    <x-page-header title="Jurnal Kegiatan" subtitle="Catatan kegiatan harian PKL">
        @if(auth()->user()->isSiswa())
            <x-slot:action><a href="{{ route('jurnal.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">+ Tambah Jurnal</a></x-slot:action>
        @endif
    </x-page-header>
    <div class="space-y-4">
        @forelse($jurnals as $j)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2"><h3 class="font-semibold text-slate-800">{{ $j->unit_kerja }}</h3><x-status-badge :status="$j->status_persetujuan" /></div>
                        <p class="text-xs text-slate-400">{{ $j->hari_tanggal->format('d M Y') }} &middot; {{ $j->siswa->name }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $j->deskripsi_pekerjaan }}</p>
                        @if($j->catatan_instruktur)<p class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600"><span class="font-medium">Catatan Instruktur:</span> {{ $j->catatan_instruktur }}</p>@endif
                    </div>
                    @if($j->dokumentasi)<img src="{{ asset('storage/' . $j->dokumentasi) }}" alt="Dokumentasi" class="h-24 w-24 shrink-0 rounded-lg object-cover">@endif
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
                    @if(auth()->user()->isInstruktur() && $j->status_persetujuan !== 'disetujui')
                        <form method="POST" action="{{ route('jurnal.approve', $j) }}" class="flex flex-wrap items-center gap-2">@csrf @method('PUT')
                            <input type="text" name="catatan_instruktur" placeholder="Catatan (opsional)" class="rounded-lg border-slate-300 text-sm">
                            <button name="status_persetujuan" value="disetujui" class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">Setujui</button>
                            <button name="status_persetujuan" value="revisi" class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">Revisi</button>
                        </form>
                    @endif
                    @if(auth()->user()->isSiswa() && $j->status_persetujuan === 'pending')
                        <form method="POST" action="{{ route('jurnal.destroy', $j) }}" onsubmit="return confirm('Hapus jurnal ini?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">Hapus</button></form>
                    @endif
                    <a href="{{ route('cetak.jurnal', auth()->user()->isSiswa() ? null : $j->siswa_id) }}" target="_blank" class="ml-auto text-sm text-indigo-600 hover:underline">Cetak PDF</a>
                </div>
            </div>
        @empty
            <p class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-slate-400">Belum ada jurnal.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $jurnals->links() }}</div>
</x-app-layout>

<x-app-layout>
    <x-page-header title="Dashboard Guru Pembimbing" subtitle="Monitoring siswa bimbingan" />
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Siswa Bimbingan" :value="$stat['siswa']" color="indigo" icon="M15 19.13a9.38 9.38 0 002.63.37" />
        <x-stat-card label="Observasi Dibuat" :value="$stat['observasi']" color="blue" icon="M2.04 12.32a1 1 0 010-.64" />
        <x-stat-card label="Jurnal Disetujui" :value="$stat['jurnal_disetujui']" color="green" icon="M4.5 12.75l6 6 9-13.5" />
    </div>
    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Nama Siswa</th><th class="px-5 py-3">NIS</th><th class="px-5 py-3">Jumlah Jurnal</th><th class="px-5 py-3">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($siswas as $s)
                    <tr>
                        <td class="px-5 py-3 font-medium text-slate-800">{{ $s->name }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $s->nis ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $s->jurnals_count }}</td>
                        <td class="px-5 py-3"><a href="{{ route('cetak.jurnal', $s->id) }}" class="text-indigo-600 hover:underline">Cetak Jurnal</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-slate-400">Belum ada siswa bimbingan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>

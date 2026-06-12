<x-app-layout>
    <x-page-header title="Dashboard Instruktur Industri" subtitle="Persetujuan & penilaian siswa" />
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Siswa Bimbingan" :value="$stat['siswa']" color="indigo" icon="M15 19.13a9.38 9.38 0 002.63.37" />
        <x-stat-card label="Jurnal Menunggu" :value="$stat['jurnal_pending']" color="yellow" icon="M12 6v6h4.5" />
        <x-stat-card label="Hadir Hari Ini" :value="$stat['hadir_hari_ini']" color="green" icon="M6.75 3v2.25" />
    </div>
    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <a href="{{ route('jurnal.index') }}" class="rounded-xl border border-slate-200 bg-white p-5 text-center text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-300">Setujui Jurnal</a>
        <a href="{{ route('absensi.index') }}" class="rounded-xl border border-slate-200 bg-white p-5 text-center text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-300">Input Absensi</a>
        <a href="{{ route('nilai.index') }}" class="rounded-xl border border-slate-200 bg-white p-5 text-center text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-300">Input Nilai</a>
        <a href="{{ route('catatan.index') }}" class="rounded-xl border border-slate-200 bg-white p-5 text-center text-sm font-medium text-slate-700 shadow-sm hover:border-indigo-300">Catatan</a>
    </div>
    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Nama Siswa</th><th class="px-5 py-3">NIS</th><th class="px-5 py-3">Kelas</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($siswas as $s)
                    <tr><td class="px-5 py-3 font-medium text-slate-800">{{ $s->name }}</td><td class="px-5 py-3 text-slate-500">{{ $s->nis ?? '-' }}</td><td class="px-5 py-3 text-slate-500">{{ $s->kelas ?? '-' }}</td></tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-6 text-center text-slate-400">Belum ada siswa bimbingan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>

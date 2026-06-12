<x-app-layout>
    <x-page-header title="Dashboard Siswa" :subtitle="'Selamat datang, ' . auth()->user()->name" />
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total Jurnal" :value="$stat['jurnal']" color="indigo" icon="M12 6.75v10.5" />
        <x-stat-card label="Disetujui" :value="$stat['jurnal_disetujui']" color="green" icon="M4.5 12.75l6 6 9-13.5" />
        <x-stat-card label="Menunggu" :value="$stat['jurnal_pending']" color="yellow" icon="M12 6v6h4.5" />
        <x-stat-card label="Hari Hadir" :value="$stat['hadir']" color="blue" icon="M6.75 3v2.25" />
    </div>
    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-700">Jurnal Terakhir</h2>
            <a href="{{ route('jurnal.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">Lihat semua</a>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($jurnalTerakhir as $j)
                <li class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $j->unit_kerja }}</p>
                        <p class="text-xs text-slate-400">{{ $j->hari_tanggal->format('d M Y') }}</p>
                    </div>
                    <x-status-badge :status="$j->status_persetujuan" />
                </li>
            @empty
                <li class="px-5 py-6 text-center text-sm text-slate-400">Belum ada jurnal. Mulai isi jurnal harian Anda.</li>
            @endforelse
        </ul>
    </div>
</x-app-layout>

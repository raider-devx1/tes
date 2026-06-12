<x-app-layout>
    <x-page-header title="Dashboard Admin" subtitle="Ringkasan monitoring PKL" />
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Siswa PKL" :value="$stat['siswa']" color="indigo" icon="M15 19.13a9.38 9.38 0 002.63.37" />
        <x-stat-card label="Guru Pembimbing" :value="$stat['guru']" color="blue" icon="M12 6.04A8.97 8.97 0 006 3.75" />
        <x-stat-card label="Instruktur" :value="$stat['instruktur']" color="green" icon="M12 6.04A8.97 8.97 0 006 3.75" />
        <x-stat-card label="Industri Mitra" :value="$stat['perusahaan']" color="yellow" icon="M3.75 21h16.5" />
    </div>
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat-card label="Jurnal Menunggu" :value="$stat['jurnal_pending']" color="yellow" icon="M12 6v6h4.5" />
        <x-stat-card label="Jurnal Disetujui" :value="$stat['jurnal_disetujui']" color="green" icon="M4.5 12.75l6 6 9-13.5" />
    </div>
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-sm font-semibold text-slate-700">Aktivitas Jurnal 7 Hari Terakhir</h2>
        @php $maks = max($grafik->max() ?? 0, 1); @endphp
        <div class="flex items-end gap-2" style="height:160px">
            @forelse($grafik as $tgl => $total)
                <div class="flex flex-1 flex-col items-center justify-end gap-1">
                    <div class="w-full rounded-t bg-indigo-500" style="height: {{ round(($total / $maks) * 100) }}%"></div>
                    <span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($tgl)->format('d/m') }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada data jurnal.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>

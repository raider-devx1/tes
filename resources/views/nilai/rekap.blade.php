<x-app-layout>
    <x-page-header title="Rekap Penilaian" subtitle="Nilai siswa bimbingan" />
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Soft</th><th class="px-4 py-3">Hard</th><th class="px-4 py-3">Peng.</th><th class="px-4 py-3">Wira</th><th class="px-4 py-3">Rata-rata</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($nilais as $n)
                    <tr><td class="px-4 py-3 font-medium text-slate-800">{{ $n->siswa->name }}</td><td class="px-4 py-3">{{ $n->soft_skill }}</td><td class="px-4 py-3">{{ $n->hard_skill }}</td><td class="px-4 py-3">{{ $n->pengembangan_hard_skill }}</td><td class="px-4 py-3">{{ $n->kewirausahaan }}</td><td class="px-4 py-3 font-semibold text-indigo-700">{{ $n->rata_rata }}</td></tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-400">Belum ada nilai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>

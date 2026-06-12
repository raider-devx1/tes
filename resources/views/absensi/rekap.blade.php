<x-app-layout>
    <x-page-header title="Rekap Absensi" subtitle="Daftar kehadiran PKL" />
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Masuk</th><th class="px-4 py-3">Pulang</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($absensis as $a)
                    <tr>
                        <td class="px-4 py-3 text-slate-600">{{ $a->tanggal->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $a->siswa->name }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$a->status" /></td>
                        <td class="px-4 py-3 text-slate-600">{{ $a->jam_masuk ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $a->jam_pulang ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada data absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $absensis->links() }}</div>
</x-app-layout>

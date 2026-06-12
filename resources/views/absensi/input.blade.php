<x-app-layout>
    <x-page-header title="Input Absensi" subtitle="Daftar hadir siswa PKL" />
    <form method="GET" action="{{ route('absensi.index') }}" class="mb-4 flex items-center gap-2">
        <input type="date" name="tanggal" value="{{ $tanggal }}" class="rounded-lg border-slate-300 text-sm">
        <button class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-medium text-white">Tampilkan</button>
    </form>
    <form method="POST" action="{{ route('absensi.store') }}" class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">@csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Jam Masuk</th><th class="px-4 py-3">Jam Pulang</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($siswas as $s)
                    @php $a = $absensis[$s->id] ?? null; @endphp
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $s->name }}</td>
                        <td class="px-4 py-3">
                            <select name="absensi[{{ $s->id }}][status]" class="rounded-lg border-slate-300 text-sm">
                                @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'] as $k => $v)
                                    <option value="{{ $k }}" {{ ($a->status ?? 'hadir') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-3"><input type="time" name="absensi[{{ $s->id }}][jam_masuk]" value="{{ $a->jam_masuk ?? '' }}" class="rounded-lg border-slate-300 text-sm"></td>
                        <td class="px-4 py-3"><input type="time" name="absensi[{{ $s->id }}][jam_pulang]" value="{{ $a->jam_pulang ?? '' }}" class="rounded-lg border-slate-300 text-sm"></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Belum ada siswa bimbingan.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($siswas->isNotEmpty())<div class="border-t border-slate-100 p-4"><button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan Absensi</button></div>@endif
    </form>
</x-app-layout>

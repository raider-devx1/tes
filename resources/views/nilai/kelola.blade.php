<x-app-layout>
    <x-page-header title="Input Penilaian" subtitle="Skala 1-5 untuk tiap komponen" />
    <div class="space-y-4">
        @forelse($siswas as $s)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 font-semibold text-slate-800">{{ $s->name }} <span class="text-xs font-normal text-slate-400">{{ $s->nis ?? '' }}</span></h3>
                <form method="POST" action="{{ route('nilai.store') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">@csrf
                    <input type="hidden" name="siswa_id" value="{{ $s->id }}">
                    @foreach(['soft_skill' => 'Soft Skill', 'hard_skill' => 'Hard Skill', 'pengembangan_hard_skill' => 'Peng. Hard Skill', 'kewirausahaan' => 'Kewirausahaan'] as $field => $label)
                        <div><label class="mb-1 block text-xs font-medium text-slate-600">{{ $label }}</label>
                            <input type="number" min="1" max="5" name="{{ $field }}" value="{{ optional($s->nilai)->$field ?? '' }}" required class="w-full rounded-lg border-slate-300 text-sm"></div>
                    @endforeach
                    <div class="flex items-end"><button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button></div>
                    <div class="sm:col-span-2 lg:col-span-5"><label class="mb-1 block text-xs font-medium text-slate-600">Catatan / Rekomendasi</label><input type="text" name="catatan_rekomendasi" value="{{ optional($s->nilai)->catatan_rekomendasi ?? '' }}" class="w-full rounded-lg border-slate-300 text-sm"></div>
                    @if($s->nilai)<p class="text-xs text-slate-400 sm:col-span-2 lg:col-span-5">Rata-rata saat ini: <span class="font-semibold text-slate-700">{{ $s->nilai->rata_rata }}</span></p>@endif
                </form>
            </div>
        @empty
            <p class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-slate-400">Belum ada siswa bimbingan.</p>
        @endforelse
    </div>
</x-app-layout>

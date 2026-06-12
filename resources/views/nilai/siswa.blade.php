<x-app-layout>
    <x-page-header title="Nilai PKL Saya" />
    @if($nilai)
        <div class="max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between"><span class="text-sm text-slate-500">Penilai: {{ optional($nilai->instruktur)->name ?? '-' }}</span><span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-bold text-indigo-700">Rata-rata: {{ $nilai->rata_rata }}</span></div>
            <dl class="divide-y divide-slate-100">
                @foreach(['soft_skill' => 'Soft Skill', 'hard_skill' => 'Hard Skill', 'pengembangan_hard_skill' => 'Pengembangan Hard Skill', 'kewirausahaan' => 'Kewirausahaan'] as $field => $label)
                    <div class="flex items-center justify-between py-2"><dt class="text-sm text-slate-600">{{ $label }}</dt><dd class="font-semibold text-slate-800">{{ $nilai->$field }} / 5</dd></div>
                @endforeach
            </dl>
            @if($nilai->catatan_rekomendasi)<p class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ $nilai->catatan_rekomendasi }}</p>@endif
            <a href="{{ route('cetak.nilai') }}" target="_blank" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">Cetak Sertifikat Nilai</a>
        </div>
    @else
        <p class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-slate-400">Nilai belum diinput oleh instruktur.</p>
    @endif
</x-app-layout>

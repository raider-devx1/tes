@props([
    'name',
    'value' => '',
    'required' => false,
    'selectClass' => 'w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-2 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30',
])

@php
    // Pemilih waktu format 24 jam (tanpa AM/PM) berbasis dropdown jam:menit.
    $val = $value !== null && $value !== '' ? substr((string) $value, 0, 5) : '';
    $jamAwal = '';
    $menitAwal = '';
    if ($val !== '' && str_contains($val, ':')) {
        [$jamAwal, $menitAwal] = explode(':', $val);
        $jamAwal = str_pad($jamAwal, 2, '0', STR_PAD_LEFT);
        $menitAwal = str_pad($menitAwal, 2, '0', STR_PAD_LEFT);
    }
@endphp

<div x-data="{ jam: '{{ $jamAwal }}', menit: '{{ $menitAwal }}' }" class="flex items-center gap-2">
    <select x-model="jam" @if($required) required @endif class="{{ $selectClass }}" aria-label="Jam">
        <option value="">Jam</option>
        @for ($h = 0; $h < 24; $h++)
            <option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}</option>
        @endfor
    </select>
    <span class="text-lg font-bold text-[#5b616e]">:</span>
    <select x-model="menit" @if($required) required @endif class="{{ $selectClass }}" aria-label="Menit">
        <option value="">Menit</option>
        @for ($m = 0; $m < 60; $m++)
            <option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>
        @endfor
    </select>
    <input type="hidden" name="{{ $name }}" :value="(jam !== '' && menit !== '') ? jam + ':' + menit : ''">
</div>

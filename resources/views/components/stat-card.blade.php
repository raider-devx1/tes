@props(['label', 'value', 'color' => 'indigo', 'icon' => 'M3 12l9-9 9 9'])
@php
    $warna = [
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'green'  => 'bg-green-50 text-green-600',
        'yellow' => 'bg-yellow-50 text-yellow-600',
        'red'    => 'bg-red-50 text-red-600',
        'blue'   => 'bg-blue-50 text-blue-600',
    ][$color] ?? 'bg-slate-50 text-slate-600';
@endphp
<div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $warna }}">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" /></svg>
    </div>
    <div>
        <p class="text-sm text-slate-500">{{ $label }}</p>
        <p class="text-2xl font-bold text-slate-800">{{ $value }}</p>
    </div>
</div>

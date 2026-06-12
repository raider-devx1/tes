@props(['status'])
@php
    $map = [
        'disetujui' => ['Disetujui', 'bg-green-100 text-green-700'],
        'revisi'    => ['Perlu Revisi', 'bg-red-100 text-red-700'],
        'pending'   => ['Menunggu', 'bg-yellow-100 text-yellow-700'],
        'hadir'     => ['Hadir', 'bg-green-100 text-green-700'],
        'izin'      => ['Izin', 'bg-blue-100 text-blue-700'],
        'sakit'     => ['Sakit', 'bg-yellow-100 text-yellow-700'],
        'alpha'     => ['Alpha', 'bg-red-100 text-red-700'],
    ];
    [$label, $kelas] = $map[$status] ?? [ucfirst($status), 'bg-slate-100 text-slate-700'];
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $kelas }}">{{ $label }}</span>

@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-6 px-4">
    <h1 class="text-xl font-semibold text-gray-800 mb-6">Daftar Hadir PKL Saya</h1>

    {{-- Kartu rekap --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $rekap['Hadir'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Hadir</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $rekap['Izin'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Izin</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $rekap['Sakit'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Sakit</p>
        </div>
        <div class="bg-white rounded-xl border p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ $rekap['Alpha'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Alpha</p>
        </div>
    </div>

    {{-- Filter bulan --}}
    <form method="GET" action="{{ route('siswa.absensi.index') }}" class="mb-4 flex items-center gap-2">
        <input type="month" name="bulan" value="{{ $bulan }}"
               class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
        <button type="submit"
                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">Filter</button>
        @if(request('bulan'))
            <a href="{{ route('siswa.absensi.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
        @endif
    </form>

    {{-- Tabel kehadiran --}}
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Jam Masuk</th>
                    <th class="px-4 py-3 text-center">Jam Pulang</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($absensis as $a)
                    @php
                        $badge = match ($a->status) {
                            'Hadir' => 'bg-green-100 text-green-700',
                            'Izin'  => 'bg-blue-100 text-blue-700',
                            'Sakit' => 'bg-yellow-100 text-yellow-700',
                            'Alpha' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs {{ $badge }}">{{ $a->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $a->jam_masuk ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">{{ $a->jam_pulang ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                            Belum ada data kehadiran.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
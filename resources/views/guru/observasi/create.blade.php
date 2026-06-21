@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Lembar Observasi</h1>
        <a href="{{ route('guru.observasi.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
            + Tambah Observasi
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Siswa</th>
                    <th class="px-4 py-3 text-left">Pekerjaan/Projek</th>
                    <th class="px-4 py-3 text-left">Permasalahan</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Cetak</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($observasi as $obs)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($obs->hari_tanggal)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $obs->user->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $obs->pekerjaan_projek ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ \Illuminate\Support\Str::limit($obs->permasalahan, 60) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($obs->is_approved)
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Disetujui</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('cetak.observasi', $obs->user_id) }}" target="_blank"
                               class="text-blue-600 hover:underline">PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                            Belum ada data observasi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
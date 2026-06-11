<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Lembar Observasi Siswa') }}
            </h2>
            <a href="{{ route('guru.observasi.create') }}" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded text-sm">
                + Tambah Observasi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
                @endif

                <h3 class="text-lg font-bold mb-4">Riwayat Observasi</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2">Tanggal</th>
                                <th class="border p-2">Nama Siswa</th>
                                <th class="border p-2">Pekerjaan/Projek</th>
                                <th class="border p-2 w-1/4">Permasalahan</th>
                                <th class="border p-2 w-1/4">Solusi</th>
                                <th class="border p-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($observasis as $obs)
                            <tr class="text-sm">
                                <td class="border p-2">{{ \Carbon\Carbon::parse($obs->hari_tanggal)->format('d M Y') }}</td>
                                <td class="border p-2 font-semibold">{{ $obs->user->name ?? '-' }}</td>
                                <td class="border p-2">{{ $obs->pekerjaan_projek ?? '-' }}</td>
                                <td class="border p-2">{{ $obs->permasalahan }}</td>
                                <td class="border p-2">{{ $obs->solusi }}</td>
                                <td class="border p-2 text-center">
                                    @if($obs->is_approved)
                                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold uppercase">Disetujui</span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold uppercase">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="border p-2 text-center">Belum ada data observasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
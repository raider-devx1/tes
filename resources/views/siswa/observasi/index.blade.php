<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lembar Observasi PKL') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-end mb-6">
                        <a href="{{ route('cetak.observasi') }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            Cetak PDF
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 border">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 border">Tanggal</th>
                                    <th class="px-4 py-3 border">Guru Pembimbing</th>
                                    <th class="px-4 py-3 border">Permasalahan</th>
                                    <th class="px-4 py-3 border">Solusi Pemecahan</th>
                                    <th class="px-4 py-3 border text-center">Status Instruktur</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($observasi as $item)
                                    <tr class="bg-white border-b">
                                        <td class="px-4 py-3 border">{{ \Carbon\Carbon::parse($item->hari_tanggal)->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 border font-medium text-gray-900">{{ $item->guru->name }}</td>
                                        <td class="px-4 py-3 border">{{ $item->permasalahan }}</td>
                                        <td class="px-4 py-3 border">{{ $item->solusi }}</td>
                                        <td class="px-4 py-3 border text-center">
                                            @if($item->is_approved)
                                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">Disetujui</span>
                                            @else
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">Menunggu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center">Belum ada observasi dari guru pembimbing.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
    <div class="mt-4">
        {!! $observasi->links() !!}
    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
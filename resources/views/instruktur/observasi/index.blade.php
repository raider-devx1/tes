<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Persetujuan Observasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if(session('success'))
                    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 border">Tanggal</th>
                                <th class="px-4 py-3 border">Siswa</th>
                                <th class="px-4 py-3 border">Permasalahan</th>
                                <th class="px-4 py-3 border">Solusi Pemecahan</th>
                                <th class="px-4 py-3 border text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($observasi as $item)
                                <tr class="bg-white border-b">
                                    <td class="px-4 py-3 border">{{ \Carbon\Carbon::parse($item->hari_tanggal)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 border font-medium text-gray-900">{{ $item->user->name }}</td>
                                    <td class="px-4 py-3 border">{{ $item->permasalahan }}</td>
                                    <td class="px-4 py-3 border">{{ $item->solusi }}</td>
                                    <td class="px-4 py-3 border text-center">
                                        @if($item->is_approved)
                                            <span class="text-green-600 font-bold">Disetujui</span>
                                        @else
                                            <form action="{{ route('instruktur.observasi.approve', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="bg-blue-600 text-white text-xs px-3 py-1.5 rounded hover:bg-blue-700">
                                                    Setujui
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center">Belum ada observasi untuk disetujui.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
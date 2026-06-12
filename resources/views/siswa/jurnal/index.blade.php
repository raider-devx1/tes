<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Jurnal Kegiatan Harian PKL') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Riwayat Jurnal Saya</h3>
                    <a href="{{ route('siswa.jurnal.create') }}" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">
                        + Tambah Jurnal
                    </a>
                    <a href="<?php echo e(route('cetak.jurnal')); ?>" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
    Cetak PDF
</a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-3">Tanggal</th>
                                <th class="border p-3">Unit Kerja</th>
                                <th class="border p-3 w-1/3">Deskripsi Pekerjaan</th>
                                <th class="border p-3">Foto</th>
                                <th class="border p-3 text-center">Status</th>
                                <th class="border p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurnals as $jurnal)
                            <tr class="hover:bg-gray-50">
                                <td class="border p-3">{{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->format('d M Y') }}</td>
                                <td class="border p-3">{{ $jurnal->unit_kerja }}</td>
                                <td class="border p-3 text-sm">{{ $jurnal->deskripsi_pekerjaan }}
                                    @if($jurnal->catatan_instruktur)
                                        <div class="mt-2 p-2 bg-yellow-50 text-xs italic border-l-2 border-yellow-400">
                                            <strong>Catatan Instruktur:</strong> {{ $jurnal->catatan_instruktur }}
                                        </div>
                                    @endif
                                </td>
                                <td class="border p-3 text-center">
                                    @if($jurnal->dokumentasi)
                                        <a href="{{ asset('storage/' . $jurnal->dokumentasi) }}" target="_blank" class="text-blue-500 underline text-sm">Lihat Foto</a>
                                    @else
                                        <span class="text-gray-400 text-sm">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="border p-3 text-center">
                                    @if($jurnal->status_persetujuan == 'pending')
                                        <span class="bg-yellow-200 text-yellow-800 py-1 px-2 rounded text-xs font-bold">Menunggu</span>
                                    @elseif($jurnal->status_persetujuan == 'disetujui')
                                        <span class="bg-green-200 text-green-800 py-1 px-2 rounded text-xs font-bold">Disetujui</span>
                                    @else
                                        <span class="bg-red-200 text-red-800 py-1 px-2 rounded text-xs font-bold">Revisi</span>
                                    @endif
                                </td>
                                <td class="border p-3 text-center">
                                    @if($jurnal->status_persetujuan == 'pending')
                                        <form action="{{ route('siswa.jurnal.destroy', $jurnal->id) }}" method="POST" onsubmit="return confirm('Hapus jurnal ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white text-xs py-1 px-2 rounded">Hapus</button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="border p-4 text-center text-gray-500">Belum ada jurnal yang diisi.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Validasi Jurnal Kegiatan Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-3">Nama Siswa</th>
                                <th class="border p-3">Tanggal & Unit Kerja</th>
                                <th class="border p-3 w-1/3">Deskripsi Pekerjaan</th>
                                <th class="border p-3">Foto</th>
                                <th class="border p-3 text-center">Tindakan Persetujuan</th>
                                <th class="border p-3 text-center">Cetak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurnals as $jurnal)
                            <tr class="hover:bg-gray-50 {{ $jurnal->status_persetujuan == 'pending' ? 'bg-yellow-50' : '' }}">
                                <td class="border p-3 font-bold">{{ $jurnal->siswa->name }}</td>
                                <td class="border p-3 text-sm">
                                    {{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->format('d M Y') }}<br>
                                    <span class="text-gray-500">{{ $jurnal->unit_kerja }}</span>
                                </td>
                                <td class="border p-3 text-sm">{{ $jurnal->deskripsi_pekerjaan }}</td>
                                <td class="border p-3 text-center">
                                    @if($jurnal->dokumentasi)
                                        <a href="{{ asset('storage/' . $jurnal->dokumentasi) }}" target="_blank" class="text-blue-500 underline text-sm">Lihat</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="border p-3">
                                    <form action="{{ route('instruktur.jurnal.update', $jurnal->id) }}" method="POST" class="flex flex-col gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="status_persetujuan" class="border-gray-300 rounded text-sm w-full">
                                            <option value="pending" {{ $jurnal->status_persetujuan == 'pending' ? 'selected' : '' }}>Menunggu</option>
                                            <option value="disetujui" {{ $jurnal->status_persetujuan == 'disetujui' ? 'selected' : '' }}>Setujui</option>
                                            <option value="revisi" {{ $jurnal->status_persetujuan == 'revisi' ? 'selected' : '' }}>Revisi</option>
                                        </select>
                                        <textarea name="catatan_instruktur" rows="2" placeholder="Catatan/Feedback..." class="border-gray-300 rounded text-sm w-full">{{ $jurnal->catatan_instruktur }}</textarea>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-1 px-2 rounded text-sm">Simpan</button>
                                    </form>
                                </td>
                                <td class="border p-3 text-center">
    <a href="<?php echo e(route('cetak.jurnal', $jurnal->siswa_id)); ?>" target="_blank" class="bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-2 rounded">PDF</a>
</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="border p-4 text-center text-gray-500">Belum ada jurnal dari siswa bimbingan Anda.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
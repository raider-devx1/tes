<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Penilaian Akhir PKL</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if(session('success')) 
                    <div class="bg-green-100 text-green-700 p-3 mb-4 rounded font-medium">
                        {{ session('success') }}
                    </div> 
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2">Siswa</th>
                                <th class="border p-2 text-center" colspan="4">Kriteria Nilai (1 - 5)</th>
                                <th class="border p-2">Catatan Instruktur</th>
                                <th class="border p-2 text-center">Aksi</th>
                            </tr>
                            <tr class="bg-gray-50 text-xs">
                                <th class="border p-2"></th>
                                <th class="border p-2 text-center">Soft Skills</th>
                                <th class="border p-2 text-center">Hard Skills</th>
                                <th class="border p-2 text-center">Pengembangan</th>
                                <th class="border p-2 text-center">Kewirausahaan</th>
                                <th class="border p-2"></th>
                                <th class="border p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswa as $item)
                                @php 
                                    // Mengambil relasi data nilai siswa jika sudah ada
                                    $n = $item->nilai; 
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="border p-2 font-bold text-gray-800">{{ $item->name }}</td>
                                    
                                    <td class="border p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="soft_skill" min="1" max="5" value="{{ $n->soft_skill ?? '' }}" class="w-16 border-gray-300 rounded text-center" required>
                                    </td>
                                    <td class="border p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="hard_skill" min="1" max="5" value="{{ $n->hard_skill ?? '' }}" class="w-16 border-gray-300 rounded text-center" required>
                                    </td>
                                    <td class="border p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="pengembangan_hard_skill" min="1" max="5" value="{{ $n->pengembangan_hard_skill ?? '' }}" class="w-16 border-gray-300 rounded text-center" required>
                                    </td>
                                    <td class="border p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="kewirausahaan" min="1" max="5" value="{{ $n->kewirausahaan ?? '' }}" class="w-16 border-gray-300 rounded text-center" required>
                                    </td>
                                    <td class="border p-2">
                                        <textarea form="form-nilai-{{ $item->id }}" name="catatan_rekomendasi" rows="1" placeholder="Opsional..." class="w-full border-gray-300 rounded text-sm">{{ $n->catatan_rekomendasi ?? '' }}</textarea>
                                    </td>
                                    
                                    <td class="border p-2 text-center">
                                        <form id="form-nilai-{{ $item->id }}" action="{{ route('instruktur.nilai.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $item->id }}">
                                            <button type="submit" class="bg-blue-600 text-white font-semibold py-1.5 px-4 rounded text-sm hover:bg-blue-800 transition">
                                                Simpan
                                            </button>
                                        </form>
                                    </td>
                                    <a href="<?php echo e(route('cetak.nilai', $item->id)); ?>" target="_blank" class="inline-block mt-2 bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded">Cetak PDF</a>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="border p-6 text-center text-gray-500">Tidak ada data siswa PKL yang Anda bimbing saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
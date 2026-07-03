<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Jurnal Harian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('siswa.jurnal.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Hari / Tanggal Kegiatan</label>
                        <input type="date" name="hari_tanggal" value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded shadow-sm" required>
                        @error('hari_tanggal') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Unit Kerja / Pekerjaan</label>
                        <input type="text" name="unit_kerja" placeholder="Contoh: Divisi Jaringan / Pemasangan Kabel LAN" class="w-full border-gray-300 rounded shadow-sm" required>
                        @error('unit_kerja') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                   

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Dokumentasi (Foto) <span class="text-gray-400 font-normal text-sm">- Opsional</span></label>
                        <input type="file" name="dokumentasi" accept="image/*" class="w-full border-gray-300 rounded shadow-sm p-1 border">
                        @error('dokumentasi') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end mt-6">
                        <a href="{{ route('siswa.jurnal.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Batal</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded">Simpan Jurnal</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
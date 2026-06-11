<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Catatan Kegiatan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form action="{{ route('siswa.catatan.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nama Pekerjaan</label>
                        <input type="text" name="nama_pekerjaan" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Perencanaan Kegiatan</label>
                        <p class="text-xs text-gray-500 mb-1">*Jadwal kegiatan / dokumen perencanaan</p>
                        <textarea name="perencanaan_kegiatan" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Pelaksanaan Kegiatan / Hasil</label>
                        <p class="text-xs text-gray-500 mb-1">*Uraian proses kerja dan hasil</p>
                        <textarea name="pelaksanaan_kegiatan" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end mt-6">
                        <a href="{{ route('siswa.catatan.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md mr-2 hover:bg-gray-600">Batal</a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Simpan Catatan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
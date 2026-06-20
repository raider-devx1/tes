<x-app-layout title="Tambah Siswa PKL">
    <div class="max-w-3xl">
        <h2 class="text-xl font-bold text-gray-800 mb-1">Tambah Siswa PKL</h2>
        <p class="text-sm text-gray-500 mb-6">Daftarkan peserta PKL baru beserta pemetaannya.</p>

        <form method="POST" action="{{ route('admin.siswa.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-2xl shadow-sm border border-blue-100 p-6">
            @csrf
            @include('admin.siswa._form')

            <div class="flex items-center gap-3 mt-6">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Simpan</button>
                <a href="{{ route('admin.siswa.index') }}" class="px-5 py-2.5 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
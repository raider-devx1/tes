<x-app-layout title="Tambah Instruktur Industri">
    <div class="max-w-2xl">
        <h2 class="text-xl font-bold text-gray-800 mb-1">Tambah Instruktur Industri</h2>
        <p class="text-sm text-gray-500 mb-6">Buat akun instruktur dari pihak industri.</p>

        <form method="POST" action="{{ route('admin.instruktur.store') }}"
              class="bg-white rounded-2xl shadow-sm border border-blue-100 p-6">
            @csrf
            @include('admin.instruktur._form')

            <div class="flex items-center gap-3 mt-6">
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Simpan</button>
                <a href="{{ route('admin.instruktur.index') }}" class="px-5 py-2.5 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Informasi PKL</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form action=" route('admin.informasi.store') " method="POST" class="space-y-4">
                        @csrf
                        @include('admin.informasi._form', ['informasi' => null])

                        <div class="flex items-center gap-3">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Simpan
                            </button>
                            <a href=" route('admin.informasi.index') " class="text-gray-600 hover:underline">Batal</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
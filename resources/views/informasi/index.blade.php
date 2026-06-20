<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Informasi & Panduan PKL
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @forelse ($informasiGroup as $kategori => $items)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-bold text-blue-700 mb-4 border-b pb-2">
                             $kategoriLabels[$kategori] ?? ucfirst($kategori) 
                        </h3>

                        <div class="space-y-4">
                            @foreach ($items as $info)
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <h4 class="font-semibold text-gray-800"> $info->judul </h4>
                                    <p class="text-gray-600 text-sm mt-1 whitespace-pre-line"> $info->konten </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center text-gray-500">
                        Belum ada informasi yang tersedia.
                    </div>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
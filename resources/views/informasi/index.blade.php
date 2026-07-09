<x-app-layout title="Informasi & Panduan PKL">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Informasi & Panduan PKL
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    @forelse ($informasi as $info)
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-semibold text-gray-800 text-base">
                                {{ $info->judul }}
                            </h4>
                            <div class="konten-html text-gray-600 text-sm mt-2 leading-relaxed">
                                {!! $info->konten !!}
                            </div>

                            @if(!empty($info->file))
                                <div class="mt-3">
                                    <a href="{{ asset('storage/' . $info->file) }}" download
                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">
                                        Unduh Lampiran
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">
                            Belum ada informasi yang tersedia.
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .konten-html h1 { font-size: 1.5rem; font-weight: 700; margin: .75rem 0 .5rem; color: #1f2937; }
        .konten-html h2 { font-size: 1.25rem; font-weight: 700; margin: .75rem 0 .5rem; color: #1f2937; }
        .konten-html h3 { font-size: 1.1rem; font-weight: 600; margin: .5rem 0; color: #1f2937; }
        .konten-html p { margin: .5rem 0; }
        .konten-html w { margin: .5rem 0; }
        .konten-html ul { list-style: disc; padding-left: 1.5rem; margin: .5rem 0; }
        .konten-html ol { list-style: decimal; padding-left: 1.5rem; margin: .5rem 0; }
        .konten-html li { margin: .25rem 0; }
        .konten-html a { color: #2563EB; text-decoration: underline; }
        .konten-html strong { font-weight: 700; }
        .konten-html em { font-style: italic; }
        .konten-html u { text-decoration: underline; }
        .konten-html blockquote { border-left: 3px solid #93C5FD; padding-left: 1rem; color: #6b7280; font-style: italic; margin: .5rem 0; }
    </style>
    @endpush
</x-app-layout>
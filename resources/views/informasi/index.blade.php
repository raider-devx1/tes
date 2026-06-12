<x-app-layout>
    <x-page-header title="Panduan PKL" subtitle="Informasi umum pelaksanaan PKL" />
    @forelse($informasis as $kategori => $items)
        <div class="mb-6">
            <h2 class="mb-3 text-lg font-semibold capitalize text-slate-700">{{ $kategori }}</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($items as $info)
                    <a href="{{ route('informasi.show', $info) }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 hover:shadow">
                        <p class="font-medium text-slate-800">{{ $info->judul }}</p>
                        <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ Str::limit(strip_tags($info->konten), 120) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <p class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-slate-400">Belum ada informasi/panduan.</p>
    @endforelse
</x-app-layout>

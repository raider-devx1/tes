<x-app-layout>
    <x-page-header :title="$informasi->judul" :subtitle="ucfirst($informasi->kategori)" />
    <div class="prose max-w-3xl rounded-xl border border-slate-200 bg-white p-6 text-slate-700 shadow-sm">
        {!! nl2br(e($informasi->konten)) !!}
    </div>
    <a href="{{ route('informasi.index') }}" class="mt-4 inline-block text-sm text-indigo-600 hover:underline">&larr; Kembali ke daftar panduan</a>
</x-app-layout>

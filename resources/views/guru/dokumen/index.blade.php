<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dokumen Siswa Bimbingan</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-gray-500">Lihat & unduh dokumen siswa bimbingan Anda sesuai hak akses.</p>

            <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Nama / NISN"
                           class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
                <a href="{{ route('guru.dokumen.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            </form>

            @forelse($siswa as $s)
                <div class="bg-white rounded-xl border border-blue-100 p-5">
                    <div class="mb-3">
                        <h3 class="font-bold text-gray-800">{{ $s->name }}</h3>
                        <p class="text-xs text-gray-400">Kelas: {{ $s->kelas ?? '-' }} · NISN: {{ $s->nisn ?? '-' }}</p>
                    </div>
                    @include('partials.dokumen-aksi', ['siswa' => $s])
                </div>
            @empty
                <div class="bg-white rounded-xl border border-blue-100 p-8 text-center text-gray-400">
                    Belum ada siswa bimbingan.
                </div>
            @endforelse

            <div>{!! $siswa->links() !!}</div>
        </div>
    </div>
</x-app-layout>
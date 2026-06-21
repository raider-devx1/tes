<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dokumen Siswa Bimbingan</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-gray-500">Lihat & unduh dokumen siswa bimbingan Anda sesuai hak akses.</p>

            @php
                $suratTugas   = \App\Models\Pengaturan::ambil('surat_tugas');
                $aturanST     = \App\Models\Dokumen::ATURAN['surat_tugas'];
                $bolehLihatST = in_array(auth()->user()->role, $aturanST['lihat'], true);
                $bolehUnduhST = in_array(auth()->user()->role, $aturanST['download'], true);
            @endphp
            <div class="bg-white rounded-xl border border-blue-100 p-5">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">📄 Surat Tugas PKL</h3>
                        <p class="text-xs text-gray-500 mt-1">Berkas resmi dari Admin — berlaku sebagai acuan untuk <strong>semua</strong> siswa bimbingan.</p>
                        @if($suratTugas)
                            <span class="inline-block mt-2 text-xs text-green-600">● Tersedia</span>
                        @else
                            <span class="inline-block mt-2 text-xs text-gray-400">○ Belum diunggah Admin</span>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        @if($suratTugas && $bolehLihatST)
                            <a href="{{ route('dokumen.surat-tugas.lihat') }}" target="_blank"
                               class="px-3 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200">Lihat</a>
                        @endif
                        @if($suratTugas && $bolehUnduhST)
                            <a href="{{ route('dokumen.surat-tugas.download') }}"
                               class="px-3 py-2 rounded-lg bg-[#2563EB] text-white text-sm hover:bg-blue-700">Download</a>
                        @endif
                        @if(!$suratTugas)
                            <span class="text-xs text-gray-400 italic self-center">Menunggu unggahan Admin</span>
                        @endif
                    </div>
                </div>
            </div>

            <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Nama / NISN"
                           class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
                <a href="{{ route('guru.dokumen.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            </form>

            <div>
                <h3 class="text-sm font-semibold text-gray-600 mb-3">Dokumen per Siswa</h3>

                <div class="space-y-6">
                    @forelse($siswa as $s)
                        <div class="bg-white rounded-xl border border-blue-100 p-5">
                            <div class="mb-3">
                                <h3 class="font-bold text-gray-800">{{ $s->name }}</h3>
                                <p class="text-xs text-gray-400">Kelas: {{ $s->kelas ?? '-' }} · NISN: {{ $s->nisn ?? '-' }}</p>
                            </div>
                            @include('partials.dokumen-aksi', ['siswa' => $s, 'exclude' => ['surat_tugas']])
                        </div>
                    @empty
                        <div class="bg-white rounded-xl border border-blue-100 p-8 text-center text-gray-400">
                            Belum ada siswa bimbingan.
                        </div>
                    @endforelse
                </div>
            </div>

            <div>{!! $siswa->links() !!}</div>
        </div>
    </div>
</x-app-layout>
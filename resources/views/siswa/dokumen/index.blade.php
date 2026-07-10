<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Dokumen PKL Saya</h2>
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                 Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-black">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- ===== BOX SURAT TUGAS ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 sm:p-6 shadow-sm">
                <h3 class="text-lg font-bold text-black mb-1">Surat Tugas PKL</h3>
                <p class="text-sm font-medium text-[#5b616e] mb-4">Diunggah oleh Admin (berlaku untuk semua siswa). Unduh untuk dicetak &amp; dibawa ke industri.</p>
                @if($suratTugas)
                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('siswa.dokumen.suratTugas.lihat') }}" target="_blank"
                           class="inline-flex items-center justify-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                            Lihat
                        </a>
                        <a href="{{ route('siswa.dokumen.suratTugas.download') }}"
                           class="inline-flex items-center justify-center rounded-xl bg-[#0047d6] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                            Download PDF
                        </a>
                    </div>
                @else
                    <p class="text-sm font-medium italic text-[#5b616e]">Surat Tugas belum diunggah oleh Admin.</p>
                @endif
            </div>

            {{-- ===== BOX UPLOAD DOKUMEN SISWA ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 sm:p-6 shadow-sm">
                <h3 class="text-lg font-bold text-black mb-4">Upload Dokumen PKL</h3>
                <form action="{{ route('siswa.dokumen.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                   <div>
                        <label class="block font-semibold mb-1">Surat Penerimaan Industri (PDF, maks 2MB)</label>
                        <p class="text-xs text-gray-500 mb-2">Scan/foto surat balasan penerimaan dari industri.</p>
                        <input type="file" name="surat_penerimaan" accept=".pdf"
                               class="border border-gray-200 p-2 rounded-lg w-full text-sm">
                        @if($dokumen && $dokumen->surat_penerimaan)
                            <a href="{{ route('dokumen.lihat', [auth()->id(), 'surat_penerimaan']) }}" target="_blank"
                               class="mt-2 inline-block text-sm font-bold text-[#0047d6] underline">Lihat file tersimpan</a>
                        @endif
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Laporan PKL Final (PDF, maks 5MB)</label>
                        <p class="text-xs text-gray-500 mb-2">Laporan akhir yang sudah selesai disusun.</p>
                        <input type="file" name="laporan_akhir" accept=".pdf"
                               class="border border-gray-200 p-2 rounded-lg w-full text-sm">
                        @if($dokumen && $dokumen->laporan_akhir)
                            <a href="{{ route('dokumen.lihat', [auth()->id(), 'laporan_akhir']) }}" target="_blank"
                               class="mt-2 inline-block text-sm font-bold text-[#0047d6] underline">Lihat file tersimpan</a>
                        @endif
                    </div>

                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-[#0047d6] px-6 py-3 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        Simpan Dokumen
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
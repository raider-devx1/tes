<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
             <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dokumen PKL Saya</h2>
           
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                &larr; Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Surat Tugas: diunggah Admin, siswa hanya unduh --}}
           <div class="bg-white shadow-sm sm:rounded-lg p-6">
    <h3 class="text-lg font-bold mb-1">Surat Tugas PKL</h3>
    <p class="text-sm text-gray-500 mb-3">Diunggah oleh Admin (berlaku untuk semua siswa). Unduh untuk dicetak & dibawa ke industri.</p>
    @if($suratTugas)
        <div class="flex gap-2">
            <a href="{{ route('dokumen.surat-tugas.lihat') }}" target="_blank"
               class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200">Lihat</a>
            <a href="{{ route('dokumen.surat-tugas.download') }}"
               class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm hover:bg-blue-700">Download PDF</a>
        </div>
    @else
        <p class="text-sm italic text-gray-400">Surat Tugas belum diunggah oleh Admin.</p>
    @endif
</div>

            {{-- Upload Surat Penerimaan + Laporan --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Upload Dokumen PKL</h3>
                <form action="{{ route('siswa.dokumen.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block font-semibold mb-1">Surat Penerimaan Industri (PDF, maks 2MB)</label>
                        <p class="text-xs text-gray-500 mb-2">Scan/foto surat balasan penerimaan dari industri.</p>
                        <input type="file" name="surat_penerimaan" accept=".pdf"
                               class="border border-gray-200 p-2 rounded-lg w-full text-sm">
                        @if($dokumen && $dokumen->surat_penerimaan)
                            <a href="{{ route('dokumen.lihat', [auth()->id(), 'surat_penerimaan']) }}" target="_blank"
                               class="text-[#2563EB] text-sm underline mt-1 inline-block">Lihat file tersimpan</a>
                        @endif
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Laporan PKL Final (PDF, maks 5MB)</label>
                        <p class="text-xs text-gray-500 mb-2">Laporan akhir yang sudah selesai disusun.</p>
                        <input type="file" name="laporan_akhir" accept=".pdf"
                               class="border border-gray-200 p-2 rounded-lg w-full text-sm">
                        @if($dokumen && $dokumen->laporan_akhir)
                            <a href="{{ route('dokumen.lihat', [auth()->id(), 'laporan_akhir']) }}" target="_blank"
                               class="text-[#2563EB] text-sm underline mt-1 inline-block">Lihat file tersimpan</a>
                        @endif
                    </div>

                    <button type="submit" class="bg-[#2563EB] text-white py-2 px-5 rounded-lg hover:bg-blue-700">Simpan Dokumen</button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
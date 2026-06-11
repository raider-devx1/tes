<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">Akhir PKL (Laporan & Penilaian)</h2></x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">Nilai PKL Anda</h3>
            @if($nilai)
                <div class="grid grid-cols-4 gap-4 mb-4 text-center">
                    <div class="bg-green-100 p-4 rounded"><p class="text-sm">Soft Skills</p><h4 class="text-2xl font-bold">{{ $nilai->soft_skills }} / 5</h4></div>
                    <div class="bg-blue-100 p-4 rounded"><p class="text-sm">Hard Skills</p><h4 class="text-2xl font-bold">{{ $nilai->hard_skills }} / 5</h4></div>
                    <div class="bg-yellow-100 p-4 rounded"><p class="text-sm">Pengembangan</p><h4 class="text-2xl font-bold">{{ $nilai->pengembangan }} / 5</h4></div>
                    <div class="bg-purple-100 p-4 rounded"><p class="text-sm">Kewirausahaan</p><h4 class="text-2xl font-bold">{{ $nilai->kewirausahaan }} / 5</h4></div>
                </div>
                <a href="{{ route('cetak.nilai', Auth::id()) }}" target="_blank" class="bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-800">Cetak Lembar Nilai (PDF)</a>
            @else
                <p class="text-gray-500 italic">Instruktur belum memasukkan nilai PKL Anda.</p>
            @endif
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-bold mb-4">Upload Dokumen PKL</h3>
            @if(session('success')) <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">{{ session('success') }}</div> @endif
            <form action="{{ route('siswa.dokumen.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-bold">Laporan Akhir PKL (PDF, Max 5MB)</label>
                    <input type="file" name="laporan_akhir" accept=".pdf" class="border p-2 rounded w-full">
                    @if($dokumen && $dokumen->laporan_akhir) <a href="{{ asset('storage/'.$dokumen->laporan_akhir) }}" class="text-blue-500 text-sm underline" target="_blank">Lihat Laporan Tersimpan</a> @endif
                </div>
                <div>
                    <label class="block font-bold">Surat Tugas / Pengantar (PDF)</label>
                    <input type="file" name="surat_tugas" accept=".pdf" class="border p-2 rounded w-full">
                    @if($dokumen && $dokumen->surat_tugas) <a href="{{ asset('storage/'.$dokumen->surat_tugas) }}" class="text-blue-500 text-sm underline" target="_blank">Lihat Surat Tersimpan</a> @endif
                </div>
                <button type="submit" class="bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-800">Simpan Dokumen</button>
            </form>
        </div>

    </div></div>
</x-app-layout>
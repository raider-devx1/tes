<x-app-layout title="Upload Surat Tugas">
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Surat Tugas PKL</h2>
            <p class="text-sm text-gray-500">
                Unggah <strong>satu</strong> Surat Tugas resmi yang berlaku untuk <strong>semua siswa</strong>.
                Siswa & Guru akan melihat/mengunduh berkas yang sama.
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 text-green-700 border border-green-200 p-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 text-red-700 border border-red-200 p-3 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-blue-100 p-6 space-y-5">

            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <p class="text-sm font-medium text-gray-700">Status berkas saat ini</p>
                    @if($suratTugas)
                        <p class="text-sm text-green-600 mt-0.5">● Surat Tugas sudah diunggah</p>
                    @else
                        <p class="text-sm text-red-500 mt-0.5">○ Belum ada Surat Tugas</p>
                    @endif
                </div>
                @if($suratTugas)
                    <div class="flex gap-2">
                        <a href="{{ route('dokumen.surat-tugas.lihat') }}" target="_blank"
                           class="px-3 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm hover:bg-gray-200">Lihat</a>
                        <a href="{{ route('dokumen.surat-tugas.download') }}"
                           class="px-3 py-2 rounded-lg bg-[#2563EB] text-white text-sm hover:bg-blue-700">Download</a>
                    </div>
                @endif
            </div>

            <form action="{{ route('admin.dokumen.surat-tugas') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        {{ $suratTugas ? 'Ganti Surat Tugas' : 'Unggah Surat Tugas' }} (PDF, maks 2MB)
                    </label>
                    <input type="file" name="surat_tugas" accept=".pdf" required
                           class="border border-gray-200 rounded-lg p-2 w-full text-sm">
                    @if($suratTugas)
                        <p class="text-xs text-amber-600 mt-1">Mengunggah berkas baru akan menggantikan Surat Tugas yang lama.</p>
                    @endif
                </div>
                <button type="submit"
                        class="px-5 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                    {{ $suratTugas ? 'Ganti Berkas' : 'Simpan' }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
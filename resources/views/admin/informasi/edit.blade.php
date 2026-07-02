<x-app-layout title="Edit Informasi">
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Informasi PKL</h2>
            <p class="text-sm text-gray-500">Perbarui pengumuman atau panduan.</p>
        </div>

        <form method="POST" action="{{ route('admin.informasi.update', $informasi) }}"
              enctype="multipart/form-data"
              class="bg-white rounded-xl border border-blue-100 p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" name="judul" value="{{ old('judul', $informasi->judul) }}" required
                       class="w-full rounded-lg border-gray-200 focus:border-[#2563EB] focus:ring-[#2563EB]">
                @error('judul') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                <input type="number" name="urutan" value="{{ old('urutan', $informasi->urutan) }}" min="0"
                       class="w-full sm:w-40 rounded-lg border-gray-200 focus:border-[#2563EB] focus:ring-[#2563EB]">
                <p class="text-xs text-gray-400 mt-1">Angka lebih kecil tampil lebih dulu.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konten</label>
                <div id="editor" class="bg-white rounded-lg"></div>
                <textarea name="konten" id="konten" class="hidden">{{ old('konten', $informasi->konten) }}</textarea>
                @error('konten') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran File (opsional)</label>
                @if(!empty($informasi->file))
                    <p class="text-xs text-gray-500 mb-1">
                        File saat ini:
                        <a href="{{ asset('storage/' . $informasi->file) }}" target="_blank" class="text-[#2563EB] hover:underline">Lihat lampiran</a>
                    </p>
                @endif
                <input type="file" name="file"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-[#2563EB] file:font-medium hover:file:bg-blue-100">
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah file. Mengunggah file baru akan mengganti yang lama.</p>
                @error('file') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('admin.informasi.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Perbarui</button>
            </div>
        </form>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <style>
        #editor .ql-editor { min-height: 240px; cursor: text; }
    </style>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hidden = document.getElementById('konten');
            const quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link', 'blockquote'],
                        ['clean'],
                    ],
                },
            });
            if (hidden.value.trim()) {
                quill.root.innerHTML = hidden.value;
            }
            hidden.closest('form').addEventListener('submit', function () {
                hidden.value = quill.getText().trim().length ? quill.root.innerHTML : '';
            });
        });
    </script>
    @endpush
</x-app-layout>
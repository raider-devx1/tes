<x-app-layout title="Tambah Informasi">
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Tambah Informasi PKL</h2>
            <p class="text-sm text-gray-500">Buat pengumuman atau panduan baru.</p>
        </div>

        <form method="POST" action="{{ route('admin.informasi.store') }}" class="bg-white rounded-xl border border-blue-100 p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" name="judul" value="{{ old('judul') }}" required
                       class="w-full rounded-lg border-gray-200 focus:border-[#2563EB] focus:ring-[#2563EB]">
                @error('judul') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="kategori" required class="w-full rounded-lg border-gray-200 focus:border-[#2563EB] focus:ring-[#2563EB]">
                        @foreach($kategoriLabels as $key => $label)
                            <option value="{{ $key }}" {{ old('kategori') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                    <input type="number" name="urutan" value="{{ old('urutan', 0) }}" min="0"
                           class="w-full rounded-lg border-gray-200 focus:border-[#2563EB] focus:ring-[#2563EB]">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konten</label>
                <div id="editor" class="bg-white rounded-lg" style="min-height: 240px;"></div>
                <textarea name="konten" id="konten" class="hidden">{{ old('konten') }}</textarea>
                @error('konten') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('admin.informasi.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
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
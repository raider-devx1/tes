<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Tambah Lembar Observasi</h1>
            <a href="{{ route('guru.observasi.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('guru.observasi.store') }}" method="POST"
              class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Siswa</label>
                <select name="user_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Siswa Bimbingan --</option>
                    @foreach ($siswas as $s)
                        <option value="{{ $s->id }}" @selected(old('user_id') == $s->id)>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hari / Tanggal</label>
                <input type="date" name="hari_tanggal" value="{{ old('hari_tanggal', date('Y-m-d')) }}" required
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan / Projek</label>
                <input type="text" name="pekerjaan_projek" value="{{ old('pekerjaan_projek') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Contoh: Maintenance jaringan kantor">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Permasalahan</label>
                <textarea name="permasalahan" rows="3" required
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('permasalahan') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Solusi</label>
                <textarea name="solusi" rows="3" required
                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">{{ old('solusi') }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    Simpan Observasi
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
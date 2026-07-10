<x-app-layout>
    <div class="max-w-3xl mx-auto py-6 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Tambah Lembar Observasi</h1>
            <a href="{{ route('guru.observasi.index') }}" class="text-sm text-gray-500 hover:text-gray-700"> Kembali</a>
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
              class="bg-white rounded-xl shadow-sm border p-6 space-y-4"
              x-data="{
                  items: {{ \Illuminate\Support\Js::from(old('items', [['permasalahan' => '', 'solusi' => '']])) }},
                  addItem() { this.items.push({ permasalahan: '', solusi: '' }) },
                  removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1) },
              }">
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
                <input type="date" name="hari_tanggal" value="{{ old('hari_tanggal') }}" required
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan / Projek</label>
                <input type="text" name="pekerjaan_projek" value="{{ old('pekerjaan_projek') }}"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Contoh: Maintenance jaringan kantor">
            </div>

            {{-- ===== DAFTAR POIN PERMASALAHAN & SOLUSI (DINAMIS) ===== --}}
            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-700">Permasalahan &amp; Solusi</label>

                {{-- List Card Item --}}
                <div class="space-y-4">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="rounded-lg border border-gray-200 p-4 bg-gray-50/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold text-gray-600">
                                    Poin <span x-text="index + 1"></span>
                                </span>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                        class="text-xs font-medium text-red-500 hover:text-red-700">
                                    Hapus
                                </button>
                            </div>

                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Permasalahan</label>
                                <textarea :name="`items[${index}][permasalahan]`" rows="3" required
                                          x-model="item.permasalahan"
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Solusi</label>
                                <textarea :name="`items[${index}][solusi]`" rows="3" required
                                          x-model="item.solusi"
                                          class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-white"></textarea>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Tombol Tambah Poin diletakkan di bawah setelah daftar Card selesai di-render --}}
                <div class="flex justify-start pt-1">
                    <button type="button" @click="addItem()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg shadow-sm transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Poin
                    </button>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                    Simpan Observasi
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
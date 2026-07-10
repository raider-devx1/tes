@php
    $oldItems = old('items')
        ? collect(old('items'))->map(fn ($it) => ['unit_kerja' => $it['unit_kerja'] ?? ''])->values()->all()
        : [['unit_kerja' => '']];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight text-[#0a0b0d]">Tambah Jurnal Kegiatan Harian</h2>
            <a href="{{ route('siswa.jurnal.index') }}"
               class="inline-flex items-center gap-1 rounded-full bg-[#eef0f3] px-4 py-2 text-sm font-semibold text-[#0a0b0d] transition hover:bg-[#dee1e6]">
                 Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-[#cf202f]/30 bg-[#cf202f]/10 px-4 py-3 text-sm text-[#cf202f]">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('siswa.jurnal.store') }}" method="POST" enctype="multipart/form-data"
                  class="rounded-3xl border border-[#dee1e6] bg-white p-6 md:p-8 space-y-5"
                  x-data="{
                      items: {{ \Illuminate\Support\Js::from($oldItems) }},
                      addItem() { this.items.push({ unit_kerja: '' }) },
                      removeItem(i) { if (this.items.length > 1) this.items.splice(i, 1) },
                  }">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hari / Tanggal</label>
                    <input type="date" name="hari_tanggal" value="{{ old('hari_tanggal', date('Y-m-d')) }}" required
                           class="w-full rounded-xl border-[#dee1e6] focus:border-[#0052ff] focus:ring-[#0052ff]">
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700">Unit Kerja / Pekerjaan</label>
                        <span class="text-xs text-[#7c828a]">Bisa lebih dari 1 pekerjaan di tanggal yang sama</span>
                    </div>

                    <template x-for="(item, index) in items" :key="index">
                        <div class="rounded-2xl border border-gray-200 p-4 bg-gray-50/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold text-gray-600">
                                    Pekerjaan <span x-text="index + 1"></span>
                                </span>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                        class="text-xs font-medium text-red-500 hover:text-red-700">Hapus</button>
                            </div>

                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Kerja / Pekerjaan</label>
                                <textarea :name="`items[${index}][unit_kerja]`" rows="3" required
                                          x-model="item.unit_kerja"
                                          class="w-full rounded-xl border-gray-300 focus:border-[#0052ff] focus:ring-[#0052ff] bg-white"
                                          placeholder="Contoh: Instalasi jaringan ruang server"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Dokumentasi (opsional)</label>
                                <input type="file" :name="`items[${index}][dokumentasi]`" accept="image/*"
                                       class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef0f3] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-[#0a0b0d]">
                            </div>
                        </div>
                    </template>

                    <div>
                        <button type="button" @click="addItem()"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#05b169] hover:bg-[#049a5b] text-white text-xs font-semibold rounded-full shadow-sm transition-colors">
                             Tambah Pekerjaan
                        </button>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center rounded-full bg-[#0052ff] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#003ecc]">
                        Simpan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
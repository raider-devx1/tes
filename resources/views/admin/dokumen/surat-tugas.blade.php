<x-app-layout title="Upload Surat Tugas">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-6 sm:py-8 md:py-10 bg-white" x-data="suratTugasCrud()">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10 space-y-6">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Surat Tugas PKL</h2>
                    <p class="text-sm text-gray-500">
                        Unggah <strong>satu</strong> Surat Tugas resmi yang berlaku untuk <strong>semua siswa</strong>.
                        Siswa &amp; Guru akan melihat/mengunduh berkas yang sama.
                    </p>
                </div>
                <button type="button" onclick="history.back()"
                        class="inline-flex items-center gap-1 rounded-xl border border-[#2563EB]/25 bg-white px-4 py-2 text-sm font-semibold text-[#2563EB] transition hover:bg-[#2563EB]/5 shrink-0 self-start">Kembali</button>
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

            {{-- REKAP RINGKAS --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                <div class="rounded-xl border border-blue-100 bg-white p-4 sm:p-5">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-gray-500">Status Berkas</p>
                    @if($suratTugas)
                        <p class="mt-1 text-lg sm:text-xl font-bold text-green-600">Sudah Diunggah</p>
                    @else
                        <p class="mt-1 text-lg sm:text-xl font-bold text-red-500">Belum Ada</p>
                    @endif
                </div>
                <div class="rounded-xl border border-blue-100 bg-white p-4 sm:p-5">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-gray-500">Berlaku Untuk</p>
                    <p class="mt-1 text-lg sm:text-xl font-bold text-gray-800">Semua Siswa</p>
                </div>
                <div class="rounded-xl border border-blue-100 bg-white p-4 sm:p-5 col-span-2 sm:col-span-1">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-gray-500">Format</p>
                    <p class="mt-1 text-lg sm:text-xl font-bold text-[#2563EB]">PDF &middot; maks 2MB</p>
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- TABEL DESKTOP / LAPTOP (>= lg): tampilkan SEMUA informasi   --}}
            {{-- ============================================================= --}}
            <div class="hidden lg:block bg-white rounded-xl border border-blue-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-blue-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3">Dokumen</th>
                                <th class="px-4 py-3">Berlaku Untuk</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Berkas</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-blue-50/40 transition">
                                <td class="px-4 py-3 text-center text-gray-500">1</td>
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    Surat Tugas PKL
                                    <div class="text-xs text-gray-400">Berkas tunggal &middot; global</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">Semua Siswa &amp; Guru</td>
                                <td class="px-4 py-3 text-center">
                                    @if($suratTugas)
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">Sudah Diunggah</span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600">Belum Ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($suratTugas)
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            <a href="{{ route('dokumen.surat-tugas.lihat') }}" target="_blank"
                                               class="px-2.5 py-1 rounded-md bg-blue-50 text-[#2563EB] text-xs font-medium hover:bg-blue-100 transition">Lihat PDF</a>
                                            <a href="{{ route('dokumen.surat-tugas.download') }}"
                                               class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">Download</a>
                                        </div>
                                    @else
                                        <span class="text-gray-300">&ndash;</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <button type="button" @click="bukaUpload()"
                                                class="px-3 py-1.5 rounded-lg bg-[#2563EB] text-white text-xs font-medium hover:bg-blue-700 transition">
                                            {{ $suratTugas ? 'Ganti Berkas' : 'Unggah' }}
                                        </button>
                                        @if($suratTugas && Route::has('admin.dokumen.surat-tugas.destroy'))
                                            <button type="button" @click="hapusOpen = true"
                                                    class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">Hapus</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- TABEL MOBILE / TABLET (< lg): hanya Nama + tombol Detail    --}}
            {{-- ============================================================= --}}
            <div class="lg:hidden bg-white rounded-xl border border-blue-100 overflow-hidden">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-3 py-3 text-center w-10">No</th>
                            <th class="px-3 py-3">Dokumen</th>
                            <th class="px-3 py-3 text-center w-28">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-blue-50/40 transition">
                            <td class="px-3 py-4 text-center text-gray-500">1</td>
                            <td class="px-3 py-4">
                                <div class="font-medium text-gray-800 leading-snug">Surat Tugas PKL</div>
                                <div class="text-[11px] text-gray-400 mt-0.5">Berkas tunggal &middot; semua siswa</div>
                                @if($suratTugas)
                                    <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-50 text-green-700">Sudah Diunggah</span>
                                @else
                                    <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-50 text-red-600">Belum Ada</span>
                                @endif
                            </td>
                            <td class="px-3 py-4 text-center">
                                <button type="button" @click="detailOpen = true"
                                        class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white transition active:scale-95 hover:bg-blue-700">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL DETAIL (mobile) - animasi smooth slide-up / fade          --}}
        {{-- ================================================================= --}}
        <div x-show="detailOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
             @keydown.escape.window="detailOpen = false">
            <div x-show="detailOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl text-left"
                 @click.outside="detailOpen = false">
                <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-blue-100 bg-white px-5 py-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Surat Tugas PKL</h3>
                        <p class="text-xs text-gray-400">Berkas tunggal untuk semua siswa &amp; guru</p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Status Berkas</p>
                        @if($suratTugas)
                            <span class="mt-1 inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">Sudah Diunggah</span>
                        @else
                            <span class="mt-1 inline-block px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-600">Belum Ada</span>
                        @endif
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Keterangan</p>
                        <p class="mt-0.5 text-sm text-gray-700">Surat Tugas resmi yang berlaku untuk semua siswa. Mengganti berkas akan menimpa Surat Tugas yang lama.</p>
                    </div>

                    <div class="rounded-xl border border-blue-100 p-3">
                        <p class="text-xs font-bold text-gray-700 mb-2">Berkas Surat Tugas</p>
                        @if($suratTugas)
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('dokumen.surat-tugas.lihat') }}" target="_blank"
                                   class="px-3 py-1.5 rounded-md bg-blue-50 text-[#2563EB] text-xs font-medium hover:bg-blue-100 transition">Lihat PDF</a>
                                <a href="{{ route('dokumen.surat-tugas.download') }}"
                                   class="px-3 py-1.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">Download</a>
                            </div>
                        @else
                            <p class="text-xs italic text-gray-400">Belum ada berkas yang diunggah.</p>
                        @endif
                    </div>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 flex gap-2 border-t border-blue-100 bg-white px-5 py-4">
                    <button type="button" @click="detailOpen = false; bukaUpload()"
                            class="flex-1 rounded-xl bg-[#2563EB] px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                        {{ $suratTugas ? 'Ganti Berkas' : 'Unggah' }}
                    </button>
                    @if($suratTugas && Route::has('admin.dokumen.surat-tugas.destroy'))
                        <button type="button" @click="detailOpen = false; hapusOpen = true"
                                class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100">Hapus</button>
                    @endif
                    <button type="button" @click="detailOpen = false"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 transition hover:bg-gray-50">Tutup</button>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL UPLOAD / GANTI BERKAS                                     --}}
        {{-- ================================================================= --}}
        <div x-show="open" x-cloak @keydown.escape.window="open = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 p-0 sm:p-4">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="relative w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl p-6" @click.outside="open = false">
                <h3 class="text-lg font-bold text-gray-800">{{ $suratTugas ? 'Ganti Surat Tugas' : 'Unggah Surat Tugas' }}</h3>
                <p class="text-sm text-gray-500 mb-4">Berkas berlaku untuk semua siswa &amp; guru.</p>
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
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition">Batal</button>
                        <button type="submit"
                                class="px-5 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                            {{ $suratTugas ? 'Ganti Berkas' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL HAPUS (hanya jika route destroy tersedia)                 --}}
        {{-- ================================================================= --}}
        @if(Route::has('admin.dokumen.surat-tugas.destroy'))
        <div x-show="hapusOpen" x-cloak @keydown.escape.window="hapusOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div x-show="hapusOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="hapusOpen = false">
                <h3 class="text-lg font-bold text-gray-800">Hapus Surat Tugas</h3>
                <p class="text-sm text-gray-600 mt-2">Yakin ingin menghapus Surat Tugas ini? Siswa &amp; guru tidak akan bisa mengunduhnya lagi. Tindakan ini tidak bisa dibatalkan.</p>
                <form method="POST" action="{{ route('admin.dokumen.surat-tugas.destroy') }}" class="flex justify-end gap-2 pt-5">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false"
                            class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition">Hapus</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <script>
        window.suratTugasCrud = function () {
            return {
                open: false,
                detailOpen: false,
                hapusOpen: false,

                init() {
                    this.$watch('open',       () => this.kunciScroll());
                    this.$watch('detailOpen', () => this.kunciScroll());
                    this.$watch('hapusOpen',  () => this.kunciScroll());
                },
                kunciScroll() {
                    document.body.style.overflow = (this.open || this.detailOpen || this.hapusOpen) ? 'hidden' : '';
                },

                bukaUpload() { this.open = true; },
            };
        };
    </script>
</x-app-layout>

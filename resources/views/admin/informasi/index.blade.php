<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kelola Informasi PKL
        </h2>
    </x-slot>

    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-8 md:py-12 bg-white" x-data="informasiCrud()">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100">
                <div class="p-4 sm:p-6 text-gray-900">

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-6">
                        <p class="text-sm text-gray-500">Kelola konten informasi PKL yang tampil untuk siswa &amp; guru.</p>
                        <a href="{{ route('admin.informasi.create') }}"
                           class="inline-flex items-center justify-center bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition shrink-0">
                            Tambah Informasi
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 text-green-800 p-3 rounded mb-4 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- ============================================================= --}}
                    {{-- TABEL DESKTOP / LAPTOP (>= lg): tampilkan SEMUA informasi   --}}
                    {{-- ============================================================= --}}
                    <div class="hidden lg:block overflow-x-auto rounded-lg border">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 border-b text-center w-20">Urutan</th>
                                    <th class="px-4 py-3 border-b">Judul</th>
                                    <th class="px-4 py-3 border-b">Konten</th>
                                    <th class="px-4 py-3 border-b text-center w-28">Lampiran</th>
                                    <th class="px-4 py-3 border-b text-center w-40">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($informasi as $item)
                                    <tr class="bg-white hover:bg-gray-50 transition align-top">
                                        <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $item->urutan }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900 break-words">{{ $item->judul }}</td>
                                        <td class="px-4 py-3 text-gray-600">{!! \Illuminate\Support\Str::limit(strip_tags($item->konten), 80) !!}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if(!empty($item->file))
                                                <a href="{{ asset('storage/' . $item->file) }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                                            @else
                                                <span class="text-gray-400">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <a href="{{ route('admin.informasi.edit', $item) }}"
                                               class="text-blue-600 hover:underline">Edit</a>
                                            <button type="button"
                                                    @click="konfirmHapus(@js(route('admin.informasi.destroy', $item)), @js($item->judul))"
                                                    class="text-red-600 hover:underline ml-3">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400 italic">Belum ada informasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ============================================================= --}}
                    {{-- TABEL MOBILE / TABLET (< lg): hanya Judul + tombol Detail   --}}
                    {{-- ============================================================= --}}
                    <div class="lg:hidden overflow-hidden rounded-lg border">
                        <table class="w-full text-sm border-collapse">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 text-left">
                                <tr>
                                    <th class="px-3 py-3 text-center w-14">Urutan</th>
                                    <th class="px-3 py-3">Judul</th>
                                    <th class="px-3 py-3 text-center w-28">Detail</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($informasi as $item)
                                    <tr class="bg-white hover:bg-gray-50 transition">
                                        <td class="px-3 py-4 text-center font-semibold text-gray-700">{{ $item->urutan }}</td>
                                        <td class="px-3 py-4">
                                            <div class="font-medium text-gray-900 leading-snug break-words">{{ $item->judul }}</div>
                                            @if(!empty($item->file))
                                                <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-50 text-blue-700">Ada Lampiran</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 text-center">
                                            <button type="button"
                                                    @click="lihatDetail(@js([
                                                        'urutan' => $item->urutan,
                                                        'judul' => $item->judul,
                                                        'konten' => $item->konten,
                                                        'file_url' => !empty($item->file) ? asset('storage/' . $item->file) : null,
                                                        'edit_url' => route('admin.informasi.edit', $item),
                                                        'destroy_url' => route('admin.informasi.destroy', $item),
                                                    ]))"
                                                    class="inline-flex items-center justify-center gap-1 rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition active:scale-95 hover:bg-blue-700">
                                                Lihat Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-400 italic">Belum ada informasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($informasi, 'links'))
                        <div class="mt-4">{{ $informasi->links() }}</div>
                    @endif
                </div>
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
                <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-gray-100 bg-white px-5 py-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Urutan <span x-text="detailData.urutan"></span></p>
                        <h3 class="text-base font-bold text-gray-800" x-text="detailData.judul"></h3>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-1">Konten</p>
                        <div class="prose prose-sm max-w-none text-gray-700" x-html="detailData.konten || '<em>Tidak ada konten.</em>'"></div>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-1">Lampiran</p>
                        <template x-if="detailData.file_url">
                            <a :href="detailData.file_url" target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 text-xs font-medium hover:bg-blue-100 transition">Lihat Lampiran</a>
                        </template>
                        <template x-if="!detailData.file_url">
                            <p class="text-xs italic text-gray-400">Tidak ada lampiran.</p>
                        </template>
                    </div>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 flex gap-2 border-t border-gray-100 bg-white px-5 py-4">
                    <a :href="detailData.edit_url"
                       class="flex-1 rounded-xl bg-blue-600 px-3 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-700">Edit</a>
                    <button type="button" @click="detailOpen = false; konfirmHapus(detailData.destroy_url, detailData.judul)"
                            class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100">Hapus</button>
                    <button type="button" @click="detailOpen = false"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 transition hover:bg-gray-50">Tutup</button>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL HAPUS                                                     --}}
        {{-- ================================================================= --}}
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
                <h3 class="text-lg font-bold text-gray-800">Hapus Informasi</h3>
                <p class="text-sm text-gray-600 mt-2">
                    Yakin ingin menghapus <span class="font-semibold" x-text="hapusLabel"></span>? Data informasi beserta lampirannya akan dihapus permanen.
                </p>
                <form method="POST" :action="hapusUrl" class="flex justify-end gap-2 pt-5">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false"
                            class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition">Ya, hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.informasiCrud = function () {
            return {
                detailOpen: false,
                detailData: {},
                hapusOpen: false,
                hapusUrl: '',
                hapusLabel: '',

                init() {
                    this.$watch('detailOpen', () => this.kunciScroll());
                    this.$watch('hapusOpen',  () => this.kunciScroll());
                },
                kunciScroll() {
                    document.body.style.overflow = (this.detailOpen || this.hapusOpen) ? 'hidden' : '';
                },

                lihatDetail(d) { this.detailData = d; this.detailOpen = true; },

                konfirmHapus(url, label) {
                    this.hapusUrl   = url;
                    this.hapusLabel = label || 'informasi ini';
                    this.hapusOpen  = true;
                },
            };
        };
    </script>
</x-app-layout>

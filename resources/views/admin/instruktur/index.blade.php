<x-app-layout title="Data Industri & Pembimbing">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-6 sm:py-8 md:py-10 bg-white" x-data="instrukturCrud()">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- ===== HEADER + AKSI ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Master Data &mdash; Industri &amp; Pembimbing</h2>
                    <p class="text-sm text-gray-500">Kelola data industri/tempat PKL beserta nama pembimbing (instruktur) industrinya. Instruktur tidak lagi memiliki akun login.</p>
                </div>
                <a href="{{ route('admin.instruktur.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700 shrink-0">
                    Tambah Industri
                </a>
            </div>

            {{-- ===== KARTU INFORMASI ===== --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Total Industri</p>
                    <p class="mt-2 text-2xl font-bold text-[#2563EB]">{{ $rekap['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Punya Pembimbing</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['pembimbing'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Punya Siswa</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">{{ $rekap['ada_siswa'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Siswa Ditempatkan</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['siswa_industri'] }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 sm:p-5">
                {{-- ===== SEARCH FILTER ===== --}}
                <form method="GET" class="mb-4 flex gap-2">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari perusahaan / pembimbing / alamat..."
                           class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
                    @if($q)
                        <a href="{{ route('admin.instruktur.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
                    @endif
                </form>

                {{-- ============================================================= --}}
                {{-- TABEL DESKTOP / LAPTOP (>= lg): tampilkan SEMUA informasi   --}}
                {{-- ============================================================= --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b border-blue-100">
                                <th class="py-3 px-3 w-12 text-center">No</th>
                                <th class="py-3 px-3">Nama Perusahaan</th>
                                <th class="py-3 px-3">Pembimbing (Instruktur)</th>
                                <th class="py-3 px-3">Alamat</th>
                                <th class="py-3 px-3">Telepon</th>
                                <th class="py-3 px-3 text-center">Jumlah Siswa</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($industri as $item)
                                <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                                    <td class="py-3 px-3 text-center text-gray-500">{{ $industri->firstItem() + $loop->index }}</td>
                                    <td class="py-3 px-3 font-medium text-gray-800">{{ $item->nama_perusahaan }}</td>
                                    <td class="py-3 px-3 text-gray-600">{{ $item->pembimbing_industri ?? '-' }}</td>
                                    <td class="py-3 px-3 text-gray-600">{{ $item->alamat ?? '-' }}</td>
                                    <td class="py-3 px-3 text-gray-600">{{ $item->telepon ?? '-' }}</td>
                                    <td class="py-3 px-3 text-center text-gray-600">{{ $item->siswa_count }}</td>
                                    <td class="py-3 px-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.instruktur.edit', $item) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100">Edit</a>
                                            <button type="button"
                                                    @click="konfirmHapus(@js(route('admin.instruktur.destroy', $item)), @js($item->nama_perusahaan))"
                                                    class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">Belum ada data industri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ============================================================= --}}
                {{-- TABEL MOBILE / TABLET (< lg): hanya Nama + tombol Detail    --}}
                {{-- ============================================================= --}}
                <div class="lg:hidden overflow-hidden rounded-xl border border-blue-100">
                    <table class="w-full text-sm border-collapse">
                        <thead class="bg-blue-50 text-gray-600 text-left">
                            <tr>
                                <th class="px-3 py-3 text-center w-10">No</th>
                                <th class="px-3 py-3">Perusahaan</th>
                                <th class="px-3 py-3 text-center w-28">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($industri as $item)
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-3 py-4 text-center text-gray-500">{{ $industri->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-4">
                                        <div class="font-medium text-gray-800 leading-snug break-words">{{ $item->nama_perusahaan }}</div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">{{ $item->siswa_count }} siswa ditempatkan</div>
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <button type="button"
                                                @click="lihatDetail(@js([
                                                    'nama_perusahaan' => $item->nama_perusahaan,
                                                    'pembimbing' => $item->pembimbing_industri ?? '-',
                                                    'alamat' => $item->alamat ?? '-',
                                                    'telepon' => $item->telepon ?? '-',
                                                    'siswa_count' => $item->siswa_count,
                                                    'edit_url' => route('admin.instruktur.edit', $item),
                                                    'destroy_url' => route('admin.instruktur.destroy', $item),
                                                ]))"
                                                class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white transition active:scale-95 hover:bg-blue-700">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-400">Belum ada data industri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $industri->links() !!}
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
                <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b border-blue-100 bg-white px-5 py-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800" x-text="detailData.nama_perusahaan"></h3>
                        <p class="text-xs text-gray-400">Pembimbing: <span x-text="detailData.pembimbing"></span></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Pembimbing (Instruktur)</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.pembimbing"></p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Alamat</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800 whitespace-pre-line" x-text="detailData.alamat"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Telepon</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.telepon"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Jumlah Siswa</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.siswa_count"></p>
                        </div>
                    </div>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 flex gap-2 border-t border-blue-100 bg-white px-5 py-4">
                    <a :href="detailData.edit_url"
                       class="flex-1 rounded-xl bg-[#2563EB] px-3 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-700">Edit</a>
                    <button type="button" @click="detailOpen = false; konfirmHapus(detailData.destroy_url, detailData.nama_perusahaan)"
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
                 class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="hapusOpen = false">
                <h3 class="text-lg font-bold text-gray-800">Hapus Data Industri</h3>
                <p class="text-sm text-gray-600 mt-2">
                    Yakin ingin menghapus <span class="font-semibold" x-text="hapusLabel"></span>? Data industri hanya bisa dihapus jika tidak dipakai siswa.
                </p>
                <form method="POST" :action="hapusUrl" class="flex justify-end gap-2 pt-5">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false"
                            class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">Ya, hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.instrukturCrud = function () {
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
                    this.hapusLabel = label || 'data industri ini';
                    this.hapusOpen  = true;
                },
            };
        };
    </script>
</x-app-layout>

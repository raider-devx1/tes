<x-app-layout title="Akun Guru Pembimbing">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-6 sm:py-8 md:py-10 bg-white" x-data="guruCrud()">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- ===== HEADER + AKSI ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Master Data &mdash; Guru Pembimbing</h2>
                    <p class="text-sm text-gray-500">Kelola akun guru pembimbing PKL.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.guru.export.excel', ['q' => $q]) }}"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-green-50 text-green-700 text-sm font-medium hover:bg-green-100">
                         Excel
                    </a>
                    <a href="{{ route('admin.guru.export.pdf', ['q' => $q]) }}"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100">
                         PDF
                    </a>
                    <button @click="importOpen = true"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium hover:bg-amber-100">
                         Import
                    </button>
                    <a href="{{ route('admin.guru.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                        Tambah Guru
                    </a>
                </div>
            </div>

            {{-- ===== KARTU INFORMASI ===== --}}
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Total Guru</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Punya Bimbingan</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">{{ $rekap['ada_bimbingan'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Tanpa Bimbingan</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ $rekap['tanpa_bimbingan'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Siswa Dibimbing</p>
                    <p class="mt-2 text-2xl font-bold text-[#2563EB]">{{ $rekap['siswa_dibimbing'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Wakasek</p>
                    <p class="mt-2 text-2xl font-bold text-purple-600">{{ $rekap['wakasek'] ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 sm:p-5">
                {{-- ===== SEARCH FILTER ===== --}}
                <form method="GET" class="mb-4 flex gap-2">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / NIP..."
                           class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
                    @if($q)
                        <a href="{{ route('admin.guru.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
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
                                <th class="py-3 px-3">Nama</th>
                                <th class="py-3 px-3">NIP</th>
                                <th class="py-3 px-3">No. HP</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($guru as $g)
                                <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                                    <td class="py-3 px-3 text-center text-gray-500">{{ $guru->firstItem() + $loop->index }}</td>
                                    <td class="py-3 px-3 font-medium text-gray-800">
                                        {{ $g->name }}
                                        @if($g->is_wakasek)
                                            <span class="ml-1 inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-[11px] font-bold text-purple-700 align-middle">Wakasek</span>
                                        @endif
                                        @if($g->is_admin)
                                            <span class="ml-1 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[11px] font-bold text-indigo-700 align-middle">Admin</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-gray-600">{{ $g->nip }}</td>
                                    <td class="py-3 px-3 text-gray-600">{{ $g->no_hp ?? '-' }}</td>
                                    <td class="py-3 px-3">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($g->is_wakasek)
                                                <form method="POST" action="{{ route('admin.guru.batalkan-wakasek', $g) }}"
                                                      onsubmit="return confirm('Batalkan status Wakasek untuk guru ini?')">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100">Batalkan Wakasek</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.guru.jadikan-wakasek', $g) }}"
                                                      onsubmit="return confirm('Jadikan guru ini sebagai Wakasek?')">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100">Jadikan Wakasek</button>
                                                </form>
                                            @endif
                                            @if($g->is_admin)
                                                <form method="POST" action="{{ route('admin.guru.batalkan-admin', $g) }}"
                                                      onsubmit="return confirm('Batalkan akses admin untuk guru ini?')">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100">Batalkan Admin</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.guru.jadikan-admin', $g) }}"
                                                      onsubmit="return confirm('Jadikan guru ini juga sebagai Admin?')">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100">Jadikan Admin</button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.guru.edit', $g) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100">Edit</a>
                                            <button type="button"
                                                    @click="konfirmHapus(@js(route('admin.guru.destroy', $g)), @js($g->name))"
                                                    class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada akun guru pembimbing.</td></tr>
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
                                <th class="px-3 py-3">Nama</th>
                                <th class="px-3 py-3 text-center w-28">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($guru as $g)
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-3 py-4 text-center text-gray-500">{{ $guru->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-4">
                                        <div class="font-medium text-gray-800 leading-snug break-words">
                                            {{ $g->name }}
                                            @if($g->is_wakasek)
                                                <span class="ml-1 inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-700 align-middle">Wakasek</span>
                                            @endif
                                            @if($g->is_admin)
                                                <span class="ml-1 inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold text-indigo-700 align-middle">Admin</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5 font-mono">NIP: {{ $g->nip }}</div>
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <button type="button"
                                                @click="lihatDetail(@js([
                                                    'nama' => $g->name,
                                                    'nip' => $g->nip,
                                                    'no_hp' => $g->no_hp ?? '-',
                                                    'is_wakasek' => (bool) $g->is_wakasek,
                                                    'is_admin' => (bool) $g->is_admin,
                                                    'edit_url' => route('admin.guru.edit', $g),
                                                    'destroy_url' => route('admin.guru.destroy', $g),
                                                    'jadikan_url' => route('admin.guru.jadikan-wakasek', $g),
                                                    'batalkan_url' => route('admin.guru.batalkan-wakasek', $g),
                                                    'jadikan_admin_url' => route('admin.guru.jadikan-admin', $g),
                                                    'batalkan_admin_url' => route('admin.guru.batalkan-admin', $g),
                                                ]))"
                                                class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white transition active:scale-95 hover:bg-blue-700">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-gray-400">Belum ada akun guru pembimbing.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $guru->links() !!}
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
                        <h3 class="text-base font-bold text-gray-800" x-text="detailData.nama"></h3>
                        <p class="text-xs font-mono text-gray-400">NIP: <span x-text="detailData.nip"></span></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">NIP</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.nip"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">No. HP</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.no_hp"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Status Wakasek</p>
                        <p class="mt-0.5 text-sm font-medium" :class="detailData.is_wakasek ? 'text-purple-700' : 'text-gray-500'"
                           x-text="detailData.is_wakasek ? 'Wakasek (dapat memvalidasi observasi)' : 'Bukan Wakasek'"></p>
                        <form method="POST" x-show="!detailData.is_wakasek" :action="detailData.jadikan_url" class="mt-2"
                              onsubmit="return confirm('Jadikan guru ini sebagai Wakasek?')">
                            @csrf @method('PUT')
                            <button type="submit" class="w-full rounded-xl bg-purple-50 px-3 py-2.5 text-sm font-semibold text-purple-700 transition hover:bg-purple-100">Jadikan Wakasek</button>
                        </form>
                        <form method="POST" x-show="detailData.is_wakasek" x-cloak :action="detailData.batalkan_url" class="mt-2"
                              onsubmit="return confirm('Batalkan status Wakasek untuk guru ini?')">
                            @csrf @method('PUT')
                            <button type="submit" class="w-full rounded-xl bg-amber-50 px-3 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">Batalkan Wakasek</button>
                        </form>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Akses Admin</p>
                        <p class="mt-0.5 text-sm font-medium" :class="detailData.is_admin ? 'text-indigo-700' : 'text-gray-500'"
                           x-text="detailData.is_admin ? 'Dapat mengakses panel admin' : 'Tidak punya akses admin'"></p>
                        <form method="POST" x-show="!detailData.is_admin" :action="detailData.jadikan_admin_url" class="mt-2"
                              onsubmit="return confirm('Jadikan guru ini juga sebagai Admin?')">
                            @csrf @method('PUT')
                            <button type="submit" class="w-full rounded-xl bg-indigo-50 px-3 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">Jadikan Admin</button>
                        </form>
                        <form method="POST" x-show="detailData.is_admin" x-cloak :action="detailData.batalkan_admin_url" class="mt-2"
                              onsubmit="return confirm('Batalkan akses admin untuk guru ini?')">
                            @csrf @method('PUT')
                            <button type="submit" class="w-full rounded-xl bg-amber-50 px-3 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100">Batalkan Admin</button>
                        </form>
                    </div>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 flex gap-2 border-t border-blue-100 bg-white px-5 py-4">
                    <a :href="detailData.edit_url"
                       class="flex-1 rounded-xl bg-[#2563EB] px-3 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-700">Edit</a>
                    <button type="button" @click="detailOpen = false; konfirmHapus(detailData.destroy_url, detailData.nama)"
                            class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100">Hapus</button>
                    <button type="button" @click="detailOpen = false"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 transition hover:bg-gray-50">Tutup</button>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL IMPORT DATA                                               --}}
        {{-- ================================================================= --}}
        <div x-show="importOpen" x-cloak @keydown.escape.window="importOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div x-show="importOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="importOpen = false">
                <h3 class="text-lg font-bold text-gray-800 mb-1">Import Data Guru</h3>
                <p class="text-sm text-gray-500 mb-4">Unggah file Excel (.xlsx/.csv) sesuai template. NIP tidak boleh sama dengan yang sudah terdaftar.</p>
                <form method="POST" action="{{ route('admin.guru.import') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                        class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 mb-4">
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('admin.guru.template') }}" class="text-sm text-[#2563EB] hover:underline"> Unduh Template</a>
                        <div class="flex gap-2">
                            <button type="button" @click="importOpen = false" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Import</button>
                        </div>
                    </div>
                </form>
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
                <h3 class="text-lg font-bold text-gray-800">Hapus Akun Guru</h3>
                <p class="text-sm text-gray-600 mt-2">
                    Yakin ingin menghapus akun <span class="font-semibold" x-text="hapusLabel"></span>? Tindakan ini tidak bisa dibatalkan.
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
        window.guruCrud = function () {
            return {
                importOpen: false,
                detailOpen: false,
                detailData: {},
                hapusOpen: false,
                hapusUrl: '',
                hapusLabel: '',

                init() {
                    this.$watch('importOpen', () => this.kunciScroll());
                    this.$watch('detailOpen', () => this.kunciScroll());
                    this.$watch('hapusOpen',  () => this.kunciScroll());
                },
                kunciScroll() {
                    document.body.style.overflow = (this.importOpen || this.detailOpen || this.hapusOpen) ? 'hidden' : '';
                },

                lihatDetail(d) { this.detailData = d; this.detailOpen = true; },

                konfirmHapus(url, label) {
                    this.hapusUrl   = url;
                    this.hapusLabel = label || 'guru ini';
                    this.hapusOpen  = true;
                },
            };
        };
    </script>
</x-app-layout>

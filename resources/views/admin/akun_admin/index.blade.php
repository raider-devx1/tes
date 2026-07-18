<x-app-layout title="Kelola Akun Admin">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-6 sm:py-8 md:py-10 bg-white" x-data="akunAdminCrud()">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- TOP HEADER SECTION --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Pengaturan &mdash; Kelola Akun Admin</h2>
                    <p class="text-sm text-gray-500">Tambah, edit, dan hapus akun administrator sistem.</p>
                </div>
                <a href="{{ route('admin.akun-admin.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700 shrink-0">
                    + Tambah Admin
                </a>
            </div>

            {{-- FLASH ALERTS --}}
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            {{-- STATISTICS CARDS --}}
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Total Admin</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Akun Anda</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">{{ $rekap['akun_anda'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Admin Lain</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ $rekap['admin_lain'] }}</p>
                </div>
            </div>

            {{-- MAIN DATA CONTAINER --}}
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 sm:p-5">
                {{-- SEARCH FORM --}}
                <form method="GET" class="mb-4 flex gap-2">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / NIP..."
                           class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
                    @if($q)
                        <a href="{{ route('admin.akun-admin.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
                    @endif
                </form>

                {{-- ============================================================= --}}
                {{-- TABEL DESKTOP / LAPTOP (>= lg): tampilkan SEMUA informasi   --}}
                {{-- ============================================================= --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b border-blue-100">
                                <th class="py-3 px-4 w-16 text-center">No</th>
                                <th class="py-3 px-6 min-w-[200px]">Nama</th>
                                <th class="py-3 px-6 min-w-[200px]">NIP</th>
                                <th class="py-3 px-6 text-right w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($admins as $admin)
                                <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                                    <td class="py-4 px-4 text-center text-gray-500">{{ $admins->firstItem() + $loop->index }}</td>
                                    <td class="py-4 px-6 font-medium text-gray-800 whitespace-nowrap">
                                        {{ $admin->name }}
                                        @if($admin->id === auth()->id())
                                            <span class="ml-2 text-[10px] px-2 py-0.5 rounded-full bg-green-50 text-green-600 inline-block align-middle font-normal">Akun Anda</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-gray-600 whitespace-nowrap">{{ $admin->nip ?? '-' }}</td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                            <a href="{{ route('admin.akun-admin.edit', $admin->id) }}"
                                               class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100 font-medium">Edit</a>
                                            @if($admin->id !== auth()->id())
                                                <button type="button"
                                                        @click="konfirmHapus(@js(route('admin.akun-admin.destroy', $admin->id)))"
                                                        class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-medium">Hapus</button>
                                            @else
                                                <span class="text-xs px-3 py-1.5 rounded-lg bg-gray-50 text-gray-400 cursor-not-allowed select-none">Hapus</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-400">Belum ada akun admin.</td>
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
                                <th class="px-3 py-3">Nama</th>
                                <th class="px-3 py-3 text-center w-28">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($admins as $admin)
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-3 py-4 text-center text-gray-500">{{ $admins->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-800 leading-snug break-words">{{ $admin->name }}</span>
                                            @if($admin->id === auth()->id())
                                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-green-50 text-green-600 shrink-0">Akun Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5 font-mono">NIP: {{ $admin->nip ?? '-' }}</div>
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <button type="button"
                                                @click="lihatDetail(@js([
                                                    'nama' => $admin->name,
                                                    'nip' => $admin->nip ?? '-',
                                                    'is_self' => $admin->id === auth()->id(),
                                                    'edit_url' => route('admin.akun-admin.edit', $admin->id),
                                                    'destroy_url' => $admin->id !== auth()->id() ? route('admin.akun-admin.destroy', $admin->id) : null,
                                                ]))"
                                                class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white transition active:scale-95 hover:bg-blue-700">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-400">Belum ada akun admin.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION LINKS --}}
                <div class="mt-4">
                    {!! $admins->links() !!}
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
                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <span x-text="detailData.nama"></span>
                            <template x-if="detailData.is_self">
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-green-50 text-green-600 font-normal">Akun Anda</span>
                            </template>
                        </h3>
                        <p class="text-xs font-mono text-gray-400">NIP: <span x-text="detailData.nip"></span></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">NIP</p>
                        <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.nip"></p>
                    </div>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 flex gap-2 border-t border-blue-100 bg-white px-5 py-4">
                    <a :href="detailData.edit_url"
                       class="flex-1 rounded-xl bg-[#2563EB] px-3 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-700">Edit</a>
                    <template x-if="!detailData.is_self">
                        <button type="button" @click="detailOpen = false; konfirmHapus(detailData.destroy_url)"
                                class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100">Hapus</button>
                    </template>
                    <template x-if="detailData.is_self">
                        <span class="rounded-xl bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-400 cursor-not-allowed select-none">Hapus</span>
                    </template>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- CONFIRM HAPUS MODAL                                             --}}
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
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false">
                <h3 class="text-base font-bold text-black">Hapus Akun Admin</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Yakin ingin menghapus akun admin ini? Tindakan ini tidak dapat dibatalkan.</p>
                <form :action="hapusUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#cf202f] px-4 py-2 text-sm font-bold text-white hover:bg-[#b01926]">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ALPINE JS LOGIC --}}
    <script>
        window.akunAdminCrud = function () {
            return {
                detailOpen: false,
                detailData: {},
                hapusOpen: false,
                hapusUrl: '',

                init() {
                    this.$watch('detailOpen', () => this.kunciScroll());
                    this.$watch('hapusOpen',  () => this.kunciScroll());
                },
                kunciScroll() {
                    document.body.style.overflow = (this.detailOpen || this.hapusOpen) ? 'hidden' : '';
                },

                lihatDetail(d) { this.detailData = d; this.detailOpen = true; },

                konfirmHapus(url) {
                    if (!url) return;
                    this.hapusUrl  = url;
                    this.hapusOpen = true;
                },
            };
        };
    </script>
</x-app-layout>

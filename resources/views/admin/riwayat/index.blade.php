<x-app-layout title="Riwayat Aktivitas">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-6 sm:py-8 md:py-10 bg-white" x-data="riwayatDetail()">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10 space-y-6">

            <div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">Riwayat Aktivitas</h2>
                <p class="text-sm text-gray-500">Seluruh aktivitas yang terjadi di sistem (siapa melakukan apa &amp; kapan).</p>
            </div>

            {{-- ===== FILTER TANGGAL ===== --}}
            <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ $from }}"
                           class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to }}"
                           class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700 transition">Filter</button>
                    <a href="{{ route('admin.riwayat.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition inline-block text-center">Reset</a>
                </div>
            </form>

            {{-- ============================================================= --}}
            {{-- TABEL DESKTOP / LAPTOP (>= lg): tampilkan SEMUA informasi   --}}
            {{-- ============================================================= --}}
            <div class="hidden lg:block bg-white rounded-xl border border-blue-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-50 text-gray-600 text-left">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">NISN</th>
                                <th class="px-4 py-3">NIP</th>
                                <th class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                                <th class="px-4 py-3">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                                @php $u = $log->user; @endphp
                                <tr class="hover:bg-blue-50/40 align-top transition">
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $logs->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">{{ optional($u)->name ?? 'Pengguna dihapus' }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ optional($u)->nisn ?: '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ optional($u)->nip ?: '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">Belum ada riwayat aktivitas.</td>
                                </tr>
                            @endforelse
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
                            <th class="px-3 py-3">Nama</th>
                            <th class="px-3 py-3 text-center w-28">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            @php $u = $log->user; @endphp
                            <tr class="hover:bg-blue-50/40 transition">
                                <td class="px-3 py-4 text-center text-gray-500">{{ $logs->firstItem() + $loop->index }}</td>
                                <td class="px-3 py-4">
                                    <div class="font-medium text-gray-800 leading-snug break-words">{{ optional($u)->name ?? 'Pengguna dihapus' }}</div>
                                    <div class="text-[11px] text-gray-400 mt-0.5 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y') }}</div>
                                </td>
                                <td class="px-3 py-4 text-center">
                                    <button type="button"
                                            @click="lihatDetail(@js([
                                                'nama' => optional($u)->name ?? 'Pengguna dihapus',
                                                'nisn' => optional($u)->nisn ?: '-',
                                                'nip' => optional($u)->nip ?: '-',
                                                'tanggal' => \Carbon\Carbon::parse($log->created_at)->translatedFormat('d M Y'),
                                                'aktivitas' => $log->description,
                                            ]))"
                                            class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white transition active:scale-95 hover:bg-blue-700">
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-400 italic">Belum ada riwayat aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div>
                {!! $logs->links() !!}
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
                        <p class="text-xs text-gray-400" x-text="detailData.tanggal"></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">NISN</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.nisn"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">NIP</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.nip"></p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Tanggal</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.tanggal"></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mb-1">Aktivitas</p>
                        <div class="rounded-lg border-l-4 border-[#2563EB] bg-blue-50/50 p-3 text-sm text-gray-700 whitespace-pre-line" x-text="detailData.aktivitas"></div>
                    </div>
                </div>

                <div class="sticky bottom-0 z-10 flex justify-end border-t border-blue-100 bg-white px-5 py-4">
                    <button type="button" @click="detailOpen = false"
                            class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-500 transition hover:bg-gray-50">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.riwayatDetail = function () {
            return {
                detailOpen: false,
                detailData: {},

                init() {
                    this.$watch('detailOpen', () => {
                        document.body.style.overflow = this.detailOpen ? 'hidden' : '';
                    });
                },

                lihatDetail(d) { this.detailData = d; this.detailOpen = true; },
            };
        };
    </script>
</x-app-layout>

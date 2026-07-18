<x-app-layout title="Periode PKL">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-6 sm:py-8 md:py-10 bg-white" x-data="periodeCrud()">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- ===== HEADER + AKSI ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Master Data &mdash; Periode PKL</h2>
                    <p class="text-sm text-gray-500">Kelola gelombang/periode pelaksanaan PKL.</p>
                </div>
                <a href="{{ route('admin.periode.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700 shrink-0">
                    Tambah Periode
                </a>
            </div>

            {{-- ===== FLASH MESSAGE ===== --}}
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 text-red-600 px-4 py-3 text-sm">{{ session('error') }}</div>
            @endif

            {{-- ===== CARD: ATUR STATUS SISWA PER PERIODE ===== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 sm:p-5 mb-6">
                <h3 class="text-base font-semibold text-gray-800 mb-1">Atur Status Siswa per Periode</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Pilih periode, lalu ubah status PKL <strong>seluruh siswa</strong> pada periode tersebut sekaligus
                    (belum / aktif / selesai). Berguna, misalnya, untuk menandai semua siswa periode lama menjadi "selesai".
                </p>
                <form method="POST" action="{{ route('admin.periode.update-status-siswa') }}"
                      x-ref="statusForm" @submit.prevent="konfirmStatus()">
                    @csrf
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Periode PKL</label>
                            <select name="periode_id" required
                                    class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                                <option value="">-- Pilih Periode --</option>
                                @foreach($semuaPeriode as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->nama }} &mdash; {{ $item->tahun_ajaran }} {{ $item->is_active ? '(Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Ubah Status Menjadi</label>
                            <select name="status_pkl" required
                                    class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                                <option value="belum">Belum</option>
                                <option value="aktif">Aktif</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                                Terapkan ke Semua Siswa
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ===== TABEL DATA PERIODE ===== --}}
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 sm:p-5">
                <form method="GET" class="mb-4 flex gap-2">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / tahun ajaran..."
                           class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <button class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
                    @if($q)
                        <a href="{{ route('admin.periode.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
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
                                <th class="py-3 px-3">Nama Periode</th>
                                <th class="py-3 px-3">Tahun Ajaran</th>
                                <th class="py-3 px-3">Mulai</th>
                                <th class="py-3 px-3">Selesai</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periode as $p)
                                <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                                    <td class="py-3 px-3 text-center text-gray-500">{{ $periode->firstItem() + $loop->index }}</td>
                                    <td class="py-3 px-3 font-medium text-gray-800">{{ $p->nama }}</td>
                                    <td class="py-3 px-3 text-gray-600">{{ $p->tahun_ajaran }}</td>
                                    <td class="py-3 px-3 text-gray-600">{{ \Carbon\Carbon::parse($p->tanggal_mulai)->translatedFormat('d M Y') }}</td>
                                    <td class="py-3 px-3 text-gray-600">{{ \Carbon\Carbon::parse($p->tanggal_selesai)->translatedFormat('d M Y') }}</td>
                                    <td class="py-3 px-3">
                                        @if($p->is_active)
                                            <span class="text-xs px-2 py-1 rounded-full bg-[#2563EB] text-white">Aktif</span>
                                        @else
                                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-500">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="flex items-center justify-end gap-2">
                                            @unless($p->is_active)
                                                <form method="POST" action="{{ route('admin.periode.aktifkan', $p->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button class="text-xs px-3 py-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100">Aktifkan</button>
                                                </form>
                                            @endunless
                                            <a href="{{ route('admin.periode.edit', $p->id) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100">Edit</a>
                                            <button type="button"
                                                    @click="konfirmHapus(@js(route('admin.periode.destroy', $p)), @js($p->nama))"
                                                    class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">Belum ada data periode.</td>
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
                                <th class="px-3 py-3">Nama Periode</th>
                                <th class="px-3 py-3 text-center w-28">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($periode as $p)
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-3 py-4 text-center text-gray-500">{{ $periode->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-800 leading-snug break-words">{{ $p->nama }}</span>
                                            @if($p->is_active)
                                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#2563EB] text-white shrink-0">Aktif</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">{{ $p->tahun_ajaran }}</div>
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <button type="button"
                                                @click="lihatDetail(@js([
                                                    'nama' => $p->nama,
                                                    'tahun_ajaran' => $p->tahun_ajaran,
                                                    'mulai' => \Carbon\Carbon::parse($p->tanggal_mulai)->translatedFormat('d M Y'),
                                                    'selesai' => \Carbon\Carbon::parse($p->tanggal_selesai)->translatedFormat('d M Y'),
                                                    'is_active' => (bool) $p->is_active,
                                                    'aktifkan_url' => route('admin.periode.aktifkan', $p->id),
                                                    'edit_url' => route('admin.periode.edit', $p->id),
                                                    'destroy_url' => route('admin.periode.destroy', $p),
                                                ]))"
                                                class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white transition active:scale-95 hover:bg-blue-700">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-400">Belum ada data periode.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {!! $periode->links() !!}
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
                        <p class="text-xs text-gray-400">Tahun Ajaran: <span x-text="detailData.tahun_ajaran"></span></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Status</p>
                        <p class="mt-1">
                            <template x-if="detailData.is_active">
                                <span class="text-xs px-2 py-1 rounded-full bg-[#2563EB] text-white">Aktif</span>
                            </template>
                            <template x-if="!detailData.is_active">
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-500">Nonaktif</span>
                            </template>
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Mulai</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.mulai"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Selesai</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.selesai"></p>
                        </div>
                    </div>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 flex flex-wrap gap-2 border-t border-blue-100 bg-white px-5 py-4">
                    <template x-if="!detailData.is_active">
                        <form method="POST" :action="detailData.aktifkan_url" class="contents">
                            @csrf
                            @method('PUT')
                            <button type="submit"
                                    class="flex-1 rounded-xl bg-green-50 px-3 py-2.5 text-center text-sm font-semibold text-green-600 transition hover:bg-green-100">Aktifkan</button>
                        </form>
                    </template>
                    <a :href="detailData.edit_url"
                       class="flex-1 rounded-xl bg-[#2563EB] px-3 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-700">Edit</a>
                    <button type="button" @click="detailOpen = false; konfirmHapus(detailData.destroy_url, detailData.nama)"
                            class="rounded-xl bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-100">Hapus</button>
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
                <h3 class="text-lg font-bold text-gray-800">Hapus Periode</h3>
                <p class="text-sm text-gray-600 mt-2">
                    Yakin ingin menghapus periode <span class="font-semibold" x-text="hapusLabel"></span>? Tindakan ini tidak bisa dibatalkan.
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

        {{-- ================================================================= --}}
        {{-- MODAL KONFIRMASI UBAH STATUS SEMUA SISWA                        --}}
        {{-- ================================================================= --}}
        <div x-show="statusOpen" x-cloak @keydown.escape.window="statusOpen = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div x-show="statusOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="statusOpen = false">
                <h3 class="text-lg font-bold text-gray-800">Ubah Status Semua Siswa</h3>
                <p class="text-sm text-gray-600 mt-2">
                    Ubah status SEMUA siswa pada periode ini? Tindakan ini berlaku untuk seluruh siswa pada periode yang dipilih.
                </p>
                <div class="flex justify-end gap-2 pt-5">
                    <button type="button" @click="statusOpen = false"
                            class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Batal</button>
                    <button type="button" @click="statusOpen = false; $refs.statusForm.submit()"
                            class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Ya, terapkan</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.periodeCrud = function () {
            return {
                detailOpen: false,
                detailData: {},
                hapusOpen: false,
                hapusUrl: '',
                hapusLabel: '',
                statusOpen: false,

                init() {
                    this.$watch('detailOpen', () => this.kunciScroll());
                    this.$watch('hapusOpen',  () => this.kunciScroll());
                    this.$watch('statusOpen', () => this.kunciScroll());
                },
                kunciScroll() {
                    document.body.style.overflow = (this.detailOpen || this.hapusOpen || this.statusOpen) ? 'hidden' : '';
                },

                lihatDetail(d) { this.detailData = d; this.detailOpen = true; },

                konfirmHapus(url, label) {
                    this.hapusUrl   = url;
                    this.hapusLabel = label || 'periode ini';
                    this.hapusOpen  = true;
                },

                konfirmStatus() { this.statusOpen = true; },
            };
        };
    </script>
</x-app-layout>

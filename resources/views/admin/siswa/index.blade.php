<x-app-layout title="Data Siswa PKL">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-6 sm:py-8 md:py-10 bg-white" x-data="siswaCrud()">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- ===== HEADER + AKSI ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Master Data &mdash; Siswa PKL</h2>
                    <p class="text-sm text-gray-500">Kelola data peserta PKL beserta pemetaan pembimbing &amp; tempat magang.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.siswa.export.excel', ['q' => $q, 'status' => $status]) }}"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-green-50 text-green-700 text-sm font-medium hover:bg-green-100">
                        Excel
                    </a>
                    <a href="{{ route('admin.siswa.export.pdf', ['q' => $q, 'status' => $status]) }}"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100">
                        PDF
                    </a>
                    <button @click="importOpen = true"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium hover:bg-amber-100">
                        Import
                    </button>
                    <a href="{{ route('admin.siswa.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                        Tambah Siswa
                    </a>
                </div>
            </div>

            {{-- ===== KARTU INFORMASI ===== --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Total Siswa</p>
                    <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Sedang Aktif PKL</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">{{ $rekap['aktif'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Belum PKL</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">{{ $rekap['belum'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                    <p class="text-xs font-medium text-gray-500">Selesai PKL</p>
                    <p class="mt-2 text-2xl font-bold text-[#2563EB]">{{ $rekap['selesai'] }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 sm:p-5">
                {{-- ===== SEARCH & FILTER ===== --}}
                <form method="GET" class="mb-4 flex flex-wrap gap-2">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / NISN..."
                        class="w-full sm:w-64 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <select name="status" class="rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                        <option value="">Semua Status</option>
                        <option value="belum" {{ $status === 'belum' ? 'selected' : '' }}>Belum</option>
                        <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    <button class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
                    @if($q || $status)
                        <a href="{{ route('admin.siswa.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
                    @endif
                </form>

                {{-- ============================================================= --}}
                {{-- TABEL DESKTOP / LAPTOP (>= lg): tampilkan SEMUA informasi   --}}
                {{-- ============================================================= --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b border-blue-100">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-6 min-w-[200px]">Siswa</th>
                                <th class="py-3 px-4">NISN</th>
                                <th class="py-3 px-4 min-w-[150px]">Periode</th>
                                <th class="py-3 px-4 min-w-[150px]">Kelas / Jurusan</th>
                                <th class="py-3 px-6 min-w-[180px]">Tempat PKL</th>
                                <th class="py-3 px-6 min-w-[180px]">Guru Pembimbing</th>
                                <th class="py-3 px-6 min-w-[180px]">Instruktur</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-6 text-right w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswa as $s)
                                @php
                                    $badge = [
                                        'belum'   => 'bg-gray-100 text-gray-600',
                                        'aktif'   => 'bg-green-50 text-green-600',
                                        'selesai' => 'bg-blue-50 text-[#2563EB]',
                                    ][$s->status_pkl] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                                    <td class="py-3 px-4 text-center text-gray-500">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="py-3 px-6 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $s->foto ? asset('storage/' . $s->foto) : 'https://ui-avatars.com/api/?background=DBEAFE&color=1E3A8A&name=' . urlencode($s->name) }}"
                                                 alt="foto" class="w-9 h-9 rounded-full object-cover shrink-0">
                                            <div class="font-medium text-gray-800">{{ $s->name }}</div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 font-medium whitespace-nowrap">{{ $s->nisn ?? '-' }}</td>
                                    <td class="py-3 px-4 text-gray-600">
                                        <div class="font-medium">{{ $s->periode->nama ?? '-' }}</div>
                                        @if($s->periode && $s->periode->tahun_ajaran)
                                            <div class="text-xs text-gray-400 mt-0.5">{{ $s->periode->tahun_ajaran }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        <div class="font-medium">{{ $s->kelas ?? '-' }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $s->jurusan ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-6 text-gray-600">{{ $s->perusahaan->nama_perusahaan ?? '-' }}</td>
                                    <td class="py-3 px-6 text-gray-600">{{ $s->guru->name ?? '-' }}</td>
                                    <td class="py-3 px-6 text-gray-600">{{ $s->instruktur->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $badge }}">{{ ucfirst($s->status_pkl) }}</span>
                                    </td>
                                    <td class="py-3 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                            <a href="{{ route('admin.siswa.edit', $s) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100 font-medium">Edit</a>
                                            <button type="button"
                                                    @click="konfirmHapus(@js(route('admin.siswa.destroy', $s)), @js($s->name))"
                                                    class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-medium">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="py-8 text-center text-gray-400">Belum ada data siswa.</td></tr>
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
                                <th class="px-3 py-3">Siswa</th>
                                <th class="px-3 py-3 text-center w-28">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($siswa as $s)
                                @php
                                    $badge = [
                                        'belum'   => 'bg-gray-100 text-gray-600',
                                        'aktif'   => 'bg-green-50 text-green-600',
                                        'selesai' => 'bg-blue-50 text-[#2563EB]',
                                    ][$s->status_pkl] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-3 py-4 text-center text-gray-500">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $s->foto ? asset('storage/' . $s->foto) : 'https://ui-avatars.com/api/?background=DBEAFE&color=1E3A8A&name=' . urlencode($s->name) }}"
                                                 alt="foto" class="w-9 h-9 rounded-full object-cover shrink-0">
                                            <div>
                                                <div class="font-medium text-gray-800 leading-snug break-words">{{ $s->name }}</div>
                                                <span class="mt-1 inline-block text-[10px] px-2 py-0.5 rounded-full font-medium {{ $badge }}">{{ ucfirst($s->status_pkl) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <button type="button"
                                                @click="lihatDetail(@js([
                                                    'foto_url' => $s->foto ? asset('storage/' . $s->foto) : 'https://ui-avatars.com/api/?background=DBEAFE&color=1E3A8A&name=' . urlencode($s->name),
                                                    'nama' => $s->name,
                                                    'nisn' => $s->nisn ?? '-',
                                                    'periode' => $s->periode->nama ?? '-',
                                                    'tahun_ajaran' => $s->periode->tahun_ajaran ?? null,
                                                    'kelas' => $s->kelas ?? '-',
                                                    'jurusan' => $s->jurusan ?? '-',
                                                    'tempat_pkl' => $s->perusahaan->nama_perusahaan ?? '-',
                                                    'guru' => $s->guru->name ?? '-',
                                                    'instruktur' => $s->instruktur->name ?? '-',
                                                    'status' => ucfirst($s->status_pkl),
                                                    'status_key' => $s->status_pkl,
                                                    'edit_url' => route('admin.siswa.edit', $s),
                                                    'destroy_url' => route('admin.siswa.destroy', $s),
                                                ]))"
                                                class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white transition active:scale-95 hover:bg-blue-700">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-gray-400">Belum ada data siswa.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {!! $siswa->links() !!}
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
                    <div class="flex items-center gap-3">
                        <img :src="detailData.foto_url" alt="foto" class="w-11 h-11 rounded-full object-cover shrink-0">
                        <div>
                            <h3 class="text-base font-bold text-gray-800" x-text="detailData.nama"></h3>
                            <p class="text-xs font-mono text-gray-400">NISN: <span x-text="detailData.nisn"></span></p>
                        </div>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Status PKL</p>
                        <span class="mt-1 inline-block text-xs px-2.5 py-1 rounded-full font-medium"
                              :class="detailData.status_key === 'aktif' ? 'bg-green-50 text-green-600' : (detailData.status_key === 'selesai' ? 'bg-blue-50 text-[#2563EB]' : 'bg-gray-100 text-gray-600')"
                              x-text="detailData.status"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Periode</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.periode"></p>
                            <p class="text-xs text-gray-400" x-text="detailData.tahun_ajaran"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Kelas / Jurusan</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.kelas"></p>
                            <p class="text-xs text-gray-400" x-text="detailData.jurusan"></p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Tempat PKL</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.tempat_pkl"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Guru Pembimbing</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.guru"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Instruktur</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.instruktur"></p>
                        </div>
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
                <h3 class="text-lg font-bold text-gray-800 mb-1">Import Data Siswa</h3>
                <p class="text-sm text-gray-500 mb-4">Unggah file Excel (.xlsx/.csv) sesuai template. Kolom <b>tempat_pkl</b> &amp; <b>pembimbing</b> harus cocok dengan data yang sudah terdaftar.</p>
                <form method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                        class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 mb-4">
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('admin.siswa.template') }}" class="text-sm text-[#2563EB] hover:underline">Unduh Template</a>
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
                <h3 class="text-lg font-bold text-gray-800">Hapus Data Siswa</h3>
                <p class="text-sm text-gray-600 mt-2">
                    Yakin ingin menghapus data <span class="font-semibold" x-text="hapusLabel"></span>? Semua data terkait siswa ini akan ikut terhapus. Tindakan ini tidak bisa dibatalkan.
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
        window.siswaCrud = function () {
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
                    this.hapusLabel = label || 'siswa ini';
                    this.hapusOpen  = true;
                },
            };
        };
    </script>
</x-app-layout>

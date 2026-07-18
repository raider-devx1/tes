<x-app-layout title="Dokumen Siswa">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-6 sm:py-8 md:py-10 bg-white" x-data="dokumenCrud()">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10 space-y-6">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Dokumen Siswa PKL</h2>
                    <p class="text-sm text-gray-500">Kelola, unggah, lihat &amp; unduh dokumen siswa. Surat Tugas dikelola global.</p>
                </div>
                <button type="button" onclick="history.back()"
                        class="inline-flex items-center gap-1 rounded-xl border border-[#2563EB]/25 bg-white px-4 py-2 text-sm font-semibold text-[#2563EB] transition hover:bg-[#2563EB]/5 shrink-0 self-start">Kembali</button>
            </div>

            @if (session('success'))
                <div class="rounded-lg bg-green-50 border border-green-100 text-green-700 text-sm px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            {{-- REKAP --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="bg-white rounded-xl border border-blue-100 p-4">
                    <p class="text-xs text-gray-500">Total Siswa</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $rekap['totalSiswa'] }}</p>
                </div>
                <div class="bg-white rounded-xl border border-blue-100 p-4">
                    <p class="text-xs text-gray-500">Laporan Akhir</p>
                    <p class="text-2xl font-bold text-[#2563EB]">{{ $rekap['laporan'] }}</p>
                </div>
                <div class="bg-white rounded-xl border border-blue-100 p-4">
                    <p class="text-xs text-gray-500">Surat Penerimaan</p>
                    <p class="text-2xl font-bold text-[#2563EB]">{{ $rekap['suratPenerimaan'] }}</p>
                </div>
                <div class="bg-white rounded-xl border border-blue-100 p-4">
                    <p class="text-xs text-gray-500">Lengkap</p>
                    <p class="text-2xl font-bold text-green-600">{{ $rekap['lengkap'] }}</p>
                </div>
                <div class="bg-white rounded-xl border border-blue-100 p-4">
                    <p class="text-xs text-gray-500">Surat Tugas (Global)</p>
                    <p class="text-lg font-bold text-gray-700">{{ $rekap['suratTugas'] }}</p>
                    <a href="{{ route('admin.dokumen.surat-tugas.index') }}" class="text-[11px] text-[#2563EB] hover:underline">Kelola &rarr;</a>
                </div>
            </div>

            {{-- FILTER --}}
            <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Nama / NISN"
                           class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                    <select name="kelas" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        <option value="">Semua</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k }}" @selected($kelas === $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jurusan</label>
                    <select name="jurusan" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        <option value="">Semua</option>
                        @foreach ($jurusanList as $j)
                            <option value="{{ $j }}" @selected($jurusan === $j)>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        <option value="">Semua</option>
                        <option value="lengkap" @selected($status === 'lengkap')>Lengkap</option>
                        <option value="sebagian" @selected($status === 'sebagian')>Sebagian</option>
                        <option value="belum" @selected($status === 'belum')>Belum</option>
                    </select>
                </div>
                <div class="md:col-span-5 flex gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700 transition">Filter</button>
                    <a href="{{ route('admin.dokumen.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition inline-block text-center">Reset</a>
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
                                <th class="px-4 py-3">Siswa</th>
                                <th class="px-4 py-3">Kelas</th>
                                <th class="px-4 py-3">Jurusan</th>
                                <th class="px-4 py-3 text-center">Laporan Akhir</th>
                                <th class="px-4 py-3 text-center">Surat Penerimaan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($siswa as $s)
                                @php
                                    $d   = $s->dokumen;
                                    $ada = collect([$d?->laporan_akhir, $d?->surat_penerimaan])->filter()->count();
                                    [$stLabel, $stClass] = $ada === 2
                                        ? ['Lengkap', 'bg-green-50 text-green-700']
                                        : ($ada === 0 ? ['Belum', 'bg-red-50 text-red-600'] : ['Sebagian', 'bg-amber-50 text-amber-700']);
                                @endphp
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-4 py-3 text-center text-gray-500">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">
                                        {{ $s->name }}
                                        <div class="text-xs text-gray-400">NISN: {{ $s->nisn ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $s->kelas ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $s->jurusan ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($d?->laporan_akhir)
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                <a href="{{ route('dokumen.lihat', [$s->id, 'laporan_akhir']) }}" target="_blank"
                                                   class="px-2.5 py-1 rounded-md bg-blue-50 text-[#2563EB] text-xs font-medium hover:bg-blue-100 transition">Lihat PDF</a>
                                                <a href="{{ route('dokumen.download', [$s->id, 'laporan_akhir']) }}"
                                                   class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">Download</a>
                                                <button type="button"
                                                    @click="konfirmHapus(@js($s->id), @js('laporan_akhir'), @js('Laporan Akhir — ' . $s->name))"
                                                    class="px-2.5 py-1 rounded-md bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">Hapus</button>
                                            </div>
                                        @else
                                            <span class="text-gray-300">&ndash;</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($d?->surat_penerimaan)
                                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                                <a href="{{ route('dokumen.lihat', [$s->id, 'surat_penerimaan']) }}" target="_blank"
                                                   class="px-2.5 py-1 rounded-md bg-blue-50 text-[#2563EB] text-xs font-medium hover:bg-blue-100 transition">Lihat PDF</a>
                                                <a href="{{ route('dokumen.download', [$s->id, 'surat_penerimaan']) }}"
                                                   class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">Download</a>
                                                <button type="button"
                                                    @click="konfirmHapus(@js($s->id), @js('surat_penerimaan'), @js('Surat Penerimaan — ' . $s->name))"
                                                    class="px-2.5 py-1 rounded-md bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">Hapus</button>
                                            </div>
                                        @else
                                            <span class="text-gray-300">&ndash;</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $stClass }}">{{ $stLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button"
                                            @click="kelola(@js(['id' => $s->id, 'nama' => $s->name, 'punyaLaporan' => (bool) $d?->laporan_akhir, 'punyaSurat' => (bool) $d?->surat_penerimaan]))"
                                            class="px-3 py-1.5 rounded-lg bg-[#2563EB] text-white text-xs font-medium hover:bg-blue-700 transition">
                                            Kelola
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400 italic">Tidak ada data siswa.</td>
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
                            <th class="px-3 py-3">Siswa</th>
                            <th class="px-3 py-3 text-center w-28">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($siswa as $s)
                            @php
                                $d   = $s->dokumen;
                                $ada = collect([$d?->laporan_akhir, $d?->surat_penerimaan])->filter()->count();
                                [$stLabel, $stClass] = $ada === 2
                                    ? ['Lengkap', 'bg-green-50 text-green-700']
                                    : ($ada === 0 ? ['Belum', 'bg-red-50 text-red-600'] : ['Sebagian', 'bg-amber-50 text-amber-700']);
                            @endphp
                            <tr class="hover:bg-blue-50/40 transition">
                                <td class="px-3 py-4 text-center text-gray-500">{{ $siswa->firstItem() + $loop->index }}</td>
                                <td class="px-3 py-4">
                                    <div class="font-medium text-gray-800 leading-snug break-words">{{ $s->name }}</div>
                                    <div class="text-[11px] text-gray-400 mt-0.5 font-mono">NISN: {{ $s->nisn ?? '-' }}</div>
                                    <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-[10px] font-medium {{ $stClass }}">{{ $stLabel }}</span>
                                </td>
                                <td class="px-3 py-4 text-center">
                                    <button type="button"
                                        @click="lihatDetail(@js([
                                            'id' => $s->id,
                                            'nama' => $s->name,
                                            'nisn' => $s->nisn ?? '-',
                                            'kelas' => $s->kelas ?? '-',
                                            'jurusan' => $s->jurusan ?? '-',
                                            'status_label' => $stLabel,
                                            'punyaLaporan' => (bool) $d?->laporan_akhir,
                                            'punyaSurat' => (bool) $d?->surat_penerimaan,
                                            'laporan_lihat_url' => $d?->laporan_akhir ? route('dokumen.lihat', [$s->id, 'laporan_akhir']) : null,
                                            'laporan_download_url' => $d?->laporan_akhir ? route('dokumen.download', [$s->id, 'laporan_akhir']) : null,
                                            'surat_lihat_url' => $d?->surat_penerimaan ? route('dokumen.lihat', [$s->id, 'surat_penerimaan']) : null,
                                            'surat_download_url' => $d?->surat_penerimaan ? route('dokumen.download', [$s->id, 'surat_penerimaan']) : null,
                                        ]))"
                                        class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white transition active:scale-95 hover:bg-blue-700">
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-400 italic">Tidak ada data siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {!! $siswa->links() !!}
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
                        <p class="text-xs font-mono text-gray-400">NISN: <span x-text="detailData.nisn"></span></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Kelas</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.kelas"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Jurusan</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.jurusan"></p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Status Dokumen</p>
                            <span class="mt-1 inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                                  :class="detailData.status_label === 'Lengkap' ? 'bg-green-50 text-green-700' : (detailData.status_label === 'Belum' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700')"
                                  x-text="detailData.status_label"></span>
                        </div>
                    </div>

                    {{-- LAPORAN AKHIR --}}
                    <div class="rounded-xl border border-blue-100 p-3">
                        <p class="text-xs font-bold text-gray-700 mb-2">Laporan Akhir</p>
                        <template x-if="detailData.punyaLaporan">
                            <div class="flex flex-wrap gap-2">
                                <a :href="detailData.laporan_lihat_url" target="_blank"
                                   class="px-3 py-1.5 rounded-md bg-blue-50 text-[#2563EB] text-xs font-medium hover:bg-blue-100 transition">Lihat PDF</a>
                                <a :href="detailData.laporan_download_url"
                                   class="px-3 py-1.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">Download</a>
                                <button type="button" @click="detailOpen = false; konfirmHapus(detailData.id, 'laporan_akhir', 'Laporan Akhir — ' + detailData.nama)"
                                        class="px-3 py-1.5 rounded-md bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">Hapus</button>
                            </div>
                        </template>
                        <template x-if="!detailData.punyaLaporan">
                            <p class="text-xs italic text-gray-400">Belum ada file.</p>
                        </template>
                    </div>

                    {{-- SURAT PENERIMAAN --}}
                    <div class="rounded-xl border border-blue-100 p-3">
                        <p class="text-xs font-bold text-gray-700 mb-2">Surat Penerimaan</p>
                        <template x-if="detailData.punyaSurat">
                            <div class="flex flex-wrap gap-2">
                                <a :href="detailData.surat_lihat_url" target="_blank"
                                   class="px-3 py-1.5 rounded-md bg-blue-50 text-[#2563EB] text-xs font-medium hover:bg-blue-100 transition">Lihat PDF</a>
                                <a :href="detailData.surat_download_url"
                                   class="px-3 py-1.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">Download</a>
                                <button type="button" @click="detailOpen = false; konfirmHapus(detailData.id, 'surat_penerimaan', 'Surat Penerimaan — ' + detailData.nama)"
                                        class="px-3 py-1.5 rounded-md bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">Hapus</button>
                            </div>
                        </template>
                        <template x-if="!detailData.punyaSurat">
                            <p class="text-xs italic text-gray-400">Belum ada file.</p>
                        </template>
                    </div>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 flex gap-2 border-t border-blue-100 bg-white px-5 py-4">
                    <button type="button" @click="kelolaDariDetail()"
                            class="flex-1 rounded-xl bg-[#2563EB] px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Kelola / Unggah</button>
                    <button type="button" @click="detailOpen = false"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 transition hover:bg-gray-50">Tutup</button>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL KELOLA / UPLOAD                                           --}}
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
                <h3 class="text-lg font-bold text-gray-800">Kelola Dokumen</h3>
                <p class="text-sm text-gray-500 mb-4" x-text="'Siswa: ' + siswaNama"></p>
                <form method="POST" :action="actionUrl" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Laporan Akhir (PDF, maks 5MB)</label>
                        <input type="file" name="laporan_akhir" accept="application/pdf"
                               class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-[#2563EB] file:font-medium hover:file:bg-blue-100">
                        <p class="text-xs text-amber-600 mt-1" x-show="punyaLaporan">Sudah ada file — unggah baru untuk mengganti.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Surat Penerimaan (PDF, maks 2MB)</label>
                        <input type="file" name="surat_penerimaan" accept="application/pdf"
                               class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-[#2563EB] file:font-medium hover:file:bg-blue-100">
                        <p class="text-xs text-amber-600 mt-1" x-show="punyaSurat">Sudah ada file — unggah baru untuk mengganti.</p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition">Batal</button>
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700 transition">Simpan</button>
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
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="hapusOpen = false">
                <h3 class="text-lg font-bold text-gray-800">Hapus Dokumen</h3>
                <p class="text-sm text-gray-600 mt-2" x-text="'Yakin ingin menghapus ' + hapusLabel + '? Tindakan ini tidak bisa dibatalkan.'"></p>
                <form method="POST" :action="hapusUrl" class="flex justify-end gap-2 pt-5">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false"
                            class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50 transition">Batal</button>
                    <button type="submit"
                            class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.dokumenCrud = function () {
            const storeTemplate = @js(route('admin.dokumen.store', ['siswa' => '__ID__']));
            const hapusTemplate = @js(route('admin.dokumen.destroy', ['siswa' => '__ID__', 'jenis' => '__JENIS__']));

            return {
                // modal upload
                open: false,
                siswaId: null,
                siswaNama: '',
                punyaLaporan: false,
                punyaSurat: false,
                // modal hapus
                hapusOpen: false,
                hapusUrl: '',
                hapusLabel: '',
                // modal detail (mobile)
                detailOpen: false,
                detailData: {},

                init() {
                    this.$watch('open',       () => this.kunciScroll());
                    this.$watch('hapusOpen',  () => this.kunciScroll());
                    this.$watch('detailOpen', () => this.kunciScroll());
                },
                kunciScroll() {
                    document.body.style.overflow = (this.open || this.hapusOpen || this.detailOpen) ? 'hidden' : '';
                },

                get actionUrl() {
                    return storeTemplate.replace('__ID__', this.siswaId);
                },

                kelola(data) {
                    this.siswaId      = data.id;
                    this.siswaNama    = data.nama;
                    this.punyaLaporan = data.punyaLaporan;
                    this.punyaSurat   = data.punyaSurat;
                    this.open         = true;
                },

                // buka detail (mobile)
                lihatDetail(d) { this.detailData = d; this.detailOpen = true; },

                // dari modal detail -> buka modal kelola/upload
                kelolaDariDetail() {
                    const d = this.detailData;
                    this.detailOpen = false;
                    this.kelola({
                        id: d.id,
                        nama: d.nama,
                        punyaLaporan: d.punyaLaporan,
                        punyaSurat: d.punyaSurat,
                    });
                },

                konfirmHapus(id, jenis, label) {
                    this.hapusUrl   = hapusTemplate.replace('__ID__', id).replace('__JENIS__', jenis);
                    this.hapusLabel = label;
                    this.hapusOpen  = true;
                },
            };
        };
    </script>
</x-app-layout>

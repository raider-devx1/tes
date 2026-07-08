<x-app-layout title="Dokumen Siswa">
    <div class="max-w-7xl mx-auto space-y-6" x-data="dokumenCrud()">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dokumen Siswa PKL</h2>
            <p class="text-sm text-gray-500">Kelola, unggah, lihat & unduh dokumen siswa. Surat Tugas dikelola global.</p>
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-green-50 border border-green-100 text-green-700 text-sm px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

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
                <a href="{{ route('admin.dokumen.surat-tugas.index') }}" class="text-[11px] text-[#2563EB] hover:underline">Kelola →</a>
            </div>
        </div>

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

        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[1000px]">
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
                                        <span class="text-gray-300">–</span>
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
                                        <span class="text-gray-300">–</span>
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

        <div>
            {!! $siswa->links() !!}
        </div>

        <div x-show="open" x-cloak @keydown.escape.window="open = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="open = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6" @click.stop>
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

        <div x-show="hapusOpen" x-cloak @keydown.escape.window="hapusOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="hapusOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
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

    <style>[x-cloak]{display:none!important;}</style>

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

                konfirmHapus(id, jenis, label) {
                    this.hapusUrl   = hapusTemplate.replace('__ID__', id).replace('__JENIS__', jenis);
                    this.hapusLabel = label;
                    this.hapusOpen  = true;
                },
            };
        };
    </script>
</x-app-layout>
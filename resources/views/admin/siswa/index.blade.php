<x-app-layout title="Data Siswa PKL">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Master Data — Siswa PKL</h2>
            <p class="text-sm text-gray-500">Kelola data peserta PKL beserta pemetaan pembimbing &amp; tempat magang.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2" x-data="{ importOpen: false }">
            <a href="{{ route('admin.siswa.export.excel', ['q' => $q, 'status' => $status]) }}"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-green-50 text-green-700 text-sm font-medium hover:bg-green-100">
                ⬇ Excel
            </a>
            <a href="{{ route('admin.siswa.export.pdf', ['q' => $q, 'status' => $status]) }}"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100">
                ⬇ PDF
            </a>
            <button @click="importOpen = true"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium hover:bg-amber-100">
                ⬆ Import
            </button>
            <a href="{{ route('admin.siswa.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                 Tambah Siswa
            </a>

            <!-- ===== MODAL IMPORT DATA ===== -->
            <div x-show="importOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="importOpen = false">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Import Data Siswa</h3>
                    <p class="text-sm text-gray-500 mb-4">Unggah file Excel (.xlsx/.csv) sesuai template. Kolom <b>tempat_pkl</b> &amp; <b>pembimbing</b> harus cocok dengan data yang sudah terdaftar.</p>

                    <form method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 mb-4">
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('admin.siswa.template') }}" class="text-sm text-[#2563EB] hover:underline">⬇ Unduh Template</a>
                            <div class="flex gap-2">
                                <button type="button" @click="importOpen = false" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Batal</button>
                                <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Import</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== KARTU INFORMASI ===== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Total Siswa</p>
              
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Sedang Aktif PKL</p>
               
            </div>
            <p class="mt-2 text-2xl font-bold text-green-600">{{ $rekap['aktif'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Belum PKL</p>
              
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $rekap['belum'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Selesai PKL</p>
               
            </div>
            <p class="mt-2 text-2xl font-bold text-[#2563EB]">{{ $rekap['selesai'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">

        <!-- ===== SEARCH & FILTER ===== -->
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

       <!-- ===== TABEL DATA ===== -->
<div class="overflow-x-auto">
    <table class="w-full text-sm min-w-[1000px]"> {{-- Menambahkan min-w agar tabel punya ruang saat responsif --}}
        <thead>
            <tr class="text-left text-gray-500 border-b border-blue-100">
                <th class="py-3 px-4 w-12 text-center">No</th> {{-- Mengubah px-3 menjadi px-4 --}}
                <th class="py-3 px-6 min-w-[200px]">Siswa</th> {{-- Memberi ruang minimal untuk nama --}}
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
                <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                    <td class="py-3 px-4 text-center text-gray-500">
                        {{ $siswa->firstItem() + $loop->index }}
                    </td>
                    <td class="py-3 px-6 whitespace-nowrap"> {{-- Mencegah teks nama terpotong/pindah baris --}}
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
                        @php
                            $badge = [
                                'belum'   => 'bg-gray-100 text-gray-600',
                                'aktif'   => 'bg-green-50 text-green-600',
                                'selesai' => 'bg-blue-50 text-[#2563EB]',
                            ][$s->status_pkl] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $badge }}">{{ ucfirst($s->status_pkl) }}</span>
                    </td>
                    <td class="py-3 px-6 text-right">
                        <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                            <a href="{{ route('admin.siswa.edit', $s) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100 font-medium">Edit</a>
                            <form method="POST" action="{{ route('admin.siswa.destroy', $s) }}"
                                  data-confirm="Hapus data siswa ini?"
                                  data-confirm-text="Semua data terkait siswa ini akan ikut terhapus."
                                  data-confirm-yes="Ya, hapus">
                                @csrf
                                @method('DELETE')
                                <button class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-medium">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="py-8 text-center text-gray-400">Belum ada data siswa.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

        <div class="mt-4">
            {!! $siswa->links() !!}
        </div>
    </div>

</x-app-layout>
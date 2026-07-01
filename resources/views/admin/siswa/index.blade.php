<x-app-layout title="Data Siswa PKL">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Master Data — Siswa PKL</h2>
            <p class="text-sm text-gray-500">Kelola data peserta PKL beserta pemetaan pembimbing & tempat magang.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2" x-data="{ importOpen: false }">
            <a href="{{ route('admin.siswa.export.excel', request()->only('q', 'status')) }}"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-green-50 text-green-700 text-sm font-medium hover:bg-green-100">
                ⬇ Excel
            </a>
            <a href="{{ route('admin.siswa.export.pdf', request()->only('q', 'status')) }}"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100">
                ⬇ PDF
            </a>
            <button @click="importOpen = true"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium hover:bg-amber-100">
                ⬆ Import
            </button>
            <a href="{{ route('admin.siswa.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                + Tambah Siswa
            </a>

            <div x-show="importOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="importOpen = false">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Import Data Siswa</h3>
                    <p class="text-sm text-gray-500 mb-4">Unggah file Excel (.xlsx/.csv) sesuai template. Kolom <b>tempat_pkl</b> & <b>pembimbing</b> harus cocok dengan data yang sudah terdaftar.</p>

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

    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">

        <form method="GET" class="mb-4 flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / NISN / email..."
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

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-blue-100">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Siswa</th>
                        <th class="py-3 px-3">Kelas / Jurusan</th>
                        <th class="py-3 px-3">Tempat PKL</th>
                        <th class="py-3 px-3">Guru Pembimbing</th>
                        <th class="py-3 px-3">Instruktur</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswa as $s)
                        <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                            <td class="py-3 px-3 text-center text-gray-500">
    {{ $siswa->firstItem() + $loop->index }}
</td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $s->foto ? asset('storage/' . $s->foto) : 'https://ui-avatars.com/api/?background=DBEAFE&color=1E3A8A&name=' . urlencode($s->name) }}"
                                         alt="foto" class="w-9 h-9 rounded-full object-cover">
                                    <div>
                                        <div class="font-medium text-gray-800">{{ $s->name }}</div>
                                        <div class="text-xs text-gray-400">NISN: {{ $s->nisn ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-gray-600">
                                <div>{{ $s->kelas ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $s->jurusan ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-3 text-gray-600">{{ $s->perusahaan->nama_perusahaan ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $s->guru->name ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $s->instruktur->name ?? '-' }}</td>
                            <td class="py-3 px-3 text-center">
                                @php
                                    $badge = [
                                        'belum'   => 'bg-gray-100 text-gray-600',
                                        'aktif'   => 'bg-green-50 text-green-600',
                                        'selesai' => 'bg-blue-50 text-[#2563EB]',
                                    ][$s->status_pkl] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="text-xs px-2 py-1 rounded-full {{ $badge }}">{{ ucfirst($s->status_pkl) }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.siswa.edit', $s) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.siswa.destroy', $s) }}" onsubmit="return confirm('Hapus data siswa ini?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-gray-400">Belum ada data siswa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {!! $siswa->links() !!}
        </div>
    </div>

</x-app-layout>
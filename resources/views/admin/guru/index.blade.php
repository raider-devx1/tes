<x-app-layout title="Akun Guru Pembimbing">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Master Data — Guru Pembimbing</h2>
            <p class="text-sm text-gray-500">Kelola akun guru pembimbing PKL.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2" x-data="{ importOpen: false }">
            <a href="{{ route('admin.guru.export.excel', ['q' => $q]) }}"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-green-50 text-green-700 text-sm font-medium hover:bg-green-100">
                ⬇ Excel
            </a>
            <a href="{{ route('admin.guru.export.pdf', ['q' => $q]) }}"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100">
                ⬇ PDF
            </a>
            <button @click="importOpen = true"
                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium hover:bg-amber-100">
                ⬆ Import
            </button>
            <a href="{{ route('admin.guru.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                 Tambah Guru
            </a>

            <!-- ===== MODAL IMPORT DATA ===== -->
            <div x-show="importOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="importOpen = false">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Import Data Guru</h3>
                    <p class="text-sm text-gray-500 mb-4">Unggah file Excel (.xlsx/.csv) sesuai template. NIP tidak boleh sama dengan yang sudah terdaftar.</p>

                    <form method="POST" action="{{ route('admin.guru.import') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 mb-4">
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('admin.guru.template') }}" class="text-sm text-[#2563EB] hover:underline">⬇ Unduh Template</a>
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
                <p class="text-xs font-medium text-gray-500">Total Guru</p>
               
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Punya Bimbingan</p>
             
            </div>
            <p class="mt-2 text-2xl font-bold text-green-600">{{ $rekap['ada_bimbingan'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Tanpa Bimbingan</p>
              
            </div>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $rekap['tanpa_bimbingan'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Siswa Dibimbing</p>
               
            </div>
            <p class="mt-2 text-2xl font-bold text-[#2563EB]">{{ $rekap['siswa_dibimbing'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">

        <!-- ===== SEARCH FILTER ===== -->
        <form method="GET" class="mb-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / NIP..."
                   class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
            @if($q)
                <a href="{{ route('admin.guru.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            @endif
        </form>

        <!-- ===== TABLE DATA ===== -->
        <div class="overflow-x-auto">
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
                            <td class="py-3 px-3 text-center text-gray-500">
                                {{ $guru->firstItem() + $loop->index }}
                            </td>
                            <td class="py-3 px-3 font-medium text-gray-800">{{ $g->name }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $g->nip }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $g->no_hp ?? '-' }}</td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.guru.edit', $g) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.guru.destroy', $g) }}"
                                          data-confirm="Hapus akun guru ini?"
                                          data-confirm-yes="Ya, hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada akun guru pembimbing.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ===== PAGINATION ===== -->
        <div class="mt-4">
            {!! $guru->links() !!}
        </div>
    </div>

</x-app-layout>
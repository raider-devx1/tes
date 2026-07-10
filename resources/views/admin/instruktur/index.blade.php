<x-app-layout title="Akun Instruktur Industri">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Master Data — Instruktur &amp; Industri</h2>
            <p class="text-sm text-gray-500">Kelola akun instruktur sekaligus data industri tempatnya bertugas.</p>
        </div>
        <a href="{{ route('admin.instruktur.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
             Tambah Instruktur
        </a>
    </div>

    <!-- ===== KARTU INFORMASI ===== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Total Instruktur</p>
             
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['total'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Total Industri</p>
              
            </div>
            <p class="mt-2 text-2xl font-bold text-[#2563EB]">{{ $rekap['industri'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Punya Siswa</p>
              
            </div>
            <p class="mt-2 text-2xl font-bold text-green-600">{{ $rekap['ada_siswa'] }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-gray-500">Siswa Dibimbing</p>
               
            </div>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $rekap['siswa_industri'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">

        <!-- ===== SEARCH FILTER ===== -->
        <form method="GET" class="mb-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / email / jabatan..."
                   class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
            @if($q)
                <a href="{{ route('admin.instruktur.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            @endif
        </form>

        <!-- ===== TABEL DATA ===== -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-blue-100">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Nama</th>
                        <th class="py-3 px-3">Email</th>
                        <th class="py-3 px-3">Jabatan</th>
                        <th class="py-3 px-3">Industri</th>
                        <th class="py-3 px-3">No. HP</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($instruktur as $it)
                        <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                            <td class="py-3 px-3 text-center text-gray-500">
                                 {{ $instruktur->firstItem() + $loop->index }} 
                            </td>
                            <td class="py-3 px-3 font-medium text-gray-800">{{ $it->name }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $it->email }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $it->jabatan ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $it->perusahaan->nama_perusahaan ?? '-' }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $it->no_hp ?? '-' }}</td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.instruktur.edit', $it) }}" class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100">Edit</a>
                                    <form method="POST" action="{{ route('admin.instruktur.destroy', $it) }}"
                                          data-confirm="Hapus akun instruktur ini?"
                                          data-confirm-text="Data industri terkait ikut dibersihkan bila tak lagi terkapai."
                                          data-confirm-yes="Ya, hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400">Belum ada akun instruktur industri.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ===== PAGINATION ===== -->
        <div class="mt-4">
            {!! $instruktur->links() !!}
        </div>
    </div>

</x-app-layout>
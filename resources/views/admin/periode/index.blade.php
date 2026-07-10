<x-app-layout title="Periode PKL">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Master Data — Periode PKL</h2>
            <p class="text-sm text-gray-500">Kelola gelombang/periode pelaksanaan PKL.</p>
        </div>
        <a href="{{ route('admin.periode.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
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
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5 mb-6">
        <h3 class="text-base font-semibold text-gray-800 mb-1">Atur Status Siswa per Periode</h3>
        <p class="text-sm text-gray-500 mb-4">
            Pilih periode, lalu ubah status PKL <strong>seluruh siswa</strong> pada periode tersebut sekaligus
            (belum / aktif / selesai). Berguna, misalnya, untuk menandai semua siswa periode lama menjadi "selesai".
        </p>

       <form method="POST" action="{{ route('admin.periode.update-status-siswa') }}"
      data-confirm="Ubah status SEMUA siswa pada periode ini?"
      data-confirm-text="Tindakan ini berlaku untuk seluruh siswa pada periode yang dipilih."
      data-confirm-icon="question"
      data-confirm-yes="Ya, terapkan">
            @csrf
            <div class="flex flex-col md:flex-row gap-3 md:items-end">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Periode PKL</label>
                    <select name="periode_id" required
                            class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                        <option value="">-- Pilih Periode --</option>
                        @foreach($semuaPeriode as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->nama }} — {{ $item->tahun_ajaran }} {{ $item->is_active ? '(Aktif)' : '' }}
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
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">

        <form method="GET" class="mb-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / tahun ajaran..."
                   class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            <button class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
            @if($q)
                <a href="{{ route('admin.periode.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            @endif
        </form>

        <div class="overflow-x-auto">
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
                            <td class="py-3 px-3 text-center text-gray-500">
                                {{ $periode->firstItem() + $loop->index }}
                            </td>
                            <td class="py-3 px-3 font-medium text-gray-800">{{ $p->nama }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $p->tahun_ajaran }}</td>
                            <td class="py-3 px-3 text-gray-600">
                                {{ \Carbon\Carbon::parse($p->tanggal_mulai)->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3 px-3 text-gray-600">
                                {{ \Carbon\Carbon::parse($p->tanggal_selesai)->translatedFormat('d M Y') }}
                            </td>
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
                                    <form method="POST" action="{{ route('admin.periode.destroy', $p) }}"
      data-confirm="Hapus periode ini?"
      data-confirm-yes="Ya, hapus">
    @csrf
    @method('DELETE')
                                        <button class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Hapus</button>
                                    </form>
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

        <div class="mt-4">
             {!! $periode->links() !!}
        </div>
    </div>

</x-app-layout>
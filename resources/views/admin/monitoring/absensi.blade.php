<x-app-layout title="Monitoring Absensi">
    <style>[x-cloak]{display:none!important;}</style>

    <div x-data="absensiCrud()" class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10 py-6 sm:py-8 space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Monitoring Absensi Siswa</h2>
                <p class="text-sm text-gray-500">Kelola kehadiran &amp; validasi siswa PKL (tambah, ubah, hapus, ubah status, cetak).</p>
            </div>
            <button type="button" @click="tambah()"
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Tambah Absensi</button>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- STATISTIK REKAP --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Hadir</p><p class="text-2xl font-bold text-green-600">{{ $rekap['Hadir'] }}</p></div>
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Izin</p><p class="text-2xl font-bold text-blue-600">{{ $rekap['Izin'] }}</p></div>
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Sakit</p><p class="text-2xl font-bold text-amber-500">{{ $rekap['Sakit'] }}</p></div>
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Alpha</p><p class="text-2xl font-bold text-red-500">{{ $rekap['Alpha'] }}</p></div>
        </div>

        {{-- FILTER --}}
        <form method="GET" class="bg-white rounded-xl border border-blue-100 p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs text-gray-500 mb-1">Cari siswa</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama / NISN"
                       class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                <select name="kelas" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    @foreach($kelasList as $k)<option value="{{ $k }}" @selected(request('kelas') === $k)>{{ $k }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Jurusan</label>
                <select name="jurusan" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    @foreach($jurusanList as $jr)<option value="{{ $jr }}" @selected(request('jurusan') === $jr)>{{ $jr }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    <option value="">Semua</option>
                    <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                    <option value="Izin"  @selected(request('status') === 'Izin')>Izin</option>
                    <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                    <option value="Alpha" @selected(request('status') === 'Alpha')>Alpha</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                <input type="month" name="bulan" value="{{ request('bulan') }}"
                       class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                       class="rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">Filter</button>
            <a href="{{ route('admin.monitoring.absensi') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
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
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Jurusan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Jam Masuk</th>
                            <th class="px-4 py-3 text-center">Jam Pulang</th>
                            <th class="px-4 py-3 text-center">Validasi</th>
                            <th class="px-4 py-3 text-center w-20">Cetak</th>
                            <th class="px-4 py-3 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($absensi as $a)
                            @php
                                $badge = match($a->status) {
                                    'Hadir' => 'bg-green-50 text-green-700',
                                    'Izin'  => 'bg-blue-50 text-blue-700',
                                    'Sakit' => 'bg-amber-50 text-amber-700',
                                    'Alpha' => 'bg-red-50 text-red-600',
                                    default => 'bg-gray-50 text-gray-600',
                                };
                                $jamMasuk  = $a->jam_masuk  ? \Illuminate\Support\Str::substr($a->jam_masuk, 0, 5)  : '';
                                $jamPulang = $a->jam_pulang ? \Illuminate\Support\Str::substr($a->jam_pulang, 0, 5) : '';
                                $sv = $a->status_validasi ?? 'draft';
                                $svBadge = match($sv) {
                                    'disetujui' => 'bg-green-50 text-green-700',
                                    'diajukan'  => 'bg-amber-50 text-amber-700',
                                    default     => 'bg-gray-100 text-gray-500',
                                };
                                $svLabel = match($sv) {
                                    'disetujui' => 'Tervalidasi',
                                    'diajukan'  => 'Menunggu',
                                    default     => 'Draft',
                                };
                                $bulanRow = \Illuminate\Support\Carbon::parse($a->tanggal)->format('Y-m');
                            @endphp
                            <tr class="hover:bg-blue-50/40 align-top">
                                <td class="px-4 py-3 text-center text-gray-500">{{ $absensi->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $a->tanggal->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $a->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $a->siswa->kelas ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $a->siswa->jurusan ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $a->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">{{ $jamMasuk ?: '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ $jamPulang ?: '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $svBadge }}">{{ $svLabel }}</span>
                                    @if($a->foto_bukti)
                                        <a href="{{ asset('storage/' . $a->foto_bukti) }}" target="_blank"
                                           class="mt-1 block text-[11px] font-bold text-[#2563EB] hover:underline">Bukti</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('cetak.absensi', ['siswa_id' => $a->siswa_id, 'bulan' => $bulanRow]) }}" target="_blank"
                                       class="inline-flex items-center rounded-full bg-[#2563EB] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-blue-700">PDF</a>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button"
                                                @click="edit(@js([
                                                    'id' => $a->id,
                                                    'siswa_id' => $a->siswa_id,
                                                    'tanggal' => optional($a->tanggal)->format('Y-m-d'),
                                                    'status' => $a->status,
                                                    'jam_masuk' => $jamMasuk,
                                                    'jam_pulang' => $jamPulang,
                                                    'status_validasi' => $sv,
                                                    'catatan_instruktur' => $a->catatan_instruktur,
                                                    'foto_bukti_url' => $a->foto_bukti ? asset('storage/'.$a->foto_bukti) : null,
                                                ]))"
                                                class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-[#2563EB] hover:bg-blue-50">Edit</button>
                                        <button type="button"
                                                @click="konfirmHapus(@js(route('admin.monitoring.absensi.destroy', $a->id)))"
                                                class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- TABEL MOBILE / TABLET (< lg): hanya Nama + tombol Detail    --}}
        {{-- ============================================================= --}}
        <div class="lg:hidden bg-white rounded-xl border border-blue-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 text-gray-600 text-left">
                    <tr>
                        <th class="px-3 py-3 text-center w-10">No</th>
                        <th class="px-3 py-3">Siswa</th>
                        <th class="px-3 py-3 text-center w-28">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($absensi as $a)
                        @php
                            $badgeM = match($a->status) {
                                'Hadir' => 'bg-green-50 text-green-700',
                                'Izin'  => 'bg-blue-50 text-blue-700',
                                'Sakit' => 'bg-amber-50 text-amber-700',
                                'Alpha' => 'bg-red-50 text-red-600',
                                default => 'bg-gray-50 text-gray-600',
                            };
                            $jamMasukM  = $a->jam_masuk  ? \Illuminate\Support\Str::substr($a->jam_masuk, 0, 5)  : '';
                            $jamPulangM = $a->jam_pulang ? \Illuminate\Support\Str::substr($a->jam_pulang, 0, 5) : '';
                            $svM = $a->status_validasi ?? 'draft';
                            $bulanRowM = \Illuminate\Support\Carbon::parse($a->tanggal)->format('Y-m');
                        @endphp
                        <tr class="hover:bg-blue-50/40 align-middle">
                            <td class="px-3 py-4 text-center text-gray-500">{{ $absensi->firstItem() + $loop->index }}</td>
                            <td class="px-3 py-4">
                                <div class="font-semibold text-gray-800 leading-snug break-words">{{ $a->siswa->name ?? '-' }}</div>
                                <div class="text-[11px] text-gray-500 mt-0.5">{{ $a->tanggal->format('d M Y') }}</div>
                                <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-[10px] font-medium {{ $badgeM }}">{{ $a->status }}</span>
                            </td>
                            <td class="px-3 py-4 text-center">
                                <button type="button"
                                        @click="lihatDetail(@js([
                                            'id' => $a->id,
                                            'siswa_id' => $a->siswa_id,
                                            'nama' => $a->siswa->name ?? '-',
                                            'kelas' => $a->siswa->kelas ?? '-',
                                            'jurusan' => $a->siswa->jurusan ?? '-',
                                            'tanggal' => optional($a->tanggal)->format('Y-m-d'),
                                            'tanggal_label' => $a->tanggal->format('d M Y'),
                                            'status' => $a->status,
                                            'jam_masuk' => $jamMasukM,
                                            'jam_pulang' => $jamPulangM,
                                            'status_validasi' => $svM,
                                            'catatan_instruktur' => $a->catatan_instruktur,
                                            'foto_bukti_url' => $a->foto_bukti ? asset('storage/'.$a->foto_bukti) : null,
                                            'cetak_url' => route('cetak.absensi', ['siswa_id' => $a->siswa_id, 'bulan' => $bulanRowM]),
                                            'destroy_url' => route('admin.monitoring.absensi.destroy', $a->id),
                                        ]))"
                                        class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-bold text-white transition active:scale-95 hover:bg-blue-700">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Tidak ada data absensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{!! $absensi->links() !!}</div>

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
                        <p class="text-xs text-gray-500"><span x-text="detailData.kelas"></span> &bull; <span x-text="detailData.jurusan"></span></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-gray-100">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                              :class="{
                                  'bg-green-50 text-green-700': detailData.status === 'Hadir',
                                  'bg-blue-50 text-blue-700': detailData.status === 'Izin',
                                  'bg-amber-50 text-amber-700': detailData.status === 'Sakit',
                                  'bg-red-50 text-red-600': detailData.status === 'Alpha',
                              }" x-text="detailData.status"></span>
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold"
                              :class="{
                                  'bg-green-50 text-green-700': detailData.status_validasi === 'disetujui',
                                  'bg-amber-50 text-amber-700': detailData.status_validasi === 'diajukan',
                                  'bg-gray-100 text-gray-500': detailData.status_validasi !== 'disetujui' && detailData.status_validasi !== 'diajukan',
                              }"
                              x-text="detailData.status_validasi === 'disetujui' ? 'Tervalidasi' : (detailData.status_validasi === 'diajukan' ? 'Menunggu' : 'Draft')"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Tanggal</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.tanggal_label"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Jam Masuk</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.jam_masuk || '-'"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Jam Pulang</p>
                            <p class="mt-0.5 text-sm font-medium text-gray-800" x-text="detailData.jam_pulang || '-'"></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Catatan Instruktur</p>
                        <template x-if="detailData.catatan_instruktur">
                            <div class="mt-1 rounded-lg border-l-4 border-amber-300 bg-amber-50 p-2.5 text-xs font-medium italic text-gray-700" x-text="detailData.catatan_instruktur"></div>
                        </template>
                        <template x-if="!detailData.catatan_instruktur">
                            <p class="mt-0.5 text-sm italic text-gray-400">-</p>
                        </template>
                    </div>

                    <template x-if="detailData.foto_bukti_url">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Bukti Fisik</p>
                            <a :href="detailData.foto_bukti_url" target="_blank" class="mt-1 inline-block">
                                <img :src="detailData.foto_bukti_url" alt="Bukti" class="max-h-48 rounded-lg border border-blue-100 object-cover">
                            </a>
                        </div>
                    </template>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 flex flex-wrap gap-2 border-t border-blue-100 bg-white px-5 py-4">
                    <a :href="detailData.cetak_url" target="_blank"
                       class="flex-1 min-w-[90px] rounded-lg border border-[#2563EB] px-3 py-2.5 text-center text-xs font-bold text-[#2563EB] transition hover:bg-[#2563EB] hover:text-white">Cetak PDF</a>
                    <button type="button" @click="editDariDetail()"
                            class="flex-1 min-w-[90px] rounded-lg bg-[#2563EB] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-blue-700">Edit</button>
                    <button type="button" @click="detailOpen = false; konfirmHapus(detailData.destroy_url)"
                            class="flex-1 min-w-[90px] rounded-lg bg-red-600 px-3 py-2.5 text-xs font-bold text-white transition hover:bg-red-700">Hapus</button>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL TAMBAH / EDIT                                             --}}
        {{-- ================================================================= --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4"
             @keydown.escape.window="open = false">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-md sm:rounded-2xl sm:p-6 max-h-[90vh] overflow-y-auto"
                 @click.outside="open = false">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-gray-800" x-text="mode === 'create' ? 'Tambah Absensi' : 'Edit Absensi'"></h3>
                    <button type="button" @click="open = false" class="rounded-lg px-2 py-1 text-lg font-bold text-gray-400 hover:bg-gray-100">&times;</button>
                </div>
                <form :action="actionUrl" method="POST" enctype="multipart/form-data" @submit="simpan($event)" class="space-y-3">
                    @csrf
                    <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="siswa_id" :value="siswaCocok ? siswaCocok.id : ''">

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">NISN Siswa</label>
                        <input type="text" x-model="form.nisn" placeholder="Masukkan NISN siswa"
                               class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        <template x-if="siswaCocok"><p class="mt-1 text-xs font-semibold text-green-600">&#10003; <span x-text="siswaCocok.name"></span></p></template>
                        <template x-if="form.nisn.trim() !== '' && !siswaCocok"><p class="mt-1 text-xs font-semibold text-red-600">NISN tidak cocok</p></template>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Tanggal</label>
                        <input type="date" name="tanggal" x-model="form.tanggal" required
                               class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Status Kehadiran</label>
                        <select name="status" x-model="form.status" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">Jam Masuk</label>
                            <input type="time" name="jam_masuk" x-model="form.jam_masuk" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">Jam Pulang</label>
                            <input type="time" name="jam_pulang" x-model="form.jam_pulang" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Status Validasi</label>
                        <select name="status_validasi" x-model="form.status_validasi" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            <option value="draft">Draft</option>
                            <option value="diajukan">Menunggu Validasi</option>
                            <option value="disetujui">Tervalidasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Catatan Instruktur</label>
                        <textarea name="catatan_instruktur" x-model="form.catatan_instruktur" rows="2" class="w-full rounded-lg border-gray-200 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Foto Bukti Fisik (opsional)</label>
                        <template x-if="form.foto_bukti_url">
                            <div class="mb-1 flex items-center gap-3">
                                <a :href="form.foto_bukti_url" target="_blank" class="text-[11px] font-bold text-[#2563EB] hover:underline">Lihat bukti saat ini</a>
                                <label class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600">
                                    <input type="checkbox" name="hapus_foto_bukti" value="1"> Hapus foto
                                </label>
                            </div>
                        </template>
                        <input type="file" name="foto_bukti" accept="image/*"
                               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-semibold">
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex-1 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Simpan</button>
                        <button type="button" @click="open = false" class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL HAPUS                                                     --}}
        {{-- ================================================================= --}}
        <div x-show="hapusOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
             @keydown.escape.window="hapusOpen = false">
            <div x-show="hapusOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false">
                <h3 class="text-base font-bold text-gray-800">Hapus Data Absensi</h3>
                <p class="mt-1 text-sm text-gray-500">Yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
                <form :action="hapusUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-bold text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.absensiCrud = function () {
            const daftarSiswa = @js($siswaList);
            const tanggalDefault = @js($tanggalDefault);
            const storeUrl = @js(route('admin.monitoring.absensi.store'));
            const baseUrl = @js(url('admin/monitoring/absensi'));
            const kosong = () => ({ id: null, nisn: '', tanggal: tanggalDefault, status: 'Hadir', jam_masuk: '', jam_pulang: '', status_validasi: 'draft', catatan_instruktur: '', foto_bukti_url: null });

            return {
                open: false,
                mode: 'create',
                form: kosong(),
                hapusOpen: false,
                hapusUrl: '',
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

                get siswaCocok() {
                    const nisn = String(this.form.nisn || '').trim();
                    if (!nisn) return null;
                    return daftarSiswa.find(s => String(s.nisn).trim() === nisn) || null;
                },
                get actionUrl() { return this.mode === 'create' ? storeUrl : baseUrl + '/' + this.form.id; },

                tambah() { this.mode = 'create'; this.form = kosong(); this.open = true; },

                edit(d) {
                    const s = daftarSiswa.find(x => String(x.id) === String(d.siswa_id));
                    this.mode = 'edit';
                    this.form = {
                        id: d.id,
                        nisn: s ? String(s.nisn) : '',
                        tanggal: d.tanggal,
                        status: d.status || 'Hadir',
                        jam_masuk: d.jam_masuk || '',
                        jam_pulang: d.jam_pulang || '',
                        status_validasi: d.status_validasi || 'draft',
                        catatan_instruktur: d.catatan_instruktur || '',
                        foto_bukti_url: d.foto_bukti_url || null,
                    };
                    this.open = true;
                },

                // buka detail (mobile)
                lihatDetail(d) { this.detailData = d; this.detailOpen = true; },

                // dari modal detail -> buka form edit
                editDariDetail() {
                    const d = this.detailData;
                    this.detailOpen = false;
                    this.edit({
                        id: d.id,
                        siswa_id: d.siswa_id,
                        tanggal: d.tanggal,
                        status: d.status,
                        jam_masuk: d.jam_masuk,
                        jam_pulang: d.jam_pulang,
                        status_validasi: d.status_validasi,
                        catatan_instruktur: d.catatan_instruktur,
                        foto_bukti_url: d.foto_bukti_url,
                    });
                },

                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },
            };
        };
    </script>
</x-app-layout>

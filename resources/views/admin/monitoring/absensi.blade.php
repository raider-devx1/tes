<x-app-layout title="Monitoring Absensi">
    <style>[x-cloak]{display:none!important;}</style>

    <div x-data="absensiCrud()" class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Monitoring Absensi Siswa</h2>
                <p class="text-sm text-gray-500">Kelola kehadiran & validasi siswa PKL (tambah, ubah, hapus, cetak).</p>
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

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Hadir</p><p class="text-2xl font-bold text-green-600">{{ $rekap['Hadir'] }}</p></div>
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Izin</p><p class="text-2xl font-bold text-blue-600">{{ $rekap['Izin'] }}</p></div>
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Sakit</p><p class="text-2xl font-bold text-amber-500">{{ $rekap['Sakit'] }}</p></div>
            <div class="bg-white rounded-xl border border-blue-100 p-4"><p class="text-xs text-gray-500">Alpha</p><p class="text-2xl font-bold text-red-500">{{ $rekap['Alpha'] }}</p></div>
        </div>

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

        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[1150px]">
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

        <div>{!! $absensi->links() !!}</div>

        {{-- MODAL TAMBAH / EDIT --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4"
             @keydown.escape.window="open = false">
            <div class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-md sm:rounded-2xl sm:p-6 max-h-[90vh] overflow-y-auto"
                 @click.outside="open = false" x-transition>
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
                        <template x-if="siswaCocok"><p class="mt-1 text-xs font-semibold text-green-600">✓ <span x-text="siswaCocok.name"></span></p></template>
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

        {{-- MODAL HAPUS --}}
        <div x-show="hapusOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
             @keydown.escape.window="hapusOpen = false">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false" x-transition>
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
                open: false, mode: 'create', form: kosong(), hapusOpen: false, hapusUrl: '',
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
                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },
            };
        };
    </script>
</x-app-layout>
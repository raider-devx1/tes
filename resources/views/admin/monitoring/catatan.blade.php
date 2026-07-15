<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Catatan Kegiatan Siswa PKL</h2>
    </x-slot>

    <style>[x-cloak]{display:none!important;}</style>

    <div x-data="catatanCrud()" class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- STATISTIK REKAP --}}
            <div class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Catatan</p>
                    <p class="mt-1 text-2xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Disetujui</p>
                    <p class="mt-1 text-2xl font-bold text-[#05b169]">{{ $rekap['disetujui'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Diajukan</p>
                    <p class="mt-1 text-2xl font-bold text-[#d98200]">{{ $rekap['diajukan'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Draft</p>
                    <p class="mt-1 text-2xl font-bold text-[#5b616e]">{{ $rekap['draft'] }}</p>
                </div>
            </div>

            {{-- KONTEN UTAMA --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                @if (session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Catatan Kegiatan Seluruh Siswa</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Admin dapat menambah, mengubah, menghapus, dan mencetak catatan.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="tambah()"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa]">Tambah Catatan</button>
                        <a href="{{ route('cetak.catatan.semua') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Cetak Semua PDF</a>
                    </div>
                </div>

                {{-- FILTER PENCARIAN --}}
                <form method="GET" action="{{ route('admin.monitoring.catatan') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div class="w-full md:w-44">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Kelas</label>
                            <select name="kelas" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Kelas</option>
                                @foreach($kelasList as $opsiKelas)
                                    <option value="{{ $opsiKelas }}" @selected(request('kelas') === $opsiKelas)>{{ $opsiKelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-44">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jurusan</label>
                            <select name="jurusan" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Jurusan</option>
                                @foreach($jurusanList as $opsiJurusan)
                                    <option value="{{ $opsiJurusan }}" @selected(request('jurusan') === $opsiJurusan)>{{ $opsiJurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-48">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                            <select name="status" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                <option value="diajukan" @selected(request('status') === 'diajukan')>Diajukan</option>
                                <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Cari</button>
                            <a href="{{ route('admin.monitoring.catatan') }}" class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                {{-- TABEL DATA --}}
                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1200px] text-left text-sm table-auto border-collapse">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3.5 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3.5 font-bold w-48">Siswa</th>
                                <th class="px-4 py-3.5 font-bold w-44">Pekerjaan</th>
                                <th class="px-4 py-3.5 font-bold w-64">Perencanaan</th>
                                <th class="px-4 py-3.5 font-bold w-64">Hasil / Pelaksanaan</th>
                                <th class="px-4 py-3.5 font-bold w-52">Catatan Instruktur</th>
                                <th class="px-4 py-3.5 text-center font-bold w-32">Status & Bukti</th>
                                <th class="px-4 py-3.5 text-center font-bold w-24">Cetak</th>
                                <th class="px-4 py-3.5 text-center font-bold w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($catatan as $item)
                                @php
                                    $badge = match($item->status) {
                                        'disetujui' => 'bg-[#05b169] text-white',
                                        'diajukan'  => 'bg-[#d98200] text-white',
                                        default     => 'bg-[#5b616e] text-white',
                                    };
                                    $label = match($item->status) {
                                        'disetujui' => 'Disetujui',
                                        'diajukan'  => 'Diajukan',
                                        default     => 'Draft',
                                    };
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-4 text-center font-semibold text-black">{{ $catatan->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-4 text-black">
                                        <div class="font-bold leading-snug break-words">{{ $item->user->name ?? '-' }}</div>
                                        <div class="text-xs text-[#5b616e] mt-1 font-mono">NISN: {{ $item->user->nisn ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-4 font-medium text-black break-words leading-relaxed">
                                        {{ $item->nama_pekerjaan }}
                                    </td>
                                    <td class="px-4 py-4 font-normal text-gray-900 break-words whitespace-normal leading-relaxed">
                                        {{ $item->perencanaan_kegiatan }}
                                    </td>
                                    <td class="px-4 py-4 font-normal text-gray-900 break-words whitespace-normal leading-relaxed">
                                        {{ $item->pelaksanaan_kegiatan }}
                                    </td>
                                    <td class="px-4 py-4 text-black break-words leading-relaxed">
                                        @if($item->catatan_instruktur)
                                            <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2.5 text-xs font-medium italic text-black">
                                                {{ $item->catatan_instruktur }}
                                            </div>
                                        @else
                                            <span class="text-[#5b616e] italic text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-bold {{ $badge }} mb-2">
                                            {{ $label }}
                                        </span>
                                        @if($item->foto_bukti)
                                            <a href="{{ asset('storage/' . $item->foto_bukti) }}" target="_blank"
                                               class="block text-[11px] font-bold text-[#0047d6] hover:underline whitespace-nowrap">Lihat Bukti Fisik ↗</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <a href="{{ route('cetak.catatan', ['catatan_id' => $item->id]) }}" target="_blank"
                                           class="inline-flex items-center justify-center rounded-lg border-2 border-[#0047d6] px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6] hover:text-white">
                                            PDF
                                        </a>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-col sm:flex-row items-center justify-center gap-1.5">
                                            <button type="button"
                                                    @click="edit(@js([
                                                        'id' => $item->id,
                                                        'user_id' => $item->user_id,
                                                        'nama_pekerjaan' => $item->nama_pekerjaan,
                                                        'perencanaan_kegiatan' => $item->perencanaan_kegiatan,
                                                        'pelaksanaan_kegiatan' => $item->pelaksanaan_kegiatan,
                                                        'catatan_instruktur' => $item->catatan_instruktur,
                                                        'status' => $item->status,
                                                        'foto_bukti_url' => $item->foto_bukti ? asset('storage/'.$item->foto_bukti) : null,
                                                    ]))"
                                                    class="w-full rounded-lg border-2 border-[#0047d6]/30 px-3 py-1.5 text-xs font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Edit</button>
                                            <button type="button"
                                                    @click="konfirmHapus(@js(route('admin.monitoring.catatan.destroy', $item->id)))"
                                                    class="w-full rounded-lg border-2 border-red-200 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada catatan yang cocok / belum ada catatan dari siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{!! $catatan->links() !!}</div>
            </div>
        </div>

        {{-- MODAL TAMBAH / EDIT --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4"
             @keydown.escape.window="open = false">
            <div class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-lg sm:rounded-2xl sm:p-6 max-h-[90vh] overflow-y-auto"
                 @click.outside="open = false" x-transition>
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-black" x-text="mode === 'create' ? 'Tambah Catatan Kegiatan' : 'Edit Catatan Kegiatan'"></h3>
                    <button type="button" @click="open = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                </div>

                <form :action="actionUrl" method="POST" enctype="multipart/form-data" @submit="simpan($event)" class="space-y-3">
                    @csrf
                    <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="user_id" :value="siswaCocok ? siswaCocok.id : ''">

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">NISN Siswa</label>
                        <input type="text" x-model="form.nisn" placeholder="Masukkan NISN siswa"
                               class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <template x-if="siswaCocok"><p class="mt-1 text-xs font-semibold text-[#05b169]">✓ <span x-text="siswaCocok.name"></span></p></template>
                        <template x-if="form.nisn.trim() !== '' && !siswaCocok"><p class="mt-1 text-xs font-semibold text-[#cf202f]">NISN tidak cocok</p></template>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Nama Pekerjaan</label>
                        <input type="text" name="nama_pekerjaan" x-model="form.nama_pekerjaan" required
                               class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Perencanaan Kegiatan</label>
                        <textarea name="perencanaan_kegiatan" x-model="form.perencanaan_kegiatan" rows="3"
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Hasil / Pelaksanaan</label>
                        <textarea name="pelaksanaan_kegiatan" x-model="form.pelaksanaan_kegiatan" rows="3"
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Catatan Instruktur</label>
                        <textarea name="catatan_instruktur" x-model="form.catatan_instruktur" rows="2"
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Status</label>
                        <select name="status" x-model="form.status"
                                class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            <option value="draft">Draft</option>
                            <option value="diajukan">Diajukan</option>
                            <option value="disetujui">Disetujui</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Foto Bukti Fisik (opsional)</label>
                        <template x-if="form.foto_bukti_url">
                            <div class="mb-1 flex items-center gap-3">
                                <a :href="form.foto_bukti_url" target="_blank" class="text-[11px] font-bold text-[#0047d6] hover:underline">Lihat bukti saat ini</a>
                                <label class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#cf202f]">
                                    <input type="checkbox" name="hapus_foto_bukti" value="1"> Hapus foto
                                </label>
                            </div>
                        </template>
                        <input type="file" name="foto_bukti" accept="image/*"
                               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef0f3] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-[#0a0b0d]">
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Simpan</button>
                        <button type="button" @click="open = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL HAPUS --}}
        <div x-show="hapusOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
             @keydown.escape.window="hapusOpen = false">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false" x-transition>
                <h3 class="text-base font-bold text-black">Hapus Catatan Kegiatan</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Yakin ingin menghapus catatan ini? Tindakan ini tidak dapat dibatalkan.</p>
                <form :action="hapusUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#cf202f] px-4 py-2 text-sm font-bold text-white hover:bg-[#b01926]">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.catatanCrud = function () {
            const daftarSiswa = @js($siswaList);
            const storeUrl = @js(route('admin.monitoring.catatan.store'));
            const baseUrl = @js(url('admin/monitoring/catatan'));
            const kosong = () => ({ id: null, nisn: '', nama_pekerjaan: '', perencanaan_kegiatan: '', pelaksanaan_kegiatan: '', catatan_instruktur: '', status: 'draft', foto_bukti_url: null });
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
                    const s = daftarSiswa.find(x => String(x.id) === String(d.user_id));
                    this.mode = 'edit';
                    this.form = {
                        id: d.id,
                        nisn: s ? String(s.nisn) : '',
                        nama_pekerjaan: d.nama_pekerjaan || '',
                        perencanaan_kegiatan: d.perencanaan_kegiatan || '',
                        pelaksanaan_kegiatan: d.pelaksanaan_kegiatan || '',
                        catatan_instruktur: d.catatan_instruktur || '',
                        status: d.status || 'draft',
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
<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Rekap &amp; Penilaian Siswa PKL</h2>
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5 shrink-0">Kembali</button>
        </div>
    </x-slot>

    <div x-data="penilaianCrud()" class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Dinilai</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['sudah'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Belum Lengkap</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['belum'] }}</p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                        <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Daftar Penilaian Seluruh Siswa</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Nilai Akhir = rata-rata 6 komponen (skala 0–100). Admin dapat menambah, mengubah, menghapus, dan mencetak.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="tambah()"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa]">Tambah Nilai</button>
                        <a href="{{ route('cetak.nilai.semua') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Cetak Semua PDF</a>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.evaluasi.penilaian') }}" class="mb-6">
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
                                @foreach($kelasList as $opsiKelas)<option value="{{ $opsiKelas }}" @selected(request('kelas') === $opsiKelas)>{{ $opsiKelas }}</option>@endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-44">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jurusan</label>
                            <select name="jurusan" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Jurusan</option>
                                @foreach($jurusanList as $opsiJurusan)<option value="{{ $opsiJurusan }}" @selected(request('jurusan') === $opsiJurusan)>{{ $opsiJurusan }}</option>@endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-48">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Penilaian</label>
                            <select name="status" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="sudah" @selected(request('status') === 'sudah')>Sudah Dinilai</option>
                                <option value="belum" @selected(request('status') === 'belum')>Belum Dinilai</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Cari</button>
                            <a href="{{ route('admin.evaluasi.penilaian') }}" class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold">Siswa</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 font-bold w-40">Guru Pembimbing</th>
                                <th class="px-4 py-3 text-center font-bold w-32 bg-[#0038aa]">Nilai Akhir</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-56">Cetak</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse($siswa as $item)
                                @php
                                    $nilai = $item->nilai;
                                    $daftarSkor = [
                                        optional($nilai)->skor_soft_skill,
                                        optional($nilai)->skor_hard_skill,
                                        optional($nilai)->skor_pengembangan,
                                        optional($nilai)->skor_kewirausahaan,
                                        optional($nilai)->skor_laporan,
                                        optional($nilai)->skor_presentasi,
                                    ];
                                    $telahDinilai = $nilai && ! in_array(null, $daftarSkor, true);
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $item->name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $item->nisn }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">{{ $item->guru?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-[#0047d6] bg-[#0047d6]/5">{{ $telahDinilai ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($telahDinilai)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Lengkap</span>
                                        @elseif($nilai)
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Belum Lengkap</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#5b616e] px-3 py-1 text-xs font-bold text-white">Belum Dinilai</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            <a href="{{ route('cetak.nilai.template', $item->id) }}" target="_blank" title="Cetak template kosong untuk instruktur"
                                               class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700 transition hover:bg-gray-200">Template</a>
                                            @if($telahDinilai)
                                                <a href="{{ route('cetak.nilai.guru', $item->id) }}" target="_blank" title="Cetak format penilaian guru"
                                                   class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">PDF Guru</a>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                    @click="edit(@js([
                                                        'nilai_id' => optional($nilai)->id,
                                                        'siswa_id' => $item->id,
                                                        'skor_soft_skill' => optional($nilai)->skor_soft_skill,
                                                        'deskripsi_soft_skill' => optional($nilai)->deskripsi_soft_skill,
                                                        'skor_hard_skill' => optional($nilai)->skor_hard_skill,
                                                        'deskripsi_hard_skill' => optional($nilai)->deskripsi_hard_skill,
                                                        'skor_pengembangan' => optional($nilai)->skor_pengembangan,
                                                        'deskripsi_pengembangan' => optional($nilai)->deskripsi_pengembangan,
                                                        'skor_kewirausahaan' => optional($nilai)->skor_kewirausahaan,
                                                        'deskripsi_kewirausahaan' => optional($nilai)->deskripsi_kewirausahaan,
                                                        'skor_laporan' => optional($nilai)->skor_laporan,
                                                        'deskripsi_laporan' => optional($nilai)->deskripsi_laporan,
                                                        'skor_presentasi' => optional($nilai)->skor_presentasi,
                                                        'deskripsi_presentasi' => optional($nilai)->deskripsi_presentasi,
                                                        'catatan_guru' => optional($nilai)->catatan_guru,
                                                        'foto_url' => optional($nilai)->foto_lembar_instruktur ? asset('storage/'.$nilai->foto_lembar_instruktur) : null,
                                                    ]))"
                                                    class="rounded-lg border-2 border-[#0047d6]/30 px-3 py-1.5 text-xs font-bold text-[#0047d6] hover:bg-[#0047d6]/5"
                                                    x-text="@js((bool) $nilai) ? 'Edit' : 'Beri Nilai'"></button>
                                            @if($nilai)
                                                <button type="button"
                                                        @click="konfirmHapus(@js(route('admin.evaluasi.penilaian.destroy', $nilai->id)))"
                                                        class="rounded-lg border-2 border-red-200 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50">Hapus</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data siswa PKL yang cocok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $siswa->links() }}</div>
            </div>
        </div>

        {{-- MODAL TAMBAH / EDIT NILAI (6 komponen 0–100) --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4" @keydown.escape.window="open = false">
            <div class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-3xl sm:rounded-2xl sm:p-6 max-h-[90vh] overflow-y-auto" @click.outside="open = false" x-transition>
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-black" x-text="mode === 'create' ? 'Tambah Penilaian' : 'Edit Penilaian'"></h3>
                    <button type="button" @click="open = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                </div>

                <form :action="actionUrl" method="POST" enctype="multipart/form-data" @submit="simpan($event)" class="space-y-5">
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

                    <div class="p-4 bg-[#0047d6]/5 rounded-xl border-2 border-[#0047d6]/20">
                        <label class="block text-sm font-bold text-black mb-1">Foto Lembar Penilaian Instruktur</label>
                        <p class="text-xs text-[#5b616e] mb-2">Unggah foto lembar penilaian yang sudah diisi &amp; diparaf instruktur (JPG/PNG, maks 2 MB).</p>
                        <template x-if="form.foto_url">
                            <p class="text-xs mb-2">
                                <a :href="form.foto_url" target="_blank" class="font-bold text-[#0047d6] underline">Lihat foto yang sudah diunggah</a>
                                <span class="text-[#5b616e]"> (kosongkan bila tidak ingin mengganti)</span>
                            </p>
                        </template>
                        <input type="file" name="foto_lembar_instruktur" accept="image/*"
                               class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#0047d6] file:px-4 file:py-2 file:text-white file:font-bold">
                    </div>

                    <h4 class="text-sm font-bold text-[#0047d6] uppercase tracking-wide">Nilai dari Instruktur (salin dari lembar instruktur)</h4>

                    <template x-for="(k, idx) in komponen" :key="k.skor">
                        <div class="p-4 bg-gray-50 rounded-xl border-2 border-gray-200">
                            <label class="block text-sm font-bold text-black mb-1" x-text="(idx + 1) + '. ' + k.label + ' (0-100)'"></label>
                            <input type="number" :name="k.skor" min="0" max="100" x-model="form[k.skor]" required
                                   class="block w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30 mb-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                            <textarea :name="k.deskripsi" rows="3" x-model="form[k.deskripsi]" required
                                      class="block w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                        </div>
                    </template>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Catatan / Rekomendasi Guru (opsional)</label>
                        <textarea name="catatan_guru" x-model="form.catatan_guru" rows="2"
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Simpan Penilaian</button>
                        <button type="button" @click="open = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL HAPUS --}}
        <div x-show="hapusOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="hapusOpen = false">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false" x-transition>
                <h3 class="text-base font-bold text-black">Hapus Data Penilaian</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Yakin ingin menghapus penilaian ini? Tindakan ini tidak dapat dibatalkan.</p>
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
        window.penilaianCrud = function () {
            const daftarSiswa = @js($siswaList);
            const storeUrl = @js(route('admin.evaluasi.penilaian.store'));
            const komponen = [
                { skor: 'skor_soft_skill',    deskripsi: 'deskripsi_soft_skill',    label: 'Internalisasi dan penerapan soft skill' },
                { skor: 'skor_hard_skill',    deskripsi: 'deskripsi_hard_skill',    label: 'Penerapan hard skill' },
                { skor: 'skor_pengembangan',  deskripsi: 'deskripsi_pengembangan',  label: 'Peningkatan dan pengembangan hard skill' },
                { skor: 'skor_kewirausahaan', deskripsi: 'deskripsi_kewirausahaan', label: 'Kewirausahaan' },
                { skor: 'skor_laporan',       deskripsi: 'deskripsi_laporan',       label: 'Penyusunan laporan' },
                { skor: 'skor_presentasi',    deskripsi: 'deskripsi_presentasi',    label: 'Presentasi / sidang' },
            ];
            const kosong = () => ({
                nilai_id: null, nisn: '',
                skor_soft_skill: '', deskripsi_soft_skill: '',
                skor_hard_skill: '', deskripsi_hard_skill: '',
                skor_pengembangan: '', deskripsi_pengembangan: '',
                skor_kewirausahaan: '', deskripsi_kewirausahaan: '',
                skor_laporan: '', deskripsi_laporan: '',
                skor_presentasi: '', deskripsi_presentasi: '',
                catatan_guru: '', foto_url: null,
            });
            return {
                open: false, mode: 'create', komponen, form: kosong(), hapusOpen: false, hapusUrl: '',
                get siswaCocok() {
                    const nisn = String(this.form.nisn || '').trim();
                    if (!nisn) return null;
                    return daftarSiswa.find(s => String(s.nisn).trim() === nisn) || null;
                },
                get actionUrl() { return this.mode === 'create' ? storeUrl : storeUrl + '/' + this.form.nilai_id; },
                tambah() { this.mode = 'create'; this.form = kosong(); this.open = true; },
                edit(d) {
                    const s = daftarSiswa.find(x => String(x.id) === String(d.siswa_id));
                    this.mode = d.nilai_id ? 'edit' : 'create';
                    this.form = {
                        nilai_id: d.nilai_id,
                        nisn: s ? String(s.nisn) : '',
                        skor_soft_skill: d.skor_soft_skill ?? '', deskripsi_soft_skill: d.deskripsi_soft_skill ?? '',
                        skor_hard_skill: d.skor_hard_skill ?? '', deskripsi_hard_skill: d.deskripsi_hard_skill ?? '',
                        skor_pengembangan: d.skor_pengembangan ?? '', deskripsi_pengembangan: d.deskripsi_pengembangan ?? '',
                        skor_kewirausahaan: d.skor_kewirausahaan ?? '', deskripsi_kewirausahaan: d.deskripsi_kewirausahaan ?? '',
                        skor_laporan: d.skor_laporan ?? '', deskripsi_laporan: d.deskripsi_laporan ?? '',
                        skor_presentasi: d.skor_presentasi ?? '', deskripsi_presentasi: d.deskripsi_presentasi ?? '',
                        catatan_guru: d.catatan_guru ?? '', foto_url: d.foto_url ?? null,
                    };
                    this.open = true;
                },
                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },
            };
        };
    </script>
</x-app-layout>
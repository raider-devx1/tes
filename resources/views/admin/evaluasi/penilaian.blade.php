<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Rekap &amp; Penilaian Siswa PKL</h2>
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5 shrink-0">
                 Kembali
            </button>
        </div>
    </x-slot>

    <div x-data="penilaianCrud()" class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-black"> {{ $rekap['total'] }} </p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Dinilai</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]"> {{ $rekap['sudah'] }} </p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Belum Lengkap</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]"> {{ $rekap['belum'] }} </p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Daftar Penilaian Seluruh Siswa</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Nilai Akhir = 50% Instruktur + 20% Guru + 30% Laporan. Admin dapat menambah, mengubah, dan menghapus penilaian.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="tambah()"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa]">
                             Tambah Nilai
                        </button>
                        <a href="{{ route('cetak.nilai.semua') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                            Cetak Semua PDF
                        </a>
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
                                @foreach($kelasList as $opsiKelas)
                                    <option value="{{ $opsiKelas }}" @selected(request('kelas') === $opsiKelas)> {{ $opsiKelas }} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-44">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jurusan</label>
                            <select name="jurusan" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Jurusan</option>
                                @foreach($jurusanList as $opsiJurusan)
                                    <option value="{{ $opsiJurusan }}" @selected(request('jurusan') === $opsiJurusan)> {{ $opsiJurusan }} </option>
                                @endforeach
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
                                <th class="px-4 py-3 text-center font-bold w-32">Instruktur (/5)</th>
                                <th class="px-4 py-3 text-center font-bold w-32 bg-[#0038aa]">Nilai Akhir</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-20">Cetak</th>
                                <th class="px-4 py-3 text-center font-bold w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse($siswa as $item)
                                @php
                                    $nilai = $item->nilai;
                                    $telahDinilai = $nilai && $nilai->nilai_akhir !== null;
                                @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black"> {{ $siswa->firstItem() + $loop->index }} </td>
                                    <td class="px-4 py-3 font-bold text-black break-words"> {{ $item->name }} </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black"> {{ $item->nisn }} </td>
                                    <td class="px-4 py-3 font-medium text-black break-words"> {{ $item->guru?->name ?? '-' }} </td>
                                    <td class="px-4 py-3 text-center font-medium text-black"> {{ $nilai && $nilai->rata_rata !== null ? number_format($nilai->rata_rata, 2) : '-' }} </td>
                                    <td class="px-4 py-3 text-center font-bold text-[#0047d6] bg-[#0047d6]/5"> {{ $telahDinilai ? number_format($nilai->nilai_akhir, 2) : '-' }} </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($telahDinilai)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Sudah Dinilai</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Belum Dinilai</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('cetak.nilai', $item->id) }}" target="_blank"
                                           class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">PDF</a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                    @click="edit(@js([
                                                        'nilai_id' => optional($nilai)->id,
                                                        'siswa_id' => $item->id,
                                                        'soft_skill' => optional($nilai)->soft_skill,
                                                        'hard_skill' => optional($nilai)->hard_skill,
                                                        'pengembangan_hard_skill' => optional($nilai)->pengembangan_hard_skill,
                                                        'kewirausahaan' => optional($nilai)->kewirausahaan,
                                                        'catatan_rekomendasi' => optional($nilai)->catatan_rekomendasi,
                                                        'nilai_guru' => optional($nilai)->nilai_guru,
                                                        'nilai_laporan' => optional($nilai)->nilai_laporan,
                                                        'catatan_guru' => optional($nilai)->catatan_guru,
                                                    ]))"
                                                    class="rounded-lg border-2 border-[#0047d6]/30 px-3 py-1.5 text-xs font-bold text-[#0047d6] hover:bg-[#0047d6]/5"
                                                    x-text="@js((bool) $nilai) ? 'Edit' : 'Nilai'"></button>
                                            @if($nilai)
                                                <button type="button"
                                                        @click="konfirmHapus(@js(route('admin.evaluasi.penilaian.destroy', $nilai->id)))"
                                                        class="rounded-lg border-2 border-red-200 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50">Hapus</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data siswa PKL yang cocok dengan pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4"> {{ $siswa->links() }} </div>
            </div>
        </div>

        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4" @keydown.escape.window="open = false">
            <div class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-2xl sm:rounded-2xl sm:p-6 max-h-[90vh] overflow-y-auto" @click.outside="open = false" x-transition>
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-black" x-text="mode === 'create' ? 'Tambah Penilaian' : 'Edit Penilaian'"></h3>
                    <button type="button" @click="open = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                </div>

                <form :action="actionUrl" method="POST" @submit="simpan($event)" class="space-y-4">
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

                    <div class="rounded-xl border-2 border-[#0047d6]/15 p-3">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wide text-[#0047d6]">Komponen Instruktur (1–5)</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-black">Soft Skill</label>
                                <input type="number" name="soft_skill" min="1" max="5" x-model="form.soft_skill" class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-black">Hard Skill</label>
                                <input type="number" name="hard_skill" min="1" max="5" x-model="form.hard_skill" class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-black">Pengembangan Hard Skill</label>
                                <input type="number" name="pengembangan_hard_skill" min="1" max="5" x-model="form.pengembangan_hard_skill" class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-black">Kewirausahaan</label>
                                <input type="number" name="kewirausahaan" min="1" max="5" x-model="form.kewirausahaan" class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="mb-1 block text-xs font-semibold text-black">Catatan / Rekomendasi Instruktur</label>
                            <textarea name="catatan_rekomendasi" x-model="form.catatan_rekomendasi" rows="2" class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                        </div>
                    </div>

                    <div class="rounded-xl border-2 border-[#0047d6]/15 p-3">
                        <p class="mb-2 text-xs font-bold uppercase tracking-wide text-[#0047d6]">Komponen Guru (0–100)</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-black">Nilai Guru</label>
                                <input type="number" step="0.01" name="nilai_guru" min="0" max="100" x-model="form.nilai_guru" class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-semibold text-black">Nilai Laporan</label>
                                <input type="number" step="0.01" name="nilai_laporan" min="0" max="100" x-model="form.nilai_laporan" class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="mb-1 block text-xs font-semibold text-black">Catatan Guru</label>
                            <textarea name="catatan_guru" x-model="form.catatan_guru" rows="2" class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                        </div>
                    </div>

                    <p class="text-xs font-medium text-[#5b616e]">Nilai Akhir dihitung otomatis (50% Instruktur + 20% Guru + 30% Laporan) setelah keempat komponen instruktur, nilai guru, dan nilai laporan terisi.</p>

                    <div class="flex gap-2 pt-1">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''" class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Simpan</button>
                        <button type="button" @click="open = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="hapusOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="hapusOpen = false">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false" x-transition>
                <h3 class="text-base font-bold text-black">Hapus Penilaian</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Yakin ingin menghapus data penilaian siswa ini? Tindakan ini tidak dapat dibatalkan.</p>
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
            const kosong = () => ({ id: null, nisn: '', soft_skill: '', hard_skill: '', pengembangan_hard_skill: '', kewirausahaan: '', catatan_rekomendasi: '', nilai_guru: '', nilai_laporan: '', catatan_guru: '' });
            return {
                open: false,
                mode: 'create',
                form: kosong(),
                hapusOpen: false,
                hapusUrl: '',
                get siswaCocok() {
                    const nisn = String(this.form.nisn || '').trim();
                    if (!nisn) return null;
                    return daftarSiswa.find(s => String(s.nisn).trim() === nisn) || null;
                },
                get actionUrl() { return this.mode === 'create' ? storeUrl : storeUrl + '/' + this.form.id; },
                tambah() { this.mode = 'create'; this.form = kosong(); this.open = true; },
                edit(d) {
                    const s = daftarSiswa.find(x => String(x.id) === String(d.siswa_id));
                    this.mode = d.nilai_id ? 'edit' : 'create';
                    this.form = {
                        id: d.nilai_id,
                        nisn: s ? String(s.nisn) : '',
                        soft_skill: d.soft_skill ?? '',
                        hard_skill: d.hard_skill ?? '',
                        pengembangan_hard_skill: d.pengembangan_hard_skill ?? '',
                        kewirausahaan: d.kewirausahaan ?? '',
                        catatan_rekomendasi: d.catatan_rekomendasi ?? '',
                        nilai_guru: d.nilai_guru ?? '',
                        nilai_laporan: d.nilai_laporan ?? '',
                        catatan_guru: d.catatan_guru ?? '',
                    };
                    this.open = true;
                },
                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },
            };
        };
    </script>
</x-app-layout>
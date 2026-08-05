<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Evaluasi Lembar Observasi</h2>
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali
            </button>
        </div>
    </x-slot>

    <div x-data="observasiCrud()" class="py-6 sm:py-8 md:py-12 bg-white">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- REKAP STATISTICS CARDS --}}
            <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-5 shadow-sm">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Observasi</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-4 sm:p-5 shadow-sm">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Divalidasi</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#05b169]">{{ $rekap['disetujui'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-4 sm:p-5 shadow-sm">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-[#5b616e]">Draft</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#d98200]">{{ $rekap['menunggu'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-5 shadow-sm">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-[#5b616e]">Jumlah Guru</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-black">{{ $jumlahGuru }}</p>
                </div>
            </div>

            {{-- MAIN CONTAINER --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                {{-- ALERTS --}}
                @if (session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                {{-- HEADER SECTION --}}
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Lembar Observasi Seluruh Siswa</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Admin dapat menambah, mengubah, menghapus, memvalidasi, membatalkan validasi, dan mencetak lembar observasi.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="tambah()"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa]">Tambah Observasi</button>
                        <a href="{{ route('cetak.observasi.semua') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Cetak Semua PDF</a>
                    </div>
                </div>

                {{-- ========= VALIDASI OBSERVASI: BEBERAPA NISN SEKALIGUS / SEMUA SISWA ========= --}}
                @php
                    $observasiValidasiList = ($siswaList ?? collect())->map(fn ($s) => [
                        'nisn'      => (string) ($s->nisn ?? ''),
                        'name'      => $s->name,
                        'statusPkl' => $s->status_pkl ?? '-',
                    ])->values();
                @endphp
                <div x-data="validasiObservasiMassal(@js($observasiValidasiList), @js(route('admin.evaluasi.observasi.validasi-pratinjau')))"
                     x-effect="document.body.style.overflow = bukaMassal ? 'hidden' : ''"
                     class="mb-6 rounded-2xl border-2 border-[#05b169]/30 bg-white p-4 sm:p-5 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold tracking-tight text-black">Validasi Observasi</h3>
                        <p class="text-xs font-medium text-[#5b616e]">
                            Memvalidasi atau membatalkan validasi lembar observasi <span class="font-bold text-black">tanpa membuka satu per satu</span>:
                            bisa <span class="font-bold text-black">beberapa NISN sekaligus</span> atau <span class="font-bold text-black">semua siswa</span>,
                            dengan filter <span class="font-bold text-black">hari tertentu</span>, <span class="font-bold text-black">rentang tanggal</span>, atau seluruh riwayat.
                            Isi observasi, bukti foto, dan paraf digital tidak diubah.
                        </p>
                    </div>
                    <button type="button" @click="bukaModal()"
                            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#05b169] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-[#05b169]/30 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Validasi Observasi
                    </button>

                    <template x-teleport="body">
                        <div x-show="bukaMassal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="bukaMassal = false">
                            <div class="absolute inset-0 bg-black/50" @click="bukaMassal = false"></div>
                            <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col rounded-2xl bg-white shadow-xl">
                                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                    <div>
                                        <h3 class="text-base font-bold text-gray-800">Validasi Observasi Massal</h3>
                                        <p class="text-xs font-medium text-[#5b616e]">Pilih lingkup siswa dan tanggalnya, periksa dulu jumlahnya, lalu setujui atau batalkan validasinya.</p>
                                    </div>
                                    <button type="button" @click="bukaMassal = false" class="text-2xl leading-none text-gray-400 hover:text-black">&times;</button>
                                </div>

                                <form method="POST" action="{{ route('admin.evaluasi.observasi.validasi-massal') }}" x-ref="formValidasiMassal" @submit.prevent
                                      class="overflow-y-auto px-5 py-4 space-y-4 text-left">
                                    @csrf
                                    <input type="hidden" name="mode" :value="mode">
                                    <input type="hidden" name="jenis_tanggal" :value="jenisTanggal">
                                    <input type="hidden" name="nisn" :value="mode === 'nisn' ? daftarNisn.join(',') : ''">
                                    <input type="hidden" name="aksi" x-ref="inputAksi" value="setujui">

                                    {{-- Lingkup siswa --}}
                                    <div class="flex gap-2">
                                        <button type="button" @click="setMode('nisn')" :class="mode==='nisn' ? 'bg-[#0047d6] text-white' : 'bg-gray-100 text-gray-600'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">Per Siswa (NISN)</button>
                                        <button type="button" @click="setMode('semua')" :class="mode==='semua' ? 'bg-[#0047d6] text-white' : 'bg-gray-100 text-gray-600'" class="flex-1 rounded-lg px-3 py-2 text-sm font-semibold">Semua Siswa</button>
                                    </div>

                                    {{-- NISN: boleh lebih dari satu --}}
                                    <div x-show="mode==='nisn'" x-cloak>
                                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">
                                            NISN Siswa
                                            <span class="ml-1 rounded bg-[#0047d6]/10 px-1.5 py-0.5 text-[10px] font-bold normal-case tracking-normal text-[#0047d6]">boleh lebih dari satu</span>
                                        </label>
                                        <textarea x-model="nisnInput" rows="2"
                                                  placeholder="Tulis / tempel beberapa NISN, pisahkan dengan koma, spasi, atau baris baru. Contoh: 0012345678, 0012345679"
                                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <button type="button" @click="isiSemuaAktif()" class="rounded-lg border-2 border-[#0047d6]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#0047d6] hover:bg-[#0047d6]/10">Isi semua siswa aktif</button>
                                            <button type="button" @click="kosongkanNisn()" x-show="daftarNisn.length" x-cloak class="rounded-lg border-2 border-[#cf202f]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#cf202f] hover:bg-[#cf202f]/10">Kosongkan</button>
                                            <span x-show="daftarNisn.length" x-cloak class="text-[11px] font-bold text-[#5b616e]">
                                                Terbaca <span x-text="daftarNisn.length"></span> NISN &middot; cocok <span class="text-[#05b169]" x-text="nisnCocok.length"></span>
                                                <span x-show="nisnTidakCocok.length" class="text-[#cf202f]">&middot; <span x-text="nisnTidakCocok.length"></span> tidak ditemukan</span>
                                            </span>
                                        </div>

                                        {{-- Chip NISN: klik untuk membuang --}}
                                        <div x-show="daftarNisn.length" x-cloak class="mt-2 flex flex-wrap gap-1.5">
                                            <template x-for="n in nisnCocok" :key="'ok-' + n">
                                                <button type="button" @click="hapusNisn(n)" class="inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-[11px] font-bold text-[#0047d6] hover:bg-[#0047d6]/20">
                                                    <span x-text="namaNisn(n)"></span>
                                                    <span class="text-sm leading-none">&times;</span>
                                                </button>
                                            </template>
                                            <template x-for="n in nisnTidakCocok" :key="'no-' + n">
                                                <button type="button" @click="hapusNisn(n)" class="inline-flex items-center gap-1 rounded-full bg-[#cf202f]/10 px-2.5 py-1 text-[11px] font-bold text-[#cf202f] hover:bg-[#cf202f]/20">
                                                    <span x-text="n + ' (tidak ada)'"></span>
                                                    <span class="text-sm leading-none">&times;</span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Mode semua siswa --}}
                                    <div x-show="mode==='semua'" x-cloak class="space-y-2 rounded-xl border-2 border-[#d98200]/30 bg-[#d98200]/5 px-4 py-3">
                                        <p class="text-xs font-bold text-[#d98200]">Aksi ini berlaku untuk SEMUA siswa PKL periode berjalan.</p>
                                        <label class="flex items-start gap-2 text-[11px] font-medium text-[#5b616e]">
                                            <input type="checkbox" name="semua_periode" value="1" x-model="semuaPeriode" @change="hasil = null" class="mt-0.5 rounded border-[#d98200]/40 text-[#d98200] focus:ring-[#d98200]">
                                            <span>Sertakan juga siswa dari <span class="font-bold">semua periode</span>, termasuk arsip angkatan lama.</span>
                                        </label>
                                    </div>

                                    {{-- Cakupan tanggal --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Cakupan Tanggal</label>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" @click="setJenis('tanggal')" :class="jenisTanggal==='tanggal' ? 'bg-[#0047d6] text-white' : 'bg-white text-[#0047d6] hover:bg-[#0047d6]/10'" class="rounded-lg border-2 border-[#0047d6]/30 px-3 py-1.5 text-[11px] font-bold">Hari Tertentu</button>
                                            <button type="button" @click="setJenis('rentang')" :class="jenisTanggal==='rentang' ? 'bg-[#0047d6] text-white' : 'bg-white text-[#0047d6] hover:bg-[#0047d6]/10'" class="rounded-lg border-2 border-[#0047d6]/30 px-3 py-1.5 text-[11px] font-bold">Rentang Tanggal</button>
                                            <button type="button" @click="setJenis('semua')" :class="jenisTanggal==='semua' ? 'bg-[#0047d6] text-white' : 'bg-white text-[#0047d6] hover:bg-[#0047d6]/10'" class="rounded-lg border-2 border-[#0047d6]/30 px-3 py-1.5 text-[11px] font-bold">Seluruh Riwayat</button>
                                        </div>

                                        <div x-show="jenisTanggal==='tanggal'" x-cloak class="mt-2">
                                            <input type="date" name="tanggal" x-model="tanggal" @change="hasil = null"
                                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                        </div>

                                        <div x-show="jenisTanggal==='rentang'" x-cloak class="mt-2 grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Tanggal Mulai</label>
                                                <input type="date" name="tanggal_mulai" x-model="mulai" @change="hasil = null"
                                                       class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Tanggal Selesai</label>
                                                <input type="date" name="tanggal_selesai" x-model="selesai" @change="hasil = null"
                                                       class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                            </div>
                                        </div>

                                        <div x-show="jenisTanggal !== 'semua'" x-cloak class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Pintasan:</span>
                                            <button type="button" @click="pilihTanggal('hariIni')" class="rounded-lg border-2 border-[#0047d6]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#0047d6] hover:bg-[#0047d6]/10">Hari Ini</button>
                                            <button type="button" @click="pilihTanggal('kemarin')" class="rounded-lg border-2 border-[#0047d6]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#0047d6] hover:bg-[#0047d6]/10">Kemarin</button>
                                            <button type="button" @click="pilihTanggal('7hari')" class="rounded-lg border-2 border-[#0047d6]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#0047d6] hover:bg-[#0047d6]/10">7 Hari Terakhir</button>
                                            <button type="button" @click="pilihTanggal('bulanIni')" class="rounded-lg border-2 border-[#0047d6]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#0047d6] hover:bg-[#0047d6]/10">Bulan Ini</button>
                                            <button type="button" @click="pilihTanggal('bulanLalu')" class="rounded-lg border-2 border-[#0047d6]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#0047d6] hover:bg-[#0047d6]/10">Bulan Lalu</button>
                                        </div>

                                        <p x-show="jenisTanggal==='semua'" x-cloak class="mt-2 text-[11px] font-bold text-[#cf202f]">Seluruh riwayat observasi siswa terpilih akan diproses (tanpa batas tanggal).</p>
                                    </div>

                                    {{-- Sumber status yang ikut disetujui --}}
                                    <div>
                                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Observasi Yang Ikut Divalidasi</label>
                                        <select name="sumber" x-model="sumber" @change="hasil = null"
                                                class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                            <option value="semua">Semua yang belum tervalidasi (Draft + Diajukan)</option>
                                            <option value="diajukan">Hanya yang berstatus Diajukan</option>
                                            <option value="draft">Hanya yang berstatus Draft</option>
                                        </select>
                                        <p class="mt-1 text-[11px] font-medium text-[#5b616e]">Pilihan ini hanya berpengaruh pada aksi <span class="font-bold">Setujui</span>. Pembatalan selalu menyasar observasi yang berstatus <span class="font-bold">Tervalidasi</span>.</p>
                                    </div>

                                    {{-- Pratinjau --}}
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-xs font-bold text-gray-700">Cakupan: <span class="font-medium text-[#5b616e]" x-text="labelCakupan"></span></p>
                                            <button type="button" @click="periksa()" :disabled="memuat" :class="memuat ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#0047d6]/10'"
                                                    class="rounded-lg border-2 border-[#0047d6]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#0047d6]">Periksa Dulu</button>
                                        </div>

                                        <p x-show="memuat" x-cloak class="mt-2 text-xs font-medium text-[#5b616e]">Menghitung jumlah lembar observasi...</p>
                                        <p x-show="pesanError" x-cloak x-text="pesanError" class="mt-2 text-xs font-bold text-[#cf202f]"></p>

                                        <template x-if="hasil">
                                            <div class="mt-2">
                                                <div class="grid grid-cols-2 gap-2 text-center sm:grid-cols-4">
                                                    <div class="rounded-lg bg-white px-2 py-1.5">
                                                        <p class="text-[10px] font-bold uppercase text-[#5b616e]">Total Observasi</p>
                                                        <p class="text-base font-extrabold text-black" x-text="hasil.ringkasan.total"></p>
                                                    </div>
                                                    <div class="rounded-lg bg-white px-2 py-1.5">
                                                        <p class="text-[10px] font-bold uppercase text-[#5b616e]">Draft</p>
                                                        <p class="text-base font-extrabold text-[#5b616e]" x-text="hasil.ringkasan.draft"></p>
                                                    </div>
                                                    <div class="rounded-lg bg-white px-2 py-1.5">
                                                        <p class="text-[10px] font-bold uppercase text-[#d98200]">Diajukan</p>
                                                        <p class="text-base font-extrabold text-[#d98200]" x-text="hasil.ringkasan.diajukan"></p>
                                                    </div>
                                                    <div class="rounded-lg bg-white px-2 py-1.5">
                                                        <p class="text-[10px] font-bold uppercase text-[#05b169]">Tervalidasi</p>
                                                        <p class="text-base font-extrabold text-[#05b169]" x-text="hasil.ringkasan.tervalidasi"></p>
                                                    </div>
                                                </div>

                                                <p class="mt-2 text-[11px] font-medium text-[#5b616e]">
                                                    Siswa terjaring: <span class="font-bold text-black" x-text="hasil.jumlah_siswa"></span> &middot;
                                                    belum tervalidasi: <span class="font-bold text-[#cf202f]" x-text="hasil.ringkasan.belum"></span> lembar
                                                </p>

                                                <div x-show="hasil.daftar.length" x-cloak class="mt-2 max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white">
                                                    <table class="w-full text-left text-[11px]">
                                                        <thead class="bg-gray-50 text-[10px] font-bold uppercase text-[#5b616e]">
                                                            <tr>
                                                                <th class="px-2 py-1.5">Siswa</th>
                                                                <th class="px-2 py-1.5 text-center">Total</th>
                                                                <th class="px-2 py-1.5 text-center">Draft</th>
                                                                <th class="px-2 py-1.5 text-center">Diajukan</th>
                                                                <th class="px-2 py-1.5 text-center">Tervalidasi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <template x-for="row in hasil.daftar" :key="row.nisn">
                                                                <tr class="border-t border-gray-100">
                                                                    <td class="px-2 py-1.5">
                                                                        <span class="font-bold text-black" x-text="row.name"></span>
                                                                        <span class="text-[#5b616e]" x-text="' (' + row.nisn + ')'"></span>
                                                                    </td>
                                                                    <td class="px-2 py-1.5 text-center font-bold" x-text="row.total"></td>
                                                                    <td class="px-2 py-1.5 text-center text-[#5b616e]" x-text="row.draft"></td>
                                                                    <td class="px-2 py-1.5 text-center text-[#d98200]" x-text="row.diajukan"></td>
                                                                    <td class="px-2 py-1.5 text-center text-[#05b169]" x-text="row.tervalidasi"></td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p x-show="mode==='semua' && hasil.jumlah_siswa > hasil.daftar.length" x-cloak class="mt-1 text-[10px] font-medium text-[#5b616e]">Tabel hanya menampilkan 50 siswa teratas, tetapi aksi tetap berlaku untuk seluruh siswa yang terjaring.</p>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Tombol aksi --}}
                                    <div class="flex flex-wrap justify-end gap-2 pt-1">
                                        <button type="button" @click="bukaMassal = false" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Tutup</button>
                                        <button type="button" @click="kirim('batalkan')" :disabled="!bolehKirim" :class="!bolehKirim ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#cf202f]/5'"
                                                class="rounded-xl border-2 border-[#cf202f] bg-white px-4 py-2 text-sm font-bold text-[#cf202f]">Batalkan Validasi</button>
                                        <button type="button" @click="kirim('setujui')" :disabled="!bolehKirim" :class="!bolehKirim ? 'opacity-40 cursor-not-allowed' : 'hover:opacity-90'"
                                                class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white">Setujui (Validasi)</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
                {{-- FILTER FORM --}}
                <form method="GET" action="{{ route('admin.evaluasi.observasi') }}" class="mb-6">
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
                        {{-- Filter status PKL siswa: aktif / belum / selesai --}}
                        <div class="w-full md:w-48">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status PKL</label>
                            <select name="status_pkl" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status PKL</option>
                                <option value="aktif" @selected(request('status_pkl') === 'aktif')>Aktif</option>
                                <option value="belum" @selected(request('status_pkl') === 'belum')>Belum</option>
                                <option value="selesai" @selected(request('status_pkl') === 'selesai')>Selesai</option>
                            </select>
                        </div>
                        <div class="w-full md:w-48">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Validasi</label>
                            <select name="status" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="1" @selected(request('status') === '1')>Sudah Divalidasi</option>
                                <option value="0" @selected(request('status') === '0')>Belum (Draft)</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Cari</button>
                            <a href="{{ route('admin.evaluasi.observasi') }}" class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                {{-- ============================================================= --}}
                {{-- TABEL DESKTOP / LAPTOP (>= lg): tampilkan SEMUA informasi   --}}
                {{-- ============================================================= --}}
                <div class="hidden lg:block overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1600px] text-left text-sm table-fixed border-collapse">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3.5 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3.5 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3.5 font-bold w-44">Siswa</th>
                                <th class="px-4 py-3.5 font-bold w-28">NISN</th>
                                <th class="px-4 py-3.5 font-bold w-44">Guru Pembimbing</th>
                                <th class="px-4 py-3.5 font-bold w-48">Pekerjaan/Projek</th>
                                <th class="px-4 py-3.5 font-bold w-80">Permasalahan</th>
                                <th class="px-4 py-3.5 font-bold w-80">Solusi Pemecahan</th>
                                <th class="px-4 py-3.5 text-center font-bold w-36">Status</th>
                                <th class="px-4 py-3.5 text-center font-bold w-52">Foto &amp; Paraf</th>
                                <th class="px-4 py-3.5 text-center font-bold w-24">Cetak</th>
                                <th class="px-4 py-3.5 text-center font-bold w-44">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($observasi as $obs)
                                @php $poin = $obs->items; @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $observasi->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ optional($obs->hari_tanggal)->format('d M Y') }}</td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $obs->user->name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $obs->user->nisn }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">{{ $obs->guru?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">{{ $obs->pekerjaan_projek ?? '-' }}</td>

                                    {{-- PERMASALAHAN --}}
                                    <td class="px-4 py-3 text-black whitespace-normal break-words">
                                        @if($poin->count())
                                            <div x-data="{ open: false }">
                                                <div class="flex items-start gap-1.5">
                                                    <span class="font-bold text-[#0047d6] flex-shrink-0">1.</span>
                                                    <span class="font-medium break-words">{{ $poin->first()->permasalahan }}</span>
                                                </div>
                                                @if($poin->count() > 1)
                                                    <button type="button" @click="open = !open"
                                                            class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                        <span x-show="!open">+ {{ $poin->count() - 1 }} lainnya</span>
                                                        <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    </button>
                                                    <ol start="2" x-show="open" x-cloak x-transition class="mt-2 list-decimal list-inside space-y-1 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                        @foreach($poin->slice(1) as $poinLainnya)
                                                            <li class="break-words">{{ $poinLainnya->permasalahan }}</li>
                                                        @endforeach
                                                    </ol>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>

                                    {{-- SOLUSI --}}
                                    <td class="px-4 py-3 text-black whitespace-normal break-words">
                                        @if($poin->count())
                                            <div x-data="{ open: false }">
                                                <div class="flex items-start gap-1.5">
                                                    <span class="font-bold text-[#0047d6] flex-shrink-0">1.</span>
                                                    <span class="font-medium break-words">{{ $poin->first()->solusi }}</span>
                                                </div>
                                                @if($poin->count() > 1)
                                                    <button type="button" @click="open = !open"
                                                            class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                        <span x-show="!open">+ {{ $poin->count() - 1 }} lainnya</span>
                                                        <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    </button>
                                                    <ol start="2" x-show="open" x-cloak x-transition class="mt-2 list-decimal list-inside space-y-1 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                        @foreach($poin->slice(1) as $poinLainnya)
                                                            <li class="break-words">{{ $poinLainnya->solusi }}</li>
                                                        @endforeach
                                                    </ol>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-4 py-3 text-center whitespace-normal">
                                        <div class="inline-flex flex-col items-center justify-center min-w-[110px]">
                                            @if ($obs->status === 'tervalidasi')
                                                <span class="inline-flex items-center justify-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white w-full shadow-sm">Tervalidasi</span>
                                                @if($obs->validated_at)
                                                    <p class="mt-1 text-[10px] font-medium text-[#5b616e] whitespace-nowrap">{{ \Carbon\Carbon::parse($obs->validated_at)->format('d M Y') }}</p>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center justify-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white w-full shadow-sm">Draft</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- FOTO & PARAF DIGITAL (bisa diperbesar + diunduh) --}}
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                            @if ($obs->foto_dokumentasi)
                                                <a href="{{ asset('storage/' . $obs->foto_dokumentasi) }}" download target="_blank" rel="noopener"
                                                   class="inline-flex items-center justify-center rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 w-28">Bukti Foto</a>
                                            @endif

                                            <x-paraf-instruktur :ttd="$obs->ttd_instruktur"
                                                                :nama="$obs->ttd_instruktur_nama ?: ($obs->user->instruktur->name ?? null)"
                                                                :waktu="$obs->ttd_instruktur_signed_at"
                                                                :foto-lama="$obs->foto_lembar_observasi"
                                                                :tinggi="38"
                                                                judul="Paraf Instruktur — {{ $obs->user->name ?? '-' }}"
                                                                unduh-nama="paraf-instruktur-observasi-{{ $obs->id }}"
                                                                kosong="Belum ada paraf instruktur" />

                                            <x-paraf-instruktur :ttd="$obs->ttd_guru"
                                                                :nama="$obs->ttd_guru_nama ?: ($obs->guru->name ?? null)"
                                                                :waktu="$obs->ttd_guru_signed_at"
                                                                :tinggi="38"
                                                                judul="Paraf Guru Pembimbing — {{ $obs->user->name ?? '-' }}"
                                                                label="Paraf guru pembimbing"
                                                                peran-label="Nama guru"
                                                                unduh-nama="paraf-guru-observasi-{{ $obs->id }}"
                                                                kosong="Belum ada paraf guru" />
                                        </div>
                                    </td>

                                    {{-- CETAK --}}
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('cetak.observasi', $obs->user_id) }}" target="_blank"
                                           class="inline-flex items-center justify-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">PDF</a>
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-center gap-2">
                                            <button type="button"
                                                    @click="konfirmValidasi(@js([
                                                        'url' => route('admin.evaluasi.observasi.validasi', $obs->id),
                                                        'nama' => $obs->user->name,
                                                        'nisn' => $obs->user->nisn,
                                                        'guru_nama' => $obs->ttd_guru_nama ?: ($obs->guru->name ?? null),
                                                        'instruktur_nama' => $obs->ttd_instruktur_nama ?: ($obs->user->instruktur->name ?? null),
                                                        'ttd_guru_url' => $obs->ttd_guru ? asset('storage/'.$obs->ttd_guru) : null,
                                                        'ttd_instruktur_url' => $obs->ttd_instruktur ? asset('storage/'.$obs->ttd_instruktur) : null,
                                                        'foto_dokumentasi_url' => $obs->foto_dokumentasi ? asset('storage/'.$obs->foto_dokumentasi) : null,
                                                        'foto_lembar_url' => $obs->foto_lembar_observasi ? asset('storage/'.$obs->foto_lembar_observasi) : null,
                                                    ]))"
                                                    class="inline-flex w-full items-center justify-center rounded-xl bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049457]">
                                                {{ $obs->status === 'tervalidasi' ? 'Validasi Ulang' : 'Validasi' }}
                                            </button>
                                            @if ($obs->status === 'tervalidasi')
                                                <button type="button" @click="konfirmBatal(@js(route('admin.evaluasi.observasi.batal', $obs->id)))"
                                                        class="inline-flex w-full items-center justify-center rounded-xl bg-[#d98200]/10 px-3 py-1.5 text-xs font-bold text-[#d98200] transition hover:bg-[#d98200]/20">
                                                    Batalkan Validasi
                                                </button>
                                            @endif
                                            <div class="flex items-center justify-center gap-2 w-full">
                                                <button type="button"
                                                        @click="edit(@js([
                                                            'id' => $obs->id,
                                                            'user_id' => $obs->user_id,
                                                            'hari_tanggal' => optional($obs->hari_tanggal)->format('Y-m-d'),
                                                            'pekerjaan_projek' => $obs->pekerjaan_projek,
                                                            'items' => $obs->items->map(fn($it) => ['id' => $it->id, 'permasalahan' => $it->permasalahan, 'solusi' => $it->solusi])->values(),
                                                        ]))"
                                                        class="flex-1 rounded-lg border-2 border-[#0047d6]/30 px-2 py-1.5 text-xs font-bold text-[#0047d6] text-center hover:bg-[#0047d6]/5">Edit</button>
                                                <button type="button"
                                                        @click="konfirmHapus(@js(route('admin.evaluasi.observasi.destroy', $obs->id)))"
                                                        class="flex-1 rounded-lg border-2 border-red-200 px-2 py-1.5 text-xs font-bold text-red-600 text-center hover:bg-red-50">Hapus</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada data observasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ============================================================= --}}
                {{-- TABEL MOBILE / TABLET (< lg): hanya Nama + tombol Detail    --}}
                {{-- ============================================================= --}}
                <div class="lg:hidden overflow-hidden rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-3 py-3 text-center w-10 font-bold">No</th>
                                <th class="px-3 py-3 font-bold">Siswa</th>
                                <th class="px-3 py-3 text-center w-28 font-bold">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($observasi as $obs)
                                <tr class="align-middle transition hover:bg-[#0047d6]/5">
                                    <td class="px-3 py-4 text-center font-semibold text-black">{{ $observasi->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-4 text-black">
                                        <div class="font-bold leading-snug break-words">{{ $obs->user->name }}</div>
                                        <div class="text-[11px] text-[#5b616e] mt-0.5 font-mono">NISN: {{ $obs->user->nisn }}</div>
                                        @if ($obs->status === 'tervalidasi')
                                            <span class="mt-1 inline-block rounded-full bg-[#05b169] px-2.5 py-0.5 text-[10px] font-bold text-white">Tervalidasi</span>
                                        @else
                                            <span class="mt-1 inline-block rounded-full bg-[#d98200] px-2.5 py-0.5 text-[10px] font-bold text-white">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <button type="button"
                                                @click="lihatDetail(@js([
                                                    'id' => $obs->id,
                                                    'user_id' => $obs->user_id,
                                                    'nama' => $obs->user->name,
                                                    'nisn' => $obs->user->nisn,
                                                    'guru' => $obs->guru?->name ?? '-',
                                                    'hari_tanggal' => optional($obs->hari_tanggal)->format('Y-m-d'),
                                                    'tanggal_label' => optional($obs->hari_tanggal)->format('d M Y') ?? '-',
                                                    'pekerjaan_projek' => $obs->pekerjaan_projek ?? '-',
                                                    'status' => $obs->status,
                                                    'validated_at' => $obs->validated_at ? \Carbon\Carbon::parse($obs->validated_at)->format('d M Y') : null,
                                                    'items' => $obs->items->map(fn($it) => ['id' => $it->id, 'permasalahan' => $it->permasalahan, 'solusi' => $it->solusi])->values(),
                                                    'foto_dokumentasi_url' => $obs->foto_dokumentasi ? asset('storage/'.$obs->foto_dokumentasi) : null,
                                                    'foto_lembar_url' => $obs->foto_lembar_observasi ? asset('storage/'.$obs->foto_lembar_observasi) : null,
                                                    'ttd_guru_url' => $obs->ttd_guru ? asset('storage/'.$obs->ttd_guru) : null,
                                                    'ttd_guru_waktu' => $obs->ttd_guru_signed_at ? $obs->ttd_guru_signed_at->format('d/m/Y H:i') : null,
                                                    'ttd_instruktur_url' => $obs->ttd_instruktur ? asset('storage/'.$obs->ttd_instruktur) : null,
                                                    'ttd_instruktur_waktu' => $obs->ttd_instruktur_signed_at ? $obs->ttd_instruktur_signed_at->format('d/m/Y H:i') : null,
                                                    'guru_nama' => $obs->ttd_guru_nama ?: ($obs->guru->name ?? null),
                                                    'instruktur_nama' => $obs->ttd_instruktur_nama ?: ($obs->user->instruktur->name ?? null),
                                                    'cetak_url' => route('cetak.observasi', $obs->user_id),
                                                    'validasi_url' => route('admin.evaluasi.observasi.validasi', $obs->id),
                                                    'batal_url' => route('admin.evaluasi.observasi.batal', $obs->id),
                                                    'destroy_url' => route('admin.evaluasi.observasi.destroy', $obs->id),
                                                ]))"
                                                class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#0047d6] px-3 py-2 text-xs font-bold text-white transition active:scale-95 hover:bg-[#0038aa]">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada data observasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-4">{{ $observasi->links() }}</div>
            </div>
        </div>

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
                <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b-2 border-[#0047d6]/10 bg-white px-5 py-4">
                    <div>
                        <h3 class="text-base font-bold text-black" x-text="detailData.nama"></h3>
                        <p class="text-xs font-mono text-[#5b616e]">NISN: <span x-text="detailData.nisn"></span></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-bold"
                              :class="detailData.status === 'tervalidasi' ? 'bg-[#05b169] text-white' : 'bg-[#d98200] text-white'"
                              x-text="detailData.status === 'tervalidasi' ? 'Tervalidasi' : 'Draft'"></span>
                        <template x-if="detailData.status === 'tervalidasi' && detailData.validated_at">
                            <span class="ml-2 text-[11px] font-medium text-[#5b616e]" x-text="'Divalidasi: ' + detailData.validated_at"></span>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p>
                            <p class="mt-0.5 text-sm font-medium text-black" x-text="detailData.tanggal_label"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Guru Pembimbing</p>
                            <p class="mt-0.5 text-sm font-medium text-black" x-text="detailData.guru"></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Pekerjaan / Projek</p>
                        <p class="mt-0.5 text-sm font-semibold text-black" x-text="detailData.pekerjaan_projek"></p>
                    </div>

                    {{-- POIN PERMASALAHAN & SOLUSI --}}
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Permasalahan &amp; Solusi</p>
                        <template x-if="!detailData.items || detailData.items.length === 0">
                            <p class="text-sm italic text-[#5b616e]">-</p>
                        </template>
                        <div class="space-y-2">
                            <template x-for="(it, i) in detailData.items" :key="i">
                                <div class="rounded-xl border-2 border-[#0047d6]/15 p-3">
                                    <p class="text-xs font-bold text-[#0047d6]" x-text="'Poin ' + (i + 1)"></p>
                                    <div class="mt-1">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-[#5b616e]">Permasalahan</p>
                                        <p class="text-sm text-black whitespace-pre-line" x-text="it.permasalahan || '-'"></p>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-[#5b616e]">Solusi</p>
                                        <p class="text-sm text-black whitespace-pre-line" x-text="it.solusi || '-'"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- FOTO --}}
                    <template x-if="detailData.foto_dokumentasi_url || detailData.foto_lembar_url">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Foto</p>
                            <div class="grid grid-cols-2 gap-2">
                                <template x-if="detailData.foto_dokumentasi_url">
                                    <a :href="detailData.foto_dokumentasi_url" download target="_blank" class="block">
                                       
                                        <span  class="inline-flex items-center gap-1 rounded-xl bg-[#d98200] px-4 py-2 text-sm font-bold text-white transition hover:opacity-90">Lihat Dokumentasi</span>
                                    </a>
                                </template>
                                <template x-if="detailData.foto_lembar_url">
                                    <a :href="detailData.foto_lembar_url" target="_blank" class="block">
                                       
                                        <span  class="inline-flex items-center gap-1 rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:opacity-90">Lihat Lembar Observasi</span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- PARAF DIGITAL: bisa dilihat & diunduh --}}
                    <template x-if="detailData.ttd_instruktur_url || detailData.ttd_guru_url">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Paraf Digital</p>
                            <div class="grid grid-cols-2 gap-2">
                                <template x-if="detailData.ttd_instruktur_url">
                                    <div class="rounded-xl border-2 border-[#05b169]/25 bg-white p-2 text-center">
                                        <img :src="detailData.ttd_instruktur_url" alt="Paraf instruktur" class="mx-auto h-14 w-auto object-contain">
                                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-[#05b169]">Instruktur</p>
                                        <p class="text-[10px] font-medium text-[#5b616e]" x-text="detailData.instruktur_nama || '-'"></p>
                                        <p class="text-[10px] text-[#5b616e]" x-text="detailData.ttd_instruktur_waktu || ''"></p>
                                        <div class="mt-1.5 flex justify-center gap-1.5">
                                            <a :href="detailData.ttd_instruktur_url" target="_blank" rel="noopener"
                                               class="rounded-lg border-2 border-[#0047d6]/30 px-2 py-1 text-[10px] font-bold text-[#0047d6] hover:bg-[#0047d6]/10">Buka</a>
                                            <a :href="detailData.ttd_instruktur_url" :download="'paraf-instruktur-observasi-' + (detailData.id || '') + '.png'"
                                               class="rounded-lg bg-[#05b169] px-2 py-1 text-[10px] font-bold text-white hover:opacity-90">Unduh</a>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="detailData.ttd_guru_url">
                                    <div class="rounded-xl border-2 border-[#0047d6]/25 bg-white p-2 text-center">
                                        <img :src="detailData.ttd_guru_url" alt="Paraf guru pembimbing" class="mx-auto h-14 w-auto object-contain">
                                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wide text-[#0047d6]">Guru Pembimbing</p>
                                        <p class="text-[10px] font-medium text-[#5b616e]" x-text="detailData.guru_nama || '-'"></p>
                                        <p class="text-[10px] text-[#5b616e]" x-text="detailData.ttd_guru_waktu || ''"></p>
                                        <div class="mt-1.5 flex justify-center gap-1.5">
                                            <a :href="detailData.ttd_guru_url" target="_blank" rel="noopener"
                                               class="rounded-lg border-2 border-[#0047d6]/30 px-2 py-1 text-[10px] font-bold text-[#0047d6] hover:bg-[#0047d6]/10">Buka</a>
                                            <a :href="detailData.ttd_guru_url" :download="'paraf-guru-observasi-' + (detailData.id || '') + '.png'"
                                               class="rounded-lg bg-[#0047d6] px-2 py-1 text-[10px] font-bold text-white hover:opacity-90">Unduh</a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 space-y-2 border-t-2 border-[#0047d6]/10 bg-white px-5 py-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="detailOpen = false; konfirmValidasi(detailData)"
                                class="flex-1 min-w-[110px] rounded-xl bg-[#05b169] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#049457]"
                                x-text="detailData.status === 'tervalidasi' ? 'Validasi Ulang' : 'Validasi'"></button>
                        <template x-if="detailData.status === 'tervalidasi'">
                            <button type="button" @click="detailOpen = false; konfirmBatal(detailData.batal_url)"
                                    class="flex-1 min-w-[110px] rounded-xl bg-[#d98200]/10 px-3 py-2.5 text-xs font-bold text-[#d98200] transition hover:bg-[#d98200]/20">Batalkan Validasi</button>
                        </template>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a :href="detailData.cetak_url" target="_blank"
                           class="flex-1 min-w-[90px] rounded-xl border-2 border-[#0047d6] px-3 py-2.5 text-center text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6] hover:text-white">Cetak PDF</a>
                        <button type="button" @click="editDariDetail()"
                                class="flex-1 min-w-[90px] rounded-xl bg-[#0047d6] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">Edit</button>
                        <button type="button" @click="detailOpen = false; konfirmHapus(detailData.destroy_url)"
                                class="flex-1 min-w-[90px] rounded-xl bg-[#cf202f] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#b01926]">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL GLOBAL: VALIDASI (upload foto)                            --}}
        {{-- ================================================================= --}}
        <div x-show="validasiOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4"
             @keydown.escape.window="validasiOpen = false">
            <div x-show="validasiOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-6 text-left shadow-xl" @click.outside="validasiOpen = false">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-black">Validasi Lembar Observasi</h3>
                    <button type="button" @click="validasiOpen = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                </div>
                <p class="mb-4 text-sm text-[#5b616e]">
                    Unggah <span class="font-semibold text-black">bukti foto observasi</span>, lalu bubuhkan
                    <span class="font-semibold text-black">paraf digital</span> guru pembimbing &amp; instruktur langsung di layar
                    (keduanya <span class="font-bold text-black">opsional</span>, bisa menyusul).
                    Setelah divalidasi, hasil cetak PDF menampilkan keterangan <span class="font-bold text-black">SUDAH DIVALIDASI</span>.
                </p>
                <form :action="validasiUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <template x-if="validasi.nama">
                        <div class="rounded-xl bg-[#0047d6]/5 px-3 py-2 text-xs font-semibold text-black">
                            Siswa: <span x-text="validasi.nama"></span>
                            <span class="font-mono text-[11px] font-medium text-[#5b616e]" x-text="validasi.nisn ? '(NISN ' + validasi.nisn + ')' : ''"></span>
                        </div>
                    </template>

                    <div>
                        <label class="block text-sm font-bold text-black mb-1">
                            Bukti Foto Observasi
                            <span class="text-red-500" x-show="!validasi.foto_dokumentasi_url">*</span>
                        </label>
                        <input type="file" name="foto_dokumentasi" accept="image/*" :required="!validasi.foto_dokumentasi_url"
                               class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#0047d6] file:px-4 file:py-2 file:text-white file:font-bold">
                        <p class="mt-1 text-xs text-gray-500"
                           x-text="validasi.foto_dokumentasi_url ? 'Sudah ada foto tersimpan. Kosongkan bila tidak ingin menggantinya.' : 'Wajib. Format JPG/JPEG/PNG, maksimal 3 MB.'"></p>
                    </div>

                    {{-- PARAF DIGITAL GURU PEMBIMBING (OPSIONAL) --}}
                    <div class="rounded-xl border-2 border-[#0047d6]/15 p-3">
                        <template x-if="validasi.ttd_guru_url">
                            <div class="mb-2 rounded-lg bg-[#0047d6]/5 p-2 text-center">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-[#0047d6]">Paraf guru tersimpan</p>
                                <img :src="validasi.ttd_guru_url" alt="Paraf guru pembimbing" class="mx-auto mt-1 h-12 w-auto object-contain">
                                <label class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#cf202f]">
                                    <input type="checkbox" name="hapus_ttd_guru" value="1" class="rounded border-[#cf202f]/40 text-[#cf202f] focus:ring-[#cf202f]">
                                    Hapus paraf guru ini
                                </label>
                            </div>
                        </template>
                        <x-ttd-pad name="ttd_guru" label="Paraf Digital Guru Pembimbing (Opsional)" :tinggi="150" :wajib="false" />
                        <p class="mt-1 text-[11px] text-[#5b616e]">Diparaf oleh: <span class="font-semibold text-black" x-text="namaGuruValidasi"></span></p>
                    </div>

                    {{-- PARAF DIGITAL INSTRUKTUR (OPSIONAL) --}}
                    <div class="rounded-xl border-2 border-[#05b169]/20 p-3">
                        <template x-if="validasi.ttd_instruktur_url">
                            <div class="mb-2 rounded-lg bg-[#05b169]/5 p-2 text-center">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-[#05b169]">Paraf instruktur tersimpan</p>
                                <img :src="validasi.ttd_instruktur_url" alt="Paraf instruktur" class="mx-auto mt-1 h-12 w-auto object-contain">
                                <label class="mt-1 inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#cf202f]">
                                    <input type="checkbox" name="hapus_ttd_instruktur" value="1" class="rounded border-[#cf202f]/40 text-[#cf202f] focus:ring-[#cf202f]">
                                    Hapus paraf instruktur ini
                                </label>
                            </div>
                        </template>
                        <x-ttd-pad name="ttd_instruktur" label="Paraf Digital Instruktur (Opsional)" :tinggi="150" :wajib="false" />
                        <p class="mt-1 text-[11px] text-[#5b616e]">Diparaf oleh: <span class="font-semibold text-black" x-text="namaInstrukturValidasi"></span></p>
                    </div>

                    <template x-if="validasi.foto_lembar_url">
                        <div class="rounded-xl bg-[#d98200]/5 px-3 py-2 text-[11px] font-medium text-[#5b616e]">
                            Data lama: <a :href="validasi.foto_lembar_url" target="_blank" rel="noopener" class="font-bold text-[#d98200] underline">foto lembar berparaf</a>
                            masih tersimpan dan tetap bisa dilihat/dicetak.
                        </div>
                    </template>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="validasiOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Batal</button>
                        <button type="submit" class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049457]">Simpan &amp; Validasi</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL GLOBAL: BATAL VALIDASI                                    --}}
        {{-- ================================================================= --}}
        <div x-show="batalOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
             @keydown.escape.window="batalOpen = false">
            <div x-show="batalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="batalOpen = false">
                <h3 class="text-base font-bold text-black">Batalkan Validasi</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Status lembar observasi ini akan kembali ke <span class="font-bold text-black">draft</span>. Foto yang sudah diunggah tetap disimpan.</p>
                <form :action="batalUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('PUT')
                    <button type="button" @click="batalOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#d98200] px-4 py-2 text-sm font-bold text-white hover:bg-[#b06a00]">Ya, Batalkan</button>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL GLOBAL: TAMBAH / EDIT OBSERVASI                           --}}
        {{-- ================================================================= --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4" @keydown.escape.window="open = false">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-2xl sm:rounded-2xl sm:p-6 max-h-[90vh] overflow-y-auto" @click.outside="open = false">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-black" x-text="mode === 'create' ? 'Tambah Observasi' : 'Edit Observasi'"></h3>
                    <button type="button" @click="open = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                </div>
                <form :action="actionUrl" method="POST" @submit="simpan($event)" class="space-y-3">
                    @csrf
                    <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="user_id" :value="siswaCocok ? siswaCocok.id : ''">

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">NISN Siswa</label>
                        <input type="text" x-model="form.nisn" placeholder="Masukkan NISN siswa"
                               class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <template x-if="siswaCocok"><p class="mt-1 text-xs font-semibold text-[#05b169]">&#10003; <span x-text="siswaCocok.name"></span></p></template>
                        <template x-if="form.nisn.trim() !== '' && !siswaCocok"><p class="mt-1 text-xs font-semibold text-[#cf202f]">NISN tidak cocok</p></template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Tanggal</label>
                            <input type="date" name="hari_tanggal" x-model="form.hari_tanggal" required
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Pekerjaan / Projek</label>
                            <input type="text" name="pekerjaan_projek" x-model="form.pekerjaan_projek"
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black">Poin Permasalahan &amp; Solusi</label>
                            <button type="button" @click="tambahItem()" class="rounded-lg bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] hover:bg-[#0047d6]/20">Tambah poin</button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(it, i) in form.items" :key="i">
                                <div class="rounded-xl border-2 border-[#0047d6]/15 p-3">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-xs font-bold text-[#0047d6]" x-text="'Poin ' + (i + 1)"></span>
                                        <button type="button" @click="hapusItem(i)" x-show="form.items.length > 1" class="rounded-lg border-2 border-red-200 px-2 py-1 text-xs font-bold text-red-600 hover:bg-red-50">Hapus poin</button>
                                    </div>
                                    <input type="hidden" :name="'items[' + i + '][id]'" :value="it.id ?? ''">
                                    <textarea :name="'items[' + i + '][permasalahan]'" x-model="it.permasalahan" rows="2" placeholder="Permasalahan..."
                                              class="mb-2 w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                                    <textarea :name="'items[' + i + '][solusi]'" x-model="it.solusi" rows="2" placeholder="Solusi pemecahan..."
                                              class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-xl bg-[#0047d6]/5 px-3 py-2.5 text-xs font-medium text-[#5b616e]">
                        Foto dokumentasi &amp; foto lembar observasi diunggah saat proses <span class="font-bold text-black">Validasi</span> (tombol hijau di kolom Aksi / dalam Detail).
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''" class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Simpan</button>
                        <button type="button" @click="open = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL GLOBAL: CONFIRM HAPUS                                     --}}
        {{-- ================================================================= --}}
        <div x-show="hapusOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="hapusOpen = false">
            <div x-show="hapusOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false">
                <h3 class="text-base font-bold text-black">Hapus Observasi</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Yakin ingin menghapus lembar observasi ini beserta seluruh poinnya? Tindakan ini tidak dapat dibatalkan.</p>
                <form :action="hapusUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#cf202f] px-4 py-2 text-sm font-bold text-white hover:bg-[#b01926]">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ALPINE JS HANDLER --}}
    <script>
        window.observasiCrud = function () {
            const daftarSiswa = @js($siswaList);
            const today = @js(date('Y-m-d'));
            const storeUrl = @js(route('admin.evaluasi.observasi.store'));
            const kosong = () => ({ id: null, nisn: '', hari_tanggal: today, pekerjaan_projek: '', items: [{ id: null, permasalahan: '', solusi: '' }] });

            return {
                open: false,
                mode: 'create',
                form: kosong(),
                hapusOpen: false,
                hapusUrl: '',
                detailOpen: false,
                detailData: {},
                validasiOpen: false,
                validasiUrl: '',
                validasi: {},
                batalOpen: false,
                batalUrl: '',

                init() {
                    this.$watch('open',        () => this.kunciScroll());
                    this.$watch('hapusOpen',   () => this.kunciScroll());
                    this.$watch('detailOpen',  () => this.kunciScroll());
                    this.$watch('validasiOpen',() => this.kunciScroll());
                    this.$watch('batalOpen',   () => this.kunciScroll());
                },
                kunciScroll() {
                    document.body.style.overflow = (this.open || this.hapusOpen || this.detailOpen || this.validasiOpen || this.batalOpen) ? 'hidden' : '';
                },

                get siswaCocok() {
                    const nisn = String(this.form.nisn || '').trim();
                    if (!nisn) return null;
                    return daftarSiswa.find(s => String(s.nisn).trim() === nisn) || null;
                },
                get actionUrl() { return this.mode === 'create' ? storeUrl : storeUrl + '/' + this.form.id; },

                tambah() { this.mode = 'create'; this.form = kosong(); this.open = true; },

                edit(d) {
                    const s = daftarSiswa.find(x => String(x.id) === String(d.user_id));
                    let items = Array.isArray(d.items) ? d.items.map(it => ({ id: it.id, permasalahan: it.permasalahan || '', solusi: it.solusi || '' })) : [];
                    if (items.length === 0) items = [{ id: null, permasalahan: '', solusi: '' }];
                    this.mode = 'edit';
                    this.form = { id: d.id, nisn: s ? String(s.nisn) : '', hari_tanggal: d.hari_tanggal, pekerjaan_projek: d.pekerjaan_projek || '', items: items };
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
                        user_id: d.user_id,
                        hari_tanggal: d.hari_tanggal,
                        pekerjaan_projek: d.pekerjaan_projek === '-' ? '' : d.pekerjaan_projek,
                        items: d.items,
                    });
                },

                tambahItem() { this.form.items.push({ id: null, permasalahan: '', solusi: '' }); },
                hapusItem(i) { this.form.items.splice(i, 1); },

                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },
                /* Nama otomatis di bawah kotak paraf (tanpa isian manual). */
                get namaGuruValidasi() {
                    const n = this.validasi.guru_nama || '';
                    return (n && n !== '-' && n !== 'Belum Diatur') ? n : 'Guru pembimbing (nama belum diatur)';
                },
                get namaInstrukturValidasi() {
                    const n = this.validasi.instruktur_nama || '';
                    return (n && n !== '-' && n !== 'Belum Diatur') ? n : 'Instruktur (nama belum diatur admin)';
                },

                /* Bersihkan kanvas paraf supaya tidak terbawa dari baris sebelumnya. */
                resetParaf() {
                    this.$nextTick(() => {
                        document.querySelectorAll('[data-ttd] [data-ttd-clear]').forEach((tombol) => tombol.click());
                    });
                },

                /* Menerima URL (string) atau objek data baris (desktop & modal detail). */
                konfirmValidasi(d) {
                    const data = (typeof d === 'string') ? { url: d } : Object.assign({}, d || {});

                    data.url = data.url || data.validasi_url || '';

                    this.validasi = data;
                    this.validasiUrl = data.url;
                    this.resetParaf();
                    this.validasiOpen = true;
                },
                konfirmBatal(url) { this.batalUrl = url; this.batalOpen = true; },
            };
        };

        /* =================================================================
           VALIDASI OBSERVASI MASSAL: beberapa NISN sekaligus / semua siswa
           (mengikuti pola halaman Monitoring Jurnal admin)
        ================================================================= */
        window.validasiObservasiMassal = function (list, urlPratinjau) {
            const tglStr = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

            return {
                bukaMassal: false,
                mode: 'nisn',            // 'nisn' (boleh banyak) atau 'semua'
                nisnInput: '',           // boleh memuat BANYAK NISN
                jenisTanggal: 'rentang', // 'tanggal' | 'rentang' | 'semua'
                tanggal: '',
                mulai: '',
                selesai: '',
                sumber: 'semua',
                semuaPeriode: false,
                list: Array.isArray(list) ? list : [],
                urlPratinjau: urlPratinjau,
                memuat: false,
                pesanError: '',
                hasil: null,

                bukaModal() {
                    const hariIni = new Date();
                    const awalBulan = new Date(hariIni.getFullYear(), hariIni.getMonth(), 1);

                    this.bukaMassal = true;
                    this.mode = 'nisn';
                    this.nisnInput = '';
                    this.jenisTanggal = 'rentang';
                    this.tanggal = tglStr(hariIni);
                    this.mulai = tglStr(awalBulan);
                    this.selesai = tglStr(hariIni);
                    this.sumber = 'semua';
                    this.semuaPeriode = false;
                    this.memuat = false;
                    this.pesanError = '';
                    this.hasil = null;
                },

                /* Daftar NISN: dipisah koma / titik koma / spasi / baris baru. */
                get daftarNisn() {
                    return [...new Set(
                        String(this.nisnInput || '').split(/[^0-9A-Za-z]+/).map((x) => x.trim()).filter((x) => x !== '')
                    )];
                },

                get nisnCocok() {
                    if (!this.list.length) return this.daftarNisn;
                    return this.daftarNisn.filter((n) => this.list.some((x) => String(x.nisn) === n));
                },

                get nisnTidakCocok() {
                    if (!this.list.length) return [];
                    return this.daftarNisn.filter((n) => !this.list.some((x) => String(x.nisn) === n));
                },

                namaNisn(n) {
                    const s = this.list.find((x) => String(x.nisn) === String(n));
                    return s ? (s.name + ' (' + n + ')') : String(n);
                },

                hapusNisn(n) {
                    this.nisnInput = this.daftarNisn.filter((x) => x !== String(n)).join(', ');
                    this.hasil = null;
                },

                kosongkanNisn() {
                    this.nisnInput = '';
                    this.hasil = null;
                    this.pesanError = '';
                },

                /* Isi semua siswa PKL aktif pada daftar. */
                isiSemuaAktif() {
                    const berNisn = this.list.filter((x) => String(x.nisn || '') !== '');
                    const aktif   = berNisn.filter((x) => String(x.statusPkl || '').toLowerCase() === 'aktif');
                    const dipakai = aktif.length ? aktif : berNisn;

                    this.nisnInput = dipakai.map((x) => String(x.nisn)).join(', ');
                    this.hasil = null;
                    this.pesanError = dipakai.length ? '' : 'Tidak ada siswa pada daftar.';
                },

                setMode(m) { this.mode = m; this.hasil = null; this.pesanError = ''; },
                setJenis(j) { this.jenisTanggal = j; this.hasil = null; },

                /* Pintasan tanggal cepat. */
                pilihTanggal(pintasan) {
                    const hariIni = new Date();

                    if (pintasan === 'hariIni') {
                        this.jenisTanggal = 'tanggal';
                        this.tanggal = tglStr(hariIni);
                    } else if (pintasan === 'kemarin') {
                        const k = new Date(hariIni);
                        k.setDate(k.getDate() - 1);
                        this.jenisTanggal = 'tanggal';
                        this.tanggal = tglStr(k);
                    } else if (pintasan === '7hari') {
                        const m = new Date(hariIni);
                        m.setDate(m.getDate() - 6);
                        this.jenisTanggal = 'rentang';
                        this.mulai = tglStr(m);
                        this.selesai = tglStr(hariIni);
                    } else if (pintasan === 'bulanIni') {
                        this.jenisTanggal = 'rentang';
                        this.mulai = tglStr(new Date(hariIni.getFullYear(), hariIni.getMonth(), 1));
                        this.selesai = tglStr(hariIni);
                    } else if (pintasan === 'bulanLalu') {
                        this.jenisTanggal = 'rentang';
                        this.mulai = tglStr(new Date(hariIni.getFullYear(), hariIni.getMonth() - 1, 1));
                        this.selesai = tglStr(new Date(hariIni.getFullYear(), hariIni.getMonth(), 0));
                    }

                    this.hasil = null;
                },

                get labelCakupan() {
                    const rapi = (s) => {
                        if (!s) return '-';
                        const p = String(s).split('-');
                        return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : String(s);
                    };

                    const siswa = this.mode === 'semua'
                        ? (this.semuaPeriode ? 'semua siswa (semua periode)' : 'semua siswa periode berjalan')
                        : (this.nisnCocok.length + ' siswa terpilih');

                    let waktu = 'seluruh riwayat observasi';
                    if (this.jenisTanggal === 'tanggal') {
                        waktu = 'tanggal ' + rapi(this.tanggal);
                    } else if (this.jenisTanggal === 'rentang') {
                        waktu = 'rentang ' + rapi(this.mulai) + ' - ' + rapi(this.selesai);
                    }

                    return siswa + ' pada ' + waktu;
                },

                get bolehKirim() {
                    if (this.memuat) return false;
                    if (this.mode === 'nisn' && !this.nisnCocok.length) return false;
                    if (this.jenisTanggal === 'tanggal' && !this.tanggal) return false;
                    if (this.jenisTanggal === 'rentang' && (!this.mulai || !this.selesai)) return false;
                    if (this.jenisTanggal === 'rentang' && this.selesai < this.mulai) return false;
                    return true;
                },

                /* Pratinjau: hitung dulu berapa lembar observasi yang terkena aksi. */
                async periksa() {
                    if (this.mode === 'nisn' && !this.daftarNisn.length) {
                        this.pesanError = 'Masukkan minimal satu NISN.';
                        return;
                    }

                    this.memuat = true;
                    this.pesanError = '';
                    this.hasil = null;

                    try {
                        const p = new URLSearchParams();
                        p.set('mode', this.mode);
                        p.set('jenis_tanggal', this.jenisTanggal);
                        p.set('nisn', this.mode === 'nisn' ? this.daftarNisn.join(',') : '');
                        p.set('tanggal', this.tanggal || '');
                        p.set('tanggal_mulai', this.mulai || '');
                        p.set('tanggal_selesai', this.selesai || '');
                        p.set('semua_periode', this.semuaPeriode ? '1' : '0');

                        const res = await fetch(this.urlPratinjau + '?' + p.toString(), {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const data = await res.json();

                        if (!res.ok || !data.ok) {
                            this.pesanError = data.pesan || 'Pratinjau gagal dimuat.';
                            return;
                        }

                        this.hasil = data;

                        if (Array.isArray(data.tidak_ditemukan) && data.tidak_ditemukan.length) {
                            this.pesanError = data.tidak_ditemukan.length + ' NISN tidak ditemukan dan akan dilewati: '
                                + data.tidak_ditemukan.slice(0, 5).join(', ');
                        }
                    } catch (e) {
                        this.pesanError = 'Tidak dapat menghubungi server. Coba lagi.';
                    } finally {
                        this.memuat = false;
                    }
                },

                kirim(aksi) {
                    if (!this.bolehKirim) return;

                    const pesan = aksi === 'setujui'
                        ? 'Setujui (validasi) lembar observasi untuk ' + this.labelCakupan + '? Isi observasi, bukti foto, dan paraf digital tidak diubah.'
                        : 'Batalkan validasi observasi untuk ' + this.labelCakupan + '? Lembar yang sudah tervalidasi akan dikembalikan ke draft.';

                    if (!window.confirm(pesan)) return;

                    this.$refs.inputAksi.value = aksi;
                    this.$refs.formValidasiMassal.submit();
                },
            };
        };
    </script>
</x-app-layout>

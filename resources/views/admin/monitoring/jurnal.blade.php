<x-app-layout title="Monitoring Jurnal Kegiatan">
    <style>
        [x-cloak]{display:none!important;}
        /* ===== Pergantian tampilan berbasis lebar layar ===== */
        .jrn-desktop{ display:none; }   /* HP: tabel lengkap disembunyikan */
        .jrn-mobile { display:block; }  /* HP: tabel ringkas tampil */
        @media (min-width:1024px){      /* laptop & PC (>=1024px) */
            .jrn-desktop{ display:block; }
            .jrn-mobile { display:none; }
        }
    </style>

    {{-- ============================================================= --}}
    {{-- min 360px  •  max 1920px  •  full kanan-kiri                   --}}
    {{-- ============================================================= --}}
    <div x-data="jurnalCrud()"
         x-effect="document.body.style.overflow = (open || hapusOpen || detailOpen || validasiOpen) ? 'hidden' : ''"
         class="py-6 md:py-8 bg-white">
        <div class="w-full max-w-[1920px] mx-auto space-y-6 px-4 sm:px-6 lg:px-8 2xl:px-12">

            {{-- ===================== HEADER ===================== --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Monitoring Jurnal Kegiatan Siswa</h2>
                    <p class="text-sm font-medium text-[#5b616e] mt-1">Kelola seluruh jurnal kegiatan siswa PKL (tambah, ubah, hapus, ubah status, validasi, cetak).</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" @click="tambah()"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Jurnal
                    </button>
                    <button type="button" onclick="history.back()"
                            class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                        Kembali
                    </button>
                </div>
            </div>

            {{-- ===================== FLASH & ERROR ===================== --}}
            @if (session('success'))
                <div class="rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ===================== KARTU REKAP ===================== --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Jurnal</p>
                    <p class="mt-1 text-2xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Disetujui</p>
                    <p class="mt-1 text-2xl font-bold text-[#05b169]">{{ $rekap['disetujui'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Diajukan</p>
                    <p class="mt-1 text-2xl font-bold text-[#d98200]">{{ $rekap['diajukan'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Draft</p>
                    <p class="mt-1 text-2xl font-bold text-[#5b616e]">{{ $rekap['draft'] }}</p>
                </div>
            </div>

            {{-- ===================== CETAK SEMUA ===================== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-black">Jurnal Kegiatan Seluruh Siswa</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Tombol <span class="font-bold text-black">Cetak Semua PDF</span> mencetak jurnal sesuai
                        <span class="font-bold text-black">filter tanggal</span>. Bila tanggal dikosongkan, otomatis mencetak jurnal <span class="font-bold text-black">hari ini</span> (1 siswa per halaman).
                    </p>
                </div>
                <a href="{{ route('cetak.jurnal.semua', ['tanggal' => request('tanggal')]) }}" target="_blank"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                    </svg>
                    Cetak Semua PDF
                </a>
            </div>

            {{-- ============ VALIDASI JURNAL: BEBERAPA NISN SEKALIGUS / SEMUA SISWA ============ --}}
            @php
                $jurnalValidasiList = ($siswaList ?? collect())->map(fn ($s) => [
                    'nisn'      => (string) ($s->nisn ?? ''),
                    'name'      => $s->name,
                    'statusPkl' => $s->status_pkl ?? '-',
                ])->values();
            @endphp
            <div x-data="validasiJurnalMassal(@js($jurnalValidasiList), @js(route('admin.monitoring.jurnal.validasi-pratinjau')))"
                 x-effect="document.body.style.overflow = open ? 'hidden' : ''"
                 class="rounded-2xl border-2 border-[#05b169]/30 bg-white p-4 sm:p-6 shadow-sm flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-black">Validasi Jurnal</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Menyetujui atau membatalkan validasi jurnal <span class="font-bold text-black">tanpa membuka satu per satu</span>:
                        bisa <span class="font-bold text-black">beberapa NISN sekaligus</span> atau <span class="font-bold text-black">semua siswa</span>,
                        dengan filter <span class="font-bold text-black">hari tertentu</span>, <span class="font-bold text-black">rentang tanggal</span>,
                        atau seluruh riwayat. Isi jurnal, dokumentasi, dan catatan instruktur tidak diubah.
                    </p>
                </div>
                <button type="button" @click="bukaModal()"
                        class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#05b169] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-[#05b169]/30 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Validasi Jurnal
                </button>

                <template x-teleport="body">
                    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="open=false">
                        <div class="absolute inset-0 bg-black/50" @click="open=false"></div>
                        <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col rounded-2xl bg-white shadow-xl">
                            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                <div>
                                    <h3 class="text-base font-bold text-gray-800">Validasi Jurnal Massal</h3>
                                    <p class="text-xs font-medium text-[#5b616e]">Pilih lingkup siswa dan tanggalnya, periksa dulu jumlahnya, lalu setujui atau batalkan validasinya.</p>
                                </div>
                                <button type="button" @click="open=false" class="text-2xl leading-none text-gray-400 hover:text-black">&times;</button>
                            </div>

                            <form method="POST" action="{{ route('admin.monitoring.jurnal.validasi') }}" x-ref="formValidasi" @submit.prevent
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

                                    <p x-show="jenisTanggal==='semua'" x-cloak class="mt-2 text-[11px] font-bold text-[#cf202f]">Seluruh riwayat jurnal siswa terpilih akan diproses (tanpa batas tanggal).</p>
                                </div>

                                {{-- Sumber status yang disetujui --}}
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Jurnal Yang Ikut Disetujui</label>
                                    <select name="sumber" x-model="sumber" @change="hasil = null"
                                            class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                        <option value="semua">Semua yang belum disetujui (Draft + Diajukan)</option>
                                        <option value="diajukan">Hanya yang berstatus Diajukan</option>
                                        <option value="draft">Hanya yang berstatus Draft</option>
                                    </select>
                                    <p class="mt-1 text-[11px] font-medium text-[#5b616e]">Pilihan ini hanya berpengaruh pada aksi <span class="font-bold">Setujui</span>. Pembatalan validasi selalu menyasar jurnal yang berstatus <span class="font-bold">Disetujui</span>.</p>
                                </div>

                                {{-- Pratinjau --}}
                                <div class="rounded-xl bg-slate-50 px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-xs font-bold text-gray-700">Cakupan: <span class="font-medium text-[#5b616e]" x-text="labelCakupan"></span></p>
                                        <button type="button" @click="periksa()" :disabled="memuat" :class="memuat ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#0047d6]/10'"
                                                class="rounded-lg border-2 border-[#0047d6]/30 bg-white px-3 py-1.5 text-[11px] font-bold text-[#0047d6]">Periksa Dulu</button>
                                    </div>

                                    <p x-show="memuat" x-cloak class="mt-2 text-xs font-medium text-[#5b616e]">Menghitung jumlah jurnal...</p>
                                    <p x-show="pesanError" x-cloak x-text="pesanError" class="mt-2 text-xs font-bold text-[#cf202f]"></p>

                                    <template x-if="hasil">
                                        <div class="mt-2">
                                            <div class="grid grid-cols-2 gap-2 text-center sm:grid-cols-4">
                                                <div class="rounded-lg bg-white px-2 py-1.5">
                                                    <p class="text-[10px] font-bold uppercase text-[#5b616e]">Total Jurnal</p>
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
                                                    <p class="text-[10px] font-bold uppercase text-[#05b169]">Disetujui</p>
                                                    <p class="text-base font-extrabold text-[#05b169]" x-text="hasil.ringkasan.disetujui"></p>
                                                </div>
                                            </div>

                                            <p class="mt-2 text-[11px] font-medium text-[#5b616e]">
                                                Siswa terjaring: <span class="font-bold text-black" x-text="hasil.jumlah_siswa"></span> &middot;
                                                belum tervalidasi: <span class="font-bold text-[#cf202f]" x-text="hasil.ringkasan.belum"></span> jurnal
                                            </p>

                                            <div x-show="hasil.daftar.length" x-cloak class="mt-2 max-h-48 overflow-y-auto rounded-lg border border-gray-200 bg-white">
                                                <table class="w-full text-left text-[11px]">
                                                    <thead class="bg-gray-50 text-[10px] font-bold uppercase text-[#5b616e]">
                                                        <tr>
                                                            <th class="px-2 py-1.5">Siswa</th>
                                                            <th class="px-2 py-1.5 text-center">Total</th>
                                                            <th class="px-2 py-1.5 text-center">Draft</th>
                                                            <th class="px-2 py-1.5 text-center">Diajukan</th>
                                                            <th class="px-2 py-1.5 text-center">Disetujui</th>
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
                                                                <td class="px-2 py-1.5 text-center text-[#05b169]" x-text="row.disetujui"></td>
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
                                    <button type="button" @click="open=false" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Tutup</button>
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

            {{-- ===================== FILTER ===================== --}}
            <form method="GET" action="{{ route('admin.monitoring.jurnal') }}"
                  class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 flex flex-wrap gap-3 items-end shadow-sm">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Kelas</label>
                    <select name="kelas" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $opsiKelas)
                            <option value="{{ $opsiKelas }}" @selected(request('kelas') === $opsiKelas)>{{ $opsiKelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jurusan</label>
                    <select name="jurusan" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList as $opsiJurusan)
                            <option value="{{ $opsiJurusan }}" @selected(request('jurusan') === $opsiJurusan)>{{ $opsiJurusan }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Filter status PKL siswa: aktif / belum / selesai --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status PKL</label>
                    <select name="status_pkl" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua Status PKL</option>
                        <option value="aktif" @selected(request('status_pkl') === 'aktif')>Aktif</option>
                        <option value="belum" @selected(request('status_pkl') === 'belum')>Belum</option>
                        <option value="selesai" @selected(request('status_pkl') === 'selesai')>Selesai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Jurnal</label>
                    <select name="status" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <option value="">Semua</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="diajukan" @selected(request('status') === 'diajukan')>Diajukan</option>
                        <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                           class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                </div>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Filter</button>
                <a href="{{ route('admin.monitoring.jurnal') }}"
                   class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
            </form>

            {{-- ============================================================= --}}
            {{-- ==========  TAMPILAN LAPTOP / PC (TABEL LENGKAP, >=1024px) == --}}
            {{-- ============================================================= --}}
            <div class="jrn-desktop overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full min-w-[1250px] text-sm text-left table-fixed">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                            <th class="px-4 py-3 font-bold w-28">Tanggal</th>
                            <th class="px-4 py-3 font-bold w-40">Nama</th>
                            <th class="px-4 py-3 font-bold w-28">NISN</th>
                            <th class="px-4 py-3 font-bold w-[24%]">Unit Kerja</th>
                            <th class="px-4 py-3 font-bold w-[15%]">Catatan Instruktur</th>
                            <th class="px-4 py-3 font-bold w-40">Foto</th>
                            <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                            <th class="px-4 py-3 text-center font-bold w-60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($jurnal as $item)
                            @php
                                $badgeStatus = match($item->status) {
                                    'disetujui' => 'bg-[#05b169] text-white',
                                    'diajukan'  => 'bg-[#d98200] text-white',
                                    default     => 'bg-[#5b616e] text-white',
                                };
                                $labelStatus = match($item->status) {
                                    'disetujui' => 'Disetujui',
                                    'diajukan'  => 'Diajukan',
                                    default     => 'Draft',
                                };
                                $daftarPekerjaan = $item->items;
                                $daftarFoto      = $item->items->whereNotNull('dokumentasi')->values();
                                $payload = [
                                    'id'                 => $item->id,
                                    'siswa_id'           => $item->siswa_id,
                                    'nama'               => $item->siswa->name ?? '-',
                                    'nisn'               => $item->siswa->nisn ?? '-',
                                    'tanggal_label'      => optional($item->hari_tanggal)->format('d M Y'),
                                    'hari_tanggal'       => optional($item->hari_tanggal)->format('Y-m-d'),
                                    'status'             => $item->status,
                                    'status_label'       => $labelStatus,
                                    'catatan_instruktur' => $item->catatan_instruktur,
                                    'foto_bukti_url'     => $item->foto_bukti ? asset('storage/'.$item->foto_bukti) : null,
                                    'ttd_url'            => $item->ttd_instruktur ? asset('storage/'.$item->ttd_instruktur) : null,
                                    'ttd_nama'           => $item->ttd_instruktur_nama,
                                    'ttd_waktu'          => optional($item->ttd_signed_at)->format('d/m/Y H:i'),
                                    'pdf_url'            => route('cetak.jurnal', ['siswa_id' => $item->siswa_id, 'jurnal_id' => $item->id]),
                                    'items'              => $item->items->map(fn($it) => [
                                        'id'                    => $it->id,
                                        'unit_kerja'            => $it->unit_kerja,
                                        'existing_dokumentasi'  => $it->dokumentasi,
                                        'dokumentasi_url'       => $it->dokumentasi ? asset('storage/'.$it->dokumentasi) : null,
                                    ])->values(),
                                ];
                            @endphp
                            <tr class="align-top transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 text-center font-semibold text-black">{{ $jurnal->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ optional($item->hari_tanggal)->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-bold text-black break-words">{{ $item->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $item->siswa->nisn ?? '-' }}</td>
                                <td class="px-4 py-3 text-black break-words">
                                    @if($daftarPekerjaan->count())
                                        <div x-data="{ open: false }">
                                            <div class="flex items-start gap-1.5">
                                                <span class="font-bold text-[#0047d6]">1.</span>
                                                <span class="font-medium break-words">{{ $daftarPekerjaan->first()->unit_kerja }}</span>
                                            </div>
                                            @if($daftarPekerjaan->count() > 1)
                                                <button type="button" @click="open = !open"
                                                        class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                    <span x-show="!open">+ {{ $daftarPekerjaan->count() - 1 }} unit kerja lainnya</span>
                                                    <span x-show="open" style="display:none;">Sembunyikan</span>
                                                </button>
                                                <ol start="2" x-show="open" x-cloak x-transition
                                                    class="mt-2 list-decimal list-inside space-y-0.5 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                    @foreach($daftarPekerjaan->slice(1) as $pekerjaan)
                                                        <li class="break-words">{{ $pekerjaan->unit_kerja }}</li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-black break-words">
                                    @if($item->catatan_instruktur)
                                        <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-xs font-medium italic text-black">
                                            {{ \Illuminate\Support\Str::limit($item->catatan_instruktur, 80) }}
                                        </div>
                                    @else
                                        <span class="text-[#5b616e]">-</span>
                                    @endif
                                </td>
                                {{-- === KOLOM FOTO GABUNGAN (dokumentasi + bukti fisik) === --}}
                                <td class="px-4 py-3">
                                    @if($daftarFoto->count() || $item->foto_bukti)
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($daftarFoto as $indexFoto => $pekerjaan)
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <span class="text-xs font-semibold text-black">Foto {{ $indexFoto + 1 }}</span>
                                                    <a href="{{ asset('storage/' . $pekerjaan->dokumentasi) }}" download target="_blank"
                                                       class="inline-flex items-center rounded-full bg-[#0047d6] px-2.5 py-1 text-xs font-bold text-white transition hover:bg-[#0038aa]">Lihat</a>
                                                </div>
                                            @endforeach
                                            @if($item->foto_bukti)
                                                <div class="flex flex-wrap items-center gap-1.5 border-t border-[#0047d6]/10 pt-1.5">
                                                    <span class="text-xs font-semibold text-[#d98200]">Bukti Fisik (lama)</span>
                                                    <a href="{{ asset('storage/' . $item->foto_bukti) }}" download target="_blank"
                                                       class="inline-flex items-center rounded-full bg-[#d98200] px-2.5 py-1 text-xs font-bold text-white transition hover:opacity-90">Lihat</a>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-[#5b616e]">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block rounded-full px-3 py-1 text-xs font-bold {{ $badgeStatus }}">{{ $labelStatus }}</span>
                                    {{-- Paraf digital instruktur: bisa dilihat & diunduh --}}
                                    <div class="mt-2 flex justify-center">
                                        <x-paraf-instruktur :ttd="$item->ttd_instruktur"
                                                            :nama="$item->ttd_instruktur_nama"
                                                            :waktu="$item->ttd_signed_at"
                                                            :foto-lama="$item->foto_bukti"
                                                            :tinggi="42"
                                                            judul="Paraf Instruktur — {{ $item->siswa->name ?? '-' }}"
                                                            unduh-nama="paraf-jurnal-{{ $item->id }}"
                                                            kosong="Belum ada paraf" />
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                                        <button type="button" @click='lihatDetail(@json($payload))'
                                                class="rounded-lg bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">Detail</button>
                                        <button type="button" @click='bukaValidasi(@json($payload))'
                                                class="rounded-lg bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049a5b]">Validasi</button>
                                        <button type="button" @click='edit(@json($payload))'
                                                class="rounded-lg border-2 border-[#0047d6]/30 px-3 py-1.5 text-xs font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Edit</button>
                                        <a href="{{ $payload['pdf_url'] }}" target="_blank"
                                           class="rounded-lg bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">PDF</a>
                                        <button type="button" @click="konfirmHapus('{{ route('admin.monitoring.jurnal.destroy', $item->id) }}')"
                                                class="rounded-lg border-2 border-red-200 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data jurnal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============================================================= --}}
            {{-- ==========  TAMPILAN HP (TABEL RINGKAS, <1024px)  ========== --}}
            {{-- ==========  hanya Nama + tombol Lihat Detail       ========== --}}
            {{-- ============================================================= --}}
            <div class="jrn-mobile overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                            <th class="px-4 py-3 font-bold">Nama</th>
                            <th class="px-4 py-3 text-center font-bold w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#0047d6]/10">
                        @forelse ($jurnal as $item)
                            @php
                                $labelStatusM = match($item->status) {
                                    'disetujui' => 'Disetujui',
                                    'diajukan'  => 'Diajukan',
                                    default     => 'Draft',
                                };
                                $payloadM = [
                                    'id'                 => $item->id,
                                    'siswa_id'           => $item->siswa_id,
                                    'nama'               => $item->siswa->name ?? '-',
                                    'nisn'               => $item->siswa->nisn ?? '-',
                                    'tanggal_label'      => optional($item->hari_tanggal)->format('d M Y'),
                                    'hari_tanggal'       => optional($item->hari_tanggal)->format('Y-m-d'),
                                    'status'             => $item->status,
                                    'status_label'       => $labelStatusM,
                                    'catatan_instruktur' => $item->catatan_instruktur,
                                    'foto_bukti_url'     => $item->foto_bukti ? asset('storage/'.$item->foto_bukti) : null,
                                    'ttd_url'            => $item->ttd_instruktur ? asset('storage/'.$item->ttd_instruktur) : null,
                                    'ttd_nama'           => $item->ttd_instruktur_nama,
                                    'ttd_waktu'          => optional($item->ttd_signed_at)->format('d/m/Y H:i'),
                                    'pdf_url'            => route('cetak.jurnal', ['siswa_id' => $item->siswa_id, 'jurnal_id' => $item->id]),
                                    'items'              => $item->items->map(fn($it) => [
                                        'id'                    => $it->id,
                                        'unit_kerja'            => $it->unit_kerja,
                                        'existing_dokumentasi'  => $it->dokumentasi,
                                        'dokumentasi_url'       => $it->dokumentasi ? asset('storage/'.$it->dokumentasi) : null,
                                    ])->values(),
                                ];
                            @endphp
                            <tr class="align-middle transition hover:bg-[#0047d6]/5">
                                <td class="px-4 py-3 font-bold text-black break-words">{{ $item->siswa->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click='lihatDetail(@json($payloadM))'
                                            class="inline-flex items-center rounded-lg bg-[#0047d6] px-3 py-2 text-xs font-bold text-white transition hover:bg-[#0038aa]">Lihat Detail</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Tidak ada data jurnal.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ===================== PAGINATION ===================== --}}
            <div>{{ $jurnal->links() }}</div>
        </div>

        {{-- ===================================================================== --}}
        {{-- ===================== MODAL TAMBAH / EDIT =========================== --}}
        {{-- ===================================================================== --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
             @keydown.escape.window="open = false">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-5 sm:p-6 shadow-xl"
                 @click.outside="open = false">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-black" x-text="mode === 'create' ? 'Tambah Jurnal' : 'Edit Jurnal'"></h3>
                    <button type="button" @click="open = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                </div>
                <form :action="actionUrl" method="POST" enctype="multipart/form-data" @submit="simpan($event)" class="space-y-3">
                    @csrf
                    <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="siswa_id" :value="siswaCocok ? siswaCocok.id : ''">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">NISN Siswa</label>
                        <input type="text" x-model="form.nisn" placeholder="Masukkan NISN siswa"
                               class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <template x-if="siswaCocok">
                            <p class="mt-1 text-xs font-semibold text-[#05b169]">&#10003; <span x-text="siswaCocok.name"></span></p>
                        </template>
                        <template x-if="form.nisn.trim() !== '' && !siswaCocok">
                            <p class="mt-1 text-xs font-semibold text-[#cf202f]">NISN tidak cocok</p>
                        </template>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Tanggal</label>
                            <input type="date" name="hari_tanggal" x-model="form.hari_tanggal" required
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
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
                    </div>
                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black">Unit Kerja / Pekerjaan</label>
                            <button type="button" @click="tambahItem()"
                                    class="rounded-lg bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] hover:bg-[#0047d6]/20">Tambah unit kerja</button>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(it, i) in form.items" :key="i">
                                <div class="rounded-xl border-2 border-[#0047d6]/15 p-3 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-[#5b616e]">Pekerjaan <span x-text="i + 1"></span></span>
                                        <button type="button" @click="hapusItem(i)" x-show="form.items.length > 1"
                                                class="rounded-lg border-2 border-red-200 px-2.5 py-1 text-xs font-bold text-red-600 hover:bg-red-50">Hapus</button>
                                    </div>
                                    <input type="hidden" :name="'items[' + i + '][id]'" :value="it.id ?? ''">
                                    <input type="hidden" :name="'items[' + i + '][existing_dokumentasi]'" :value="it.existing_dokumentasi ?? ''">
                                    <textarea :name="'items[' + i + '][unit_kerja]'" x-model="it.unit_kerja" rows="2"
                                              placeholder="Contoh: Instalasi jaringan ruang server"
                                              class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Foto Dokumentasi (opsional)</label>
                                        <template x-if="it.dokumentasi_url">
                                            <a :href="it.dokumentasi_url" download target="_blank" class="mb-1 inline-block text-[11px] font-bold text-[#0047d6] hover:underline">Lihat foto saat ini</a>
                                        </template>
                                        <input type="file" :name="'items[' + i + '][dokumentasi]'" accept="image/*"
                                               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#eef0f3] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-[#0a0b0d]">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Catatan Instruktur</label>
                        <textarea name="catatan_instruktur" x-model="form.catatan_instruktur" rows="2"
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                    </div>
                    <div>
                        {{-- ===== TANDA TANGAN DIGITAL INSTRUKTUR (OPSIONAL) ===== --}}
                        <div class="rounded-xl border-2 border-[#0047d6]/15 bg-[#0047d6]/5 p-3">
                            <p class="text-xs font-bold uppercase tracking-wide text-black">Tanda Tangan Digital Instruktur (opsional)</p>
                            <p class="mt-0.5 text-[11px] font-medium text-[#5b616e]">Instruktur bisa memaraf langsung di kotak di bawah (mendukung layar HP). Unggah foto lembar berparaf sudah tidak diperlukan.</p>

                            {{-- paraf yang sudah tersimpan (mode edit) --}}
                            <template x-if="form.ttd_url">
                                <div class="mt-2 rounded-xl border-2 border-[#05b169]/40 bg-white p-2">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-[#05b169]">Paraf tersimpan</p>
                                    <img :src="form.ttd_url" alt="Paraf digital instruktur" style="height:48px; width:auto; max-width:100%; margin-top:4px;">
                                    <div class="mt-1 flex flex-wrap items-center gap-3">
                                        <a :href="form.ttd_url" target="_blank" rel="noopener" class="text-[11px] font-bold text-[#0047d6] hover:underline">Lihat</a>
                                        <a :href="form.ttd_url" :download="'paraf-jurnal-' + (form.id || '') + '.png'" class="text-[11px] font-bold text-[#0047d6] hover:underline">Unduh paraf</a>
                                        <label class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#cf202f]">
                                            <input type="checkbox" name="hapus_ttd_instruktur" value="1"> Hapus paraf
                                        </label>
                                    </div>
                                    <p class="mt-1 text-[10px] font-medium text-[#5b616e]">Menandatangani ulang di kotak bawah otomatis mengganti paraf lama.</p>
                                </div>
                            </template>

                            <div class="mt-2">
                                <x-ttd-pad name="ttd_instruktur"
                                           label="Paraf / Tanda Tangan Instruktur"
                                           :tinggi="150"
                                           :wajib="false" />
                                <p class="mt-1 text-[11px] text-[#5b616e]">Diparaf oleh:
                                    <span class="font-semibold text-black" x-text="namaInstrukturForm || 'Instruktur (nama belum diatur admin)'"></span>
                                </p>
                            </div>

                            {{-- data lama: masih memakai foto lembar berparaf --}}
                            <template x-if="form.foto_bukti_url">
                                <div class="mt-2 rounded-xl border-2 border-dashed border-[#d98200]/40 bg-white p-2">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-[#d98200]">Data lama: foto lembar berparaf</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-3">
                                        <a :href="form.foto_bukti_url" download target="_blank" class="text-[11px] font-bold text-[#0047d6] hover:underline">Lihat foto lama</a>
                                        <label class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#cf202f]">
                                            <input type="checkbox" name="hapus_foto_bukti" value="1"> Hapus foto lama
                                        </label>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''"
                                class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Simpan</button>
                        <button type="button" @click="open = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- ===================== MODAL DETAIL (animasi smooth) ================= --}}
        {{-- ===================================================================== --}}
        <div x-show="detailOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
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
                <div class="flex items-center justify-between border-b-2 border-[#0047d6]/15 px-5 py-3 sticky top-0 bg-white">
                    <h3 class="text-base font-bold text-black">Detail Jurnal</h3>
                    <button type="button" @click="detailOpen = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Nama Siswa</p>
                            <p class="font-bold text-black break-words" x-text="detail.nama"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">NISN</p>
                            <p class="font-medium text-black" x-text="detail.nisn"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p>
                            <p class="font-medium text-black" x-text="detail.tanggal_label"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Status</p>
                            <span class="inline-block rounded-full px-3 py-1 text-xs font-bold"
                                  :class="detail.status === 'disetujui' ? 'bg-[#05b169] text-white' : (detail.status === 'diajukan' ? 'bg-[#d98200] text-white' : 'bg-[#5b616e] text-white')"
                                  x-text="detail.status_label"></span>
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Unit Kerja / Pekerjaan</p>
                        <template x-if="detail.items && detail.items.length">
                            <ol class="list-decimal list-inside space-y-2">
                                <template x-for="(it, i) in detail.items" :key="i">
                                    <li class="font-medium text-black break-words">
                                        <span x-text="it.unit_kerja"></span>
                                        <template x-if="it.dokumentasi_url">
                                            <a :href="it.dokumentasi_url" download target="_blank"
                                               class="ml-1 inline-flex items-center rounded-full bg-[#0047d6] px-2 py-0.5 text-[11px] font-bold text-white transition hover:bg-[#0038aa]">Foto</a>
                                        </template>
                                    </li>
                                </template>
                            </ol>
                        </template>
                        <template x-if="!detail.items || !detail.items.length">
                            <p class="text-sm text-[#5b616e]">-</p>
                        </template>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Catatan Instruktur</p>
                        <template x-if="detail.catatan_instruktur">
                            <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-sm font-medium italic text-black" x-text="detail.catatan_instruktur"></div>
                        </template>
                        <template x-if="!detail.catatan_instruktur">
                            <p class="text-sm text-[#5b616e]">-</p>
                        </template>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Paraf Digital Instruktur</p>
                        <template x-if="detail.ttd_url">
                            <div class="rounded-xl border-2 border-[#05b169]/40 bg-white p-3">
                                <img :src="detail.ttd_url" alt="Paraf digital instruktur" style="height:56px; width:auto; max-width:100%;">
                                <p class="mt-2 text-[11px] font-semibold text-[#5b616e]">
                                    <span class="font-bold text-black">Nama:</span> <span x-text="detail.ttd_nama || '-'"></span>
                                    &middot;
                                    <span class="font-bold text-black">Waktu:</span> <span x-text="detail.ttd_waktu || '-'"></span>
                                </p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <a :href="detail.ttd_url" target="_blank" rel="noopener"
                                       class="inline-flex items-center rounded-xl bg-[#0047d6] px-3 py-2 text-xs font-bold text-white transition hover:bg-[#0038aa]">Buka Gambar</a>
                                    <a :href="detail.ttd_url" :download="'paraf-jurnal-' + (detail.id || '') + '.png'"
                                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6] bg-white px-3 py-2 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Unduh Paraf</a>
                                </div>
                            </div>
                        </template>
                        <template x-if="!detail.ttd_url && detail.foto_bukti_url">
                            <a :href="detail.foto_bukti_url" download target="_blank"
                               class="inline-flex items-center gap-1 rounded-xl bg-[#d98200] px-4 py-2 text-sm font-bold text-white transition hover:opacity-90">Lihat Bukti Fisik (lama)</a>
                        </template>
                        <template x-if="!detail.ttd_url && !detail.foto_bukti_url">
                            <p class="text-sm text-[#5b616e]">Belum ada paraf instruktur.</p>
                        </template>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <a :href="detail.pdf_url" target="_blank"
                           class="flex-1 min-w-[110px] text-center rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Cetak PDF</a>
                        <button type="button" @click="detailOpen = false; bukaValidasi(detail)"
                                class="flex-1 min-w-[110px] rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#049a5b]">Validasi</button>
                        <button type="button" @click="detailOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- ===================== MODAL VALIDASI (animasi smooth) ============== --}}
        {{-- ===================================================================== --}}
        <div x-show="validasiOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
             @keydown.escape.window="validasiOpen = false">
            <div x-show="validasiOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl text-left"
                 @click.outside="validasiOpen = false">
                <div class="flex items-center justify-between border-b-2 border-[#0047d6]/15 px-5 py-3">
                    <h3 class="text-base font-bold text-black">Validasi Jurnal &mdash; <span x-text="validasi.nama"></span></h3>
                    <button type="button" @click="validasiOpen = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                </div>
                <form :action="validasiUrl" method="POST" enctype="multipart/form-data" class="space-y-4 p-5">
                    @csrf
                    @method('PUT')
                    {{-- field wajib agar update tidak menghapus data --}}
                    <input type="hidden" name="siswa_id" :value="validasi.siswa_id">
                    <input type="hidden" name="hari_tanggal" :value="validasi.hari_tanggal">
                    {{-- pertahankan seluruh unit kerja yang sudah ada --}}
                    <template x-for="(it, i) in validasi.items" :key="i">
                        <div>
                            <input type="hidden" :name="'items[' + i + '][id]'" :value="it.id ?? ''">
                            <input type="hidden" :name="'items[' + i + '][unit_kerja]'" :value="it.unit_kerja">
                            <input type="hidden" :name="'items[' + i + '][existing_dokumentasi]'" :value="it.existing_dokumentasi ?? ''">
                        </div>
                    </template>

                    <div class="rounded-xl bg-[#0047d6]/5 p-3 text-sm">
                        <p class="font-semibold text-black"><span x-text="validasi.nama"></span> &middot; NISN <span x-text="validasi.nisn"></span></p>
                        <p class="text-xs font-medium text-[#5b616e]">Tanggal: <span x-text="validasi.tanggal_label"></span></p>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Ubah Status</label>
                        <select name="status" x-model="validasi.status"
                                class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                            <option value="draft">Draft</option>
                            <option value="diajukan">Diajukan</option>
                            <option value="disetujui">Disetujui</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Catatan Instruktur</label>
                        <textarea name="catatan_instruktur" x-model="validasi.catatan_instruktur" rows="3"
                                  placeholder="Tulis catatan/nilai dari instruktur..."
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                    </div>

                    <div>
                        {{-- ===== TANDA TANGAN DIGITAL INSTRUKTUR (OPSIONAL) ===== --}}
                        <div class="rounded-xl border-2 border-[#0047d6]/15 bg-[#0047d6]/5 p-3">
                            <p class="text-xs font-bold uppercase tracking-wide text-black">Tanda Tangan Digital Instruktur (opsional)</p>

                            <template x-if="validasi.ttd_url">
                                <div class="mt-2 rounded-xl border-2 border-[#05b169]/40 bg-white p-2">
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-[#05b169]">Paraf tersimpan</p>
                                    <img :src="validasi.ttd_url" alt="Paraf digital instruktur" style="height:48px; width:auto; max-width:100%; margin-top:4px;">
                                    <div class="mt-1 flex flex-wrap items-center gap-3">
                                        <a :href="validasi.ttd_url" target="_blank" rel="noopener" class="text-[11px] font-bold text-[#0047d6] hover:underline">Lihat</a>
                                        <a :href="validasi.ttd_url" :download="'paraf-jurnal-' + (validasi.id || '') + '.png'" class="text-[11px] font-bold text-[#0047d6] hover:underline">Unduh paraf</a>
                                        <label class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#cf202f]">
                                            <input type="checkbox" name="hapus_ttd_instruktur" value="1"> Hapus paraf
                                        </label>
                                    </div>
                                </div>
                            </template>

                            <div class="mt-2">
                                <x-ttd-pad name="ttd_instruktur"
                                           label="Paraf / Tanda Tangan Instruktur"
                                           :tinggi="140"
                                           :wajib="false" />
                                <p class="mt-1 text-[11px] text-[#5b616e]">Diparaf oleh:
                                    <span class="font-semibold text-black" x-text="namaInstrukturValidasi || 'Instruktur (nama belum diatur admin)'"></span>
                                </p>
                            </div>

                            <template x-if="validasi.foto_bukti_url">
                                <p class="mt-2 text-[11px] font-semibold text-[#d98200]">Data lama:
                                    <a :href="validasi.foto_bukti_url" download target="_blank" class="font-bold text-[#0047d6] hover:underline">lihat foto lembar berparaf</a>
                                </p>
                            </template>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 rounded-xl bg-[#05b169] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#049a5b]">Simpan Validasi</button>
                        <button type="button" @click="validasiOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- ===================== MODAL HAPUS =================================== --}}
        {{-- ===================================================================== --}}
        <div x-show="hapusOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
             @keydown.escape.window="hapusOpen = false">
            <div x-show="hapusOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false">
                <h3 class="text-base font-bold text-black">Hapus Jurnal</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Yakin ingin menghapus jurnal ini beserta seluruh unit kerja &amp; fotonya? Tindakan ini tidak dapat dibatalkan.</p>
                <form :action="hapusUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#cf202f] px-4 py-2 text-sm font-bold text-white hover:bg-[#b01926]">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================================================================== --}}
    {{-- ===================== ALPINE COMPONENT ============================= --}}
    {{-- ===================================================================== --}}
    <script>
        /* ===== VALIDASI JURNAL MASSAL: beberapa NISN sekaligus / semua siswa ===== */
        window.validasiJurnalMassal = function (list, urlPratinjau) {
            const tglStr = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

            return {
                open: false,
                mode: 'nisn',            // 'nisn' (boleh banyak) atau 'semua'
                nisnInput: '',           // boleh memuat BANYAK NISN
                jenisTanggal: 'rentang', // 'tanggal' | 'rentang' | 'semua'
                tanggal: '',
                mulai: '',
                selesai: '',
                sumber: 'semua',         // status jurnal yang ikut disetujui
                semuaPeriode: false,
                list: Array.isArray(list) ? list : [],
                urlPratinjau: urlPratinjau,
                memuat: false,
                pesanError: '',
                hasil: null,

                bukaModal() {
                    const hariIni = new Date();
                    const awalBulan = new Date(hariIni.getFullYear(), hariIni.getMonth(), 1);

                    this.open = true;
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

                /* ---- Daftar NISN: dipisah koma / titik koma / spasi / baris baru ---- */
                get daftarNisn() {
                    return [...new Set(
                        String(this.nisnInput || '').split(/[\s,;]+/).map((x) => x.trim()).filter((x) => x !== '')
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

                /* Isi semua siswa PKL aktif pada daftar halaman ini. */
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

                    let waktu = 'seluruh riwayat jurnal';
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

                /* Pratinjau: hitung dulu berapa jurnal yang akan terkena aksi. */
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
                        ? 'Setujui (validasi) jurnal untuk ' + this.labelCakupan + '? Isi jurnal, dokumentasi, dan catatan instruktur tidak diubah.'
                        : 'Batalkan validasi jurnal untuk ' + this.labelCakupan + '? Jurnal yang sudah disetujui akan dikembalikan ke status draft.';

                    if (!window.confirm(pesan)) return;

                    this.$refs.inputAksi.value = aksi;
                    this.$refs.formValidasi.submit();
                },
            };
        };

        window.jurnalCrud = function () {
            const daftarSiswa = @js($siswaList);
            const today = @js(date('Y-m-d'));
            const storeUrl = @js(route('admin.monitoring.jurnal.store'));
            const baseUrl = @js(url('admin/monitoring/jurnal'));

            const kosong = () => ({
                id: null, nisn: '', hari_tanggal: today, status: 'draft',
                catatan_instruktur: '', foto_bukti_url: null,
                ttd_url: null, ttd_nama: '',
                items: [{ id: null, unit_kerja: '', existing_dokumentasi: '', dokumentasi_url: null }],
            });

            return {
                // ---- state ----
                open: false,
                mode: 'create',
                form: kosong(),
                hapusOpen: false,
                hapusUrl: '',
                detailOpen: false,
                detail: {},
                validasiOpen: false,
                validasi: {
                    id: null, siswa_id: null, nama: '', nisn: '', tanggal_label: '',
                    hari_tanggal: '', status: 'diajukan', catatan_instruktur: '',
                    foto_bukti_url: null, ttd_url: null, ttd_nama: '',
                    items: [],
                },

                // ---- computed ----
                get siswaCocok() {
                    const nisn = String(this.form.nisn || '').trim();
                    if (!nisn) return null;
                    return daftarSiswa.find(s => String(s.nisn).trim() === nisn) || null;
                },

                // Nama instruktur otomatis (teks "Diparaf oleh" di bawah kanvas paraf).
                get namaInstrukturForm() {
                    const s = this.siswaCocok;
                    return (s && s.instruktur_nama) ? s.instruktur_nama : (this.form.ttd_nama || '');
                },
                get namaInstrukturValidasi() {
                    const s = daftarSiswa.find(x => String(x.id) === String(this.validasi.siswa_id));
                    return (s && s.instruktur_nama) ? s.instruktur_nama : (this.validasi.ttd_nama || '');
                },
                get actionUrl() { return this.mode === 'create' ? storeUrl : baseUrl + '/' + this.form.id; },
                get validasiUrl() { return this.validasi.id ? baseUrl + '/' + this.validasi.id : '#'; },

                // ---- tambah / edit ----
                tambah() { this.mode = 'create'; this.form = kosong(); this.resetParaf(); this.open = true; },

                // Kosongkan kanvas tanda tangan agar paraf dari modal sebelumnya tidak terbawa.
                resetParaf() {
                    document.querySelectorAll('[data-ttd] [data-ttd-clear]').forEach(function (tombol) { tombol.click(); });
                },
                edit(d) {
                    const s = daftarSiswa.find(x => String(x.id) === String(d.siswa_id));
                    let items = Array.isArray(d.items) ? d.items.map(it => ({
                        id: it.id,
                        unit_kerja: it.unit_kerja || '',
                        existing_dokumentasi: it.existing_dokumentasi || '',
                        dokumentasi_url: it.dokumentasi_url || null,
                    })) : [];
                    if (items.length === 0) items = [{ id: null, unit_kerja: '', existing_dokumentasi: '', dokumentasi_url: null }];
                    this.mode = 'edit';
                    this.form = {
                        id: d.id,
                        nisn: s ? String(s.nisn) : String(d.nisn || ''),
                        hari_tanggal: d.hari_tanggal,
                        status: d.status || 'draft',
                        catatan_instruktur: d.catatan_instruktur || '',
                        foto_bukti_url: d.foto_bukti_url || null,
                        ttd_url: d.ttd_url || null,
                        ttd_nama: d.ttd_nama || '',
                        items: items,
                    };
                    this.detailOpen = false;
                    this.resetParaf();
                    this.open = true;
                },
                tambahItem() { this.form.items.push({ id: null, unit_kerja: '', existing_dokumentasi: '', dokumentasi_url: null }); },
                hapusItem(i) { this.form.items.splice(i, 1); },
                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },

                // ---- hapus ----
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },

                // ---- detail ----
                lihatDetail(d) { this.detail = d; this.detailOpen = true; },

                // ---- validasi ----
                bukaValidasi(d) {
                    this.validasi = {
                        id: d.id,
                        siswa_id: d.siswa_id,
                        nama: d.nama || '',
                        nisn: d.nisn || '',
                        tanggal_label: d.tanggal_label || '',
                        hari_tanggal: d.hari_tanggal || '',
                        status: d.status === 'draft' ? 'disetujui' : (d.status || 'disetujui'),
                        catatan_instruktur: d.catatan_instruktur || '',
                        foto_bukti_url: d.foto_bukti_url || null,
                        ttd_url: d.ttd_url || null,
                        ttd_nama: d.ttd_nama || '',
                        items: Array.isArray(d.items) ? d.items.map(it => ({
                            id: it.id,
                            unit_kerja: it.unit_kerja || '',
                            existing_dokumentasi: it.existing_dokumentasi || '',
                        })) : [],
                    };
                    this.resetParaf();
                    this.validasiOpen = true;
                },
            };
        };
    </script>
</x-app-layout>

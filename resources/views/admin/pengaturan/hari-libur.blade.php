<x-app-layout title="Tanggal Merah">
    <style>[x-cloak]{display:none!important;}</style>

    @php
        /* Menandai form mana yang baru saja gagal validasi, supaya pop up yang
           bersangkutan dibuka kembali beserta isian lama pengguna. */
        $formGagal = old('_form');
    @endphp

    {{--
        Isian lama (setelah validasi gagal) dititipkan lewat atribut data-*,
        BUKAN disisipkan langsung ke dalam kode JavaScript. Nama libur yang
        memuat tanda petik, misalnya Isra Miraj dengan apostrof, akan dibaca
        utuh oleh browser tanpa merusak atribut maupun string JavaScript.
    --}}
    <div class="py-6 sm:py-8 md:py-10 bg-white"
         data-gagal="{{ $formGagal }}"
         data-ubah-aksi="{{ $formGagal === 'ubah' ? old('_aksi') : '' }}"
         data-ubah-nama="{{ $formGagal === 'ubah' ? old('nama') : '' }}"
         data-ubah-mulai="{{ $formGagal === 'ubah' ? old('tanggal_mulai') : '' }}"
         data-ubah-selesai="{{ $formGagal === 'ubah' ? old('tanggal_selesai') : '' }}"
         data-ubah-keterangan="{{ $formGagal === 'ubah' ? old('keterangan') : '' }}"
         data-ubah-label="{{ $formGagal === 'ubah' ? old('_label') : '' }}"
         x-data="halamanHariLibur()"
         x-init="pulihkan($el.dataset)"
         x-effect="document.body.style.overflow = adaModal ? 'hidden' : ''"
         x-on:keydown.escape.window="tutupSemua()">

        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- ===================== HEADER ===================== --}}
            <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Pengaturan &mdash; Tanggal Merah</h2>
                    <p class="text-sm text-gray-500">Daftar hari libur nasional, cuti bersama, dan libur sekolah dalam satu tahun.</p>
                </div>
                <button type="button" @click="openMassal = true"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-[#2563EB] bg-white px-4 py-2 text-sm font-medium text-[#2563EB] transition hover:bg-blue-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-7 5h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Tambah Beberapa Sekaligus
                </button>
            </div>

            {{-- ===================== FLASH ===================== --}}
            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">{{ session('error') }}</div>
            @endif

            @if ($belumSiap)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <span class="font-semibold">Tabel tanggal merah belum tersedia.</span>
                    Jalankan perintah <code class="rounded bg-amber-100 px-1">php artisan migrate --force</code> di server, lalu muat ulang halaman ini.
                </div>
            @endif

            {{-- ===================== PENJELASAN EFEK ===================== --}}
            <div class="mb-6 rounded-2xl border border-blue-100 bg-blue-50/60 p-4 sm:p-5">
                <h3 class="mb-2 flex items-center gap-2 text-sm font-bold text-[#2563EB]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 8h.01M11 12h1v4h1" />
                    </svg>
                    Yang otomatis terjadi pada tanggal merah
                </h3>
                <ul class="space-y-1.5 text-sm text-slate-600">
                    <li class="flex gap-2">
                        <span class="font-bold text-[#2563EB]">1.</span>
                        <span>Halaman absensi siswa <span class="font-semibold">tertutup</span> &mdash; absensi tidak perlu diisi.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold text-[#2563EB]">2.</span>
                        <span>Hari itu <span class="font-semibold">tidak pernah dihitung Alpha</span>, walaupun tidak ada siswa yang absen.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold text-[#2563EB]">3.</span>
                        <span>Notifikasi pengingat <span class="font-semibold">jurnal, catatan, dan absensi</span> tidak dimunculkan &mdash; baik untuk siswa maupun guru pembimbing.</span>
                    </li>
                </ul>
                <p class="mt-3 border-t border-blue-100 pt-3 text-xs text-slate-500">
                    Pengingat juga otomatis diam di <span class="font-semibold">luar jam kerja</span> dan pada hari yang bukan hari kerja menurut jadwal.
                    Bila sekolah tetap mengadakan kegiatan di tanggal merah, admin masih bisa memakai tombol
                    <span class="font-semibold">Buka Absensi</span> di halaman Monitoring Absensi &mdash; pembukaan manual selalu menang atas aturan ini.
                </p>
            </div>

            {{-- ===================== RINGKASAN ===================== --}}
            <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Tanggal Merah Aktif</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">{{ $jumlahAktif }}</p>
                    <p class="text-xs text-slate-500">entri pada tahun {{ $tahun }}</p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Hari Libur</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">{{ $jumlahHari }}</p>
                    <p class="text-xs text-slate-500">hari dibebaskan dari absensi</p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Libur Terdekat</p>
                    @if ($berikutnya)
                        <p class="mt-1 truncate text-base font-bold text-gray-800">{{ $berikutnya->nama }}</p>
                        <p class="text-xs text-slate-500">{{ $berikutnya->label_tanggal }}</p>
                    @else
                        <p class="mt-1 text-base font-bold text-slate-400">Belum ada</p>
                        <p class="text-xs text-slate-500">tidak ada libur yang akan datang</p>
                    @endif
                </div>
            </div>

            {{-- ===================== FORM TAMBAH SATU ===================== --}}
            <div class="mb-6 rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
                <h3 class="mb-1 text-base font-semibold text-gray-800">Tambah Tanggal Merah</h3>
                <p class="mb-4 text-sm text-gray-500">
                    Isi <span class="font-semibold">Tanggal Selesai</span> hanya bila liburnya lebih dari satu hari, misalnya cuti bersama.
                </p>

                <form method="POST" action="{{ route('admin.hari-libur.store') }}">
                    @csrf
                    <input type="hidden" name="_form" value="tambah">

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="lg:col-span-2">
                            <label for="nama" class="mb-1 block text-xs font-medium text-gray-500">Nama Libur</label>
                            <input type="text" id="nama" name="nama" required maxlength="120"
                                   value="{{ $formGagal === 'tambah' ? old('nama') : '' }}"
                                   placeholder="mis. Hari Kemerdekaan RI"
                                   class="w-full rounded-lg text-sm focus:border-[#2563EB] focus:ring-[#2563EB] {{ $formGagal === 'tambah' && $errors->has('nama') ? 'border-red-300' : 'border-blue-100' }}">
                        </div>
                        <div>
                            <label for="tanggal_mulai" class="mb-1 block text-xs font-medium text-gray-500">Tanggal</label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" required
                                   value="{{ $formGagal === 'tambah' ? old('tanggal_mulai') : '' }}"
                                   class="w-full rounded-lg text-sm focus:border-[#2563EB] focus:ring-[#2563EB] {{ $formGagal === 'tambah' && $errors->has('tanggal_mulai') ? 'border-red-300' : 'border-blue-100' }}">
                        </div>
                        <div>
                            <label for="tanggal_selesai" class="mb-1 block text-xs font-medium text-gray-500">Tanggal Selesai <span class="text-slate-400">(opsional)</span></label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai"
                                   value="{{ $formGagal === 'tambah' ? old('tanggal_selesai') : '' }}"
                                   class="w-full rounded-lg text-sm focus:border-[#2563EB] focus:ring-[#2563EB] {{ $formGagal === 'tambah' && $errors->has('tanggal_selesai') ? 'border-red-300' : 'border-blue-100' }}">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="keterangan" class="mb-1 block text-xs font-medium text-gray-500">Keterangan <span class="text-slate-400">(opsional)</span></label>
                        <input type="text" id="keterangan" name="keterangan" maxlength="500"
                               value="{{ $formGagal === 'tambah' ? old('keterangan') : '' }}"
                               placeholder="Catatan internal, mis. sesuai SKB 3 Menteri"
                               class="w-full rounded-lg text-sm focus:border-[#2563EB] focus:ring-[#2563EB] {{ $formGagal === 'tambah' && $errors->has('keterangan') ? 'border-red-300' : 'border-blue-100' }}">
                    </div>

                    @if ($formGagal === 'tambah' && $errors->any())
                        <ul class="mt-3 space-y-1 rounded-lg bg-red-50 px-4 py-3 text-xs text-red-600">
                            @foreach ($errors->all() as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 sm:w-auto sm:py-2">
                            Simpan Tanggal Merah
                        </button>
                    </div>
                </form>
            </div>

            {{-- ===================== PENYARING TAHUN ===================== --}}
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <form method="GET" action="{{ route('admin.hari-libur.index') }}" class="w-full sm:w-56">
                    <label for="tahun" class="mb-1 block text-xs font-medium text-gray-500">Tampilkan Tahun</label>
                    <select id="tahun" name="tahun" onchange="this.form.submit()"
                            class="w-full rounded-lg border-blue-100 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        @foreach ($daftarTahun as $pilihan)
                            <option value="{{ $pilihan }}" {{ (int) $pilihan === (int) $tahun ? 'selected' : '' }}>{{ $pilihan }}</option>
                        @endforeach
                    </select>
                </form>

                @if ($daftar->isNotEmpty())
                    <button type="button"
                            data-aksi="{{ route('admin.hari-libur.destroy-tahun') }}"
                            data-tahun="{{ $tahun }}"
                            data-judul="Hapus Semua Tanggal Merah {{ $tahun }}?"
                            data-pesan="Seluruh {{ $daftar->count() }} entri tanggal merah tahun {{ $tahun }} akan dihapus permanen. Absensi pada tanggal-tanggal itu kembali mengikuti jadwal biasa."
                            @click="mulaiHapus($el.dataset)"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50">
                        Hapus Semua Tahun {{ $tahun }}
                    </button>
                @endif
            </div>

            {{-- ===================== DAFTAR ===================== --}}
            @if ($daftar->isEmpty())
                <div class="rounded-2xl border border-dashed border-blue-200 bg-blue-50/40 px-6 py-12 text-center">
                    <p class="text-sm font-semibold text-gray-700">Belum ada tanggal merah pada tahun {{ $tahun }}.</p>
                    <p class="mt-1 text-sm text-gray-500">Tambahkan lewat form di atas, atau tempelkan sekaligus dengan tombol <span class="font-semibold">Tambah Beberapa Sekaligus</span>.</p>
                </div>
            @else
                {{-- Tabel untuk layar lebar --}}
                <div class="hidden overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm lg:block">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Tanggal</th>
                                <th class="px-4 py-3 font-semibold">Nama Libur</th>
                                <th class="px-4 py-3 font-semibold">Jumlah Hari</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($daftar as $libur)
                                <tr class="{{ $libur->sedang_berlangsung ? 'bg-red-50/50' : '' }}">
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-gray-800">{{ $libur->label_tanggal }}</span>
                                        @if ($libur->sedang_berlangsung)
                                            <span class="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700">Hari ini</span>
                                        @elseif ($libur->sudah_lewat)
                                            <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Lewat</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-gray-700">{{ $libur->nama }}</span>
                                        @if ($libur->keterangan)
                                            <span class="block text-xs text-slate-400">{{ $libur->keterangan }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $libur->jumlah_hari }} hari</td>
                                    <td class="px-4 py-3">
                                        @if ($libur->aktif)
                                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Berlaku</span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button"
                                                    data-aksi="{{ route('admin.hari-libur.update', $libur) }}"
                                                    data-nama="{{ $libur->nama }}"
                                                    data-mulai="{{ \Carbon\Carbon::parse($libur->tanggal_mulai)->format('Y-m-d') }}"
                                                    data-selesai="{{ $libur->tanggal_selesai ? \Carbon\Carbon::parse($libur->tanggal_selesai)->format('Y-m-d') : '' }}"
                                                    data-keterangan="{{ $libur->keterangan }}"
                                                    data-label="{{ $libur->label_tanggal }}"
                                                    @click="mulaiUbah($el.dataset)"
                                                    class="rounded-lg border border-blue-100 px-3 py-1.5 text-xs font-medium text-[#2563EB] transition hover:bg-blue-50">
                                                Ubah
                                            </button>

                                            <form method="POST" action="{{ route('admin.hari-libur.toggle', $libur) }}" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit"
                                                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50">
                                                    {{ $libur->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>

                                            <button type="button"
                                                    data-aksi="{{ route('admin.hari-libur.destroy', $libur) }}"
                                                    data-judul="Hapus Tanggal Merah?"
                                                    data-pesan="{{ $libur->nama }} ({{ $libur->label_tanggal }}) akan dihapus permanen. Absensi pada tanggal itu kembali mengikuti jadwal biasa."
                                                    @click="mulaiHapus($el.dataset)"
                                                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Kartu untuk mobile & tablet --}}
                <div class="space-y-3 lg:hidden">
                    @foreach ($daftar as $libur)
                        <div class="rounded-2xl border bg-white p-4 shadow-sm {{ $libur->sedang_berlangsung ? 'border-red-200 bg-red-50/40' : 'border-blue-100' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-800">{{ $libur->nama }}</p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $libur->label_tanggal }}</p>
                                </div>
                                @if ($libur->aktif)
                                    <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Berlaku</span>
                                @else
                                    <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Nonaktif</span>
                                @endif
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 font-medium text-slate-600">{{ $libur->jumlah_hari }} hari</span>
                                @if ($libur->sedang_berlangsung)
                                    <span class="rounded-md bg-red-100 px-2 py-0.5 font-bold text-red-700">Hari ini</span>
                                @elseif ($libur->sudah_lewat)
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 font-medium text-slate-500">Lewat</span>
                                @endif
                            </div>

                            @if ($libur->keterangan)
                                <p class="mt-2 text-xs text-slate-400">{{ $libur->keterangan }}</p>
                            @endif

                            <div class="mt-3 grid grid-cols-3 gap-2">
                                <button type="button"
                                        data-aksi="{{ route('admin.hari-libur.update', $libur) }}"
                                        data-nama="{{ $libur->nama }}"
                                        data-mulai="{{ \Carbon\Carbon::parse($libur->tanggal_mulai)->format('Y-m-d') }}"
                                        data-selesai="{{ $libur->tanggal_selesai ? \Carbon\Carbon::parse($libur->tanggal_selesai)->format('Y-m-d') : '' }}"
                                        data-keterangan="{{ $libur->keterangan }}"
                                        data-label="{{ $libur->label_tanggal }}"
                                        @click="mulaiUbah($el.dataset)"
                                        class="rounded-lg border border-blue-100 px-2 py-2 text-xs font-medium text-[#2563EB] transition hover:bg-blue-50">
                                    Ubah
                                </button>

                                <form method="POST" action="{{ route('admin.hari-libur.toggle', $libur) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                            class="w-full rounded-lg border border-slate-200 px-2 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50">
                                        {{ $libur->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                <button type="button"
                                        data-aksi="{{ route('admin.hari-libur.destroy', $libur) }}"
                                        data-judul="Hapus Tanggal Merah?"
                                        data-pesan="{{ $libur->nama }} ({{ $libur->label_tanggal }}) akan dihapus permanen. Absensi pada tanggal itu kembali mengikuti jadwal biasa."
                                        @click="mulaiHapus($el.dataset)"
                                        class="rounded-lg border border-red-200 px-2 py-2 text-xs font-medium text-red-600 transition hover:bg-red-50">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ==================================================================
             POP UP: UBAH TANGGAL MERAH
             Panel dibatasi tinggi layar lalu isinya yang di-scroll, sehingga
             tombol Simpan tetap terlihat di ponsel.
        ================================================================== --}}
        <template x-teleport="body">
            <div x-show="openUbah" x-cloak
                 class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4">
                <div class="absolute inset-0 bg-black/50" @click="openUbah = false"></div>

                <div x-show="openUbah"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                     class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:max-h-[88vh] sm:max-w-lg sm:rounded-2xl">

                    <div class="flex-none border-b border-[#e6e9ef] px-4 pb-3 pt-3 sm:px-6 sm:pt-5">
                        <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-[#d7dbe3] sm:hidden"></div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-gray-800">Ubah Tanggal Merah</h3>
                                <p class="truncate text-xs text-slate-500" x-text="ubahLabel"></p>
                            </div>
                            <button type="button" @click="openUbah = false" aria-label="Tutup"
                                    class="-mr-1 flex h-8 w-8 flex-none items-center justify-center rounded-lg text-2xl leading-none text-gray-400 transition hover:bg-gray-100 hover:text-black">&times;</button>
                        </div>
                    </div>

                    <form method="POST" x-bind:action="ubahAksi" class="flex min-h-0 flex-1 flex-col">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="ubah">
                        <input type="hidden" name="_aksi" x-bind:value="ubahAksi">
                        <input type="hidden" name="_label" x-bind:value="ubahLabel">

                        <div class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">
                            @if ($formGagal === 'ubah' && $errors->any())
                                <ul class="space-y-1 rounded-lg bg-red-50 px-4 py-3 text-xs text-red-600">
                                    @foreach ($errors->all() as $pesan)
                                        <li>{{ $pesan }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Nama Libur</label>
                                <input type="text" name="nama" required maxlength="120" x-model="ubahNama"
                                       class="w-full rounded-lg border-blue-100 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Tanggal</label>
                                <input type="date" name="tanggal_mulai" required x-model="ubahMulai"
                                       class="w-full rounded-lg border-blue-100 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Tanggal Selesai <span class="text-slate-400">(opsional)</span></label>
                                <input type="date" name="tanggal_selesai" x-model="ubahSelesai"
                                       class="w-full rounded-lg border-blue-100 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Keterangan <span class="text-slate-400">(opsional)</span></label>
                                <input type="text" name="keterangan" maxlength="500" x-model="ubahKeterangan"
                                       class="w-full rounded-lg border-blue-100 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            </div>
                        </div>

                        <div class="flex-none border-t border-[#e6e9ef] bg-white px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] sm:px-6 sm:pb-3">
                            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                <button type="button" @click="openUbah = false"
                                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 sm:w-auto sm:py-2">Batal</button>
                                <button type="submit"
                                        class="w-full rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 sm:w-auto sm:py-2">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ==================================================================
             POP UP: TAMBAH BEBERAPA SEKALIGUS
        ================================================================== --}}
        <template x-teleport="body">
            <div x-show="openMassal" x-cloak
                 class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4">
                <div class="absolute inset-0 bg-black/50" @click="openMassal = false"></div>

                <div x-show="openMassal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                     class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:max-h-[88vh] sm:max-w-xl sm:rounded-2xl">

                    <div class="flex-none border-b border-[#e6e9ef] px-4 pb-3 pt-3 sm:px-6 sm:pt-5">
                        <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-[#d7dbe3] sm:hidden"></div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-gray-800">Tambah Beberapa Sekaligus</h3>
                                <p class="text-xs text-slate-500">Tempelkan kalender libur satu tahun dalam sekali isi.</p>
                            </div>
                            <button type="button" @click="openMassal = false" aria-label="Tutup"
                                    class="-mr-1 flex h-8 w-8 flex-none items-center justify-center rounded-lg text-2xl leading-none text-gray-400 transition hover:bg-gray-100 hover:text-black">&times;</button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.hari-libur.massal') }}" class="flex min-h-0 flex-1 flex-col">
                        @csrf
                        <input type="hidden" name="_form" value="massal">

                        <div class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">
                            @if ($formGagal === 'massal' && $errors->any())
                                <ul class="space-y-1 rounded-lg bg-red-50 px-4 py-3 text-xs text-red-600">
                                    @foreach ($errors->all() as $pesan)
                                        <li>{{ $pesan }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="rounded-lg bg-slate-50 px-3 py-2.5 text-xs text-slate-600">
                                <p class="mb-1 font-semibold text-slate-700">Satu baris satu libur:</p>
                                <p class="font-mono leading-relaxed">2026-01-01 = Tahun Baru</p>
                                <p class="font-mono leading-relaxed">17/08/2026 = Hari Kemerdekaan RI</p>
                                <p class="font-mono leading-relaxed">2026-03-20 .. 2026-03-22 = Cuti Bersama</p>
                                <p class="mt-2 leading-relaxed">
                                    Pemisah nama boleh <span class="font-mono">=</span> <span class="font-mono">|</span> <span class="font-mono">;</span> atau koma.
                                    Untuk rentang gunakan <span class="font-mono">..</span> <span class="font-mono">s/d</span> atau <span class="font-mono">sampai</span>.
                                    Baris yang diawali <span class="font-mono">#</span> dilewati.
                                </p>
                            </div>

                            <div>
                                <label for="daftar" class="mb-1 block text-xs font-medium text-gray-500">Daftar Tanggal Merah</label>
                                <textarea id="daftar" name="daftar" rows="10" required
                                          placeholder="2026-01-01 = Tahun Baru&#10;2026-05-01 = Hari Buruh&#10;2026-08-17 = Hari Kemerdekaan RI"
                                          class="w-full rounded-lg border-blue-100 font-mono text-xs leading-relaxed focus:border-[#2563EB] focus:ring-[#2563EB]">{{ $formGagal === 'massal' ? old('daftar') : '' }}</textarea>
                                <p class="mt-1 text-xs text-slate-400">Maksimal 200 baris sekali kirim. Tanggal yang sudah terdaftar otomatis dilewati.</p>
                            </div>
                        </div>

                        <div class="flex-none border-t border-[#e6e9ef] bg-white px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] sm:px-6 sm:pb-3">
                            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                <button type="button" @click="openMassal = false"
                                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 sm:w-auto sm:py-2">Batal</button>
                                <button type="submit"
                                        class="w-full rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 sm:w-auto sm:py-2">Simpan Semua</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ==================================================================
             POP UP: KONFIRMASI HAPUS
        ================================================================== --}}
        <template x-teleport="body">
            <div x-show="openHapus" x-cloak
                 class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4">
                <div class="absolute inset-0 bg-black/50" @click="openHapus = false"></div>

                <div x-show="openHapus"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                     class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:max-h-[88vh] sm:max-w-md sm:rounded-2xl">

                    <div class="flex-none border-b border-[#e6e9ef] px-4 pb-3 pt-3 sm:px-6 sm:pt-5">
                        <div class="mx-auto mb-3 h-1.5 w-10 rounded-full bg-[#d7dbe3] sm:hidden"></div>
                        <h3 class="pr-8 text-base font-bold text-red-600" x-text="hapusJudul"></h3>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6">
                        <p class="text-sm text-slate-600" x-text="hapusPesan"></p>
                    </div>

                    <div class="flex-none border-t border-[#e6e9ef] bg-white px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] sm:px-6 sm:pb-3">
                        <form method="POST" x-bind:action="hapusAksi">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="tahun" x-bind:value="hapusTahun">
                            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                <button type="button" @click="openHapus = false"
                                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 sm:w-auto sm:py-2">Batal</button>
                                <button type="submit"
                                        class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-red-700 sm:w-auto sm:py-2">Ya, Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function halamanHariLibur() {
            return {
                /* ---- pop up ubah ---- */
                openUbah: false,
                ubahAksi: '',
                ubahNama: '',
                ubahMulai: '',
                ubahSelesai: '',
                ubahKeterangan: '',
                ubahLabel: '',

                /* ---- pop up tambah massal ---- */
                openMassal: false,

                /* ---- pop up konfirmasi hapus ---- */
                openHapus: false,
                hapusAksi: '',
                hapusJudul: '',
                hapusPesan: '',
                hapusTahun: '',

                get adaModal() {
                    return this.openUbah || this.openMassal || this.openHapus;
                },

                tutupSemua() {
                    this.openUbah = false;
                    this.openMassal = false;
                    this.openHapus = false;
                },

                /*
                 * Setelah validasi gagal, buka kembali pop up yang bersangkutan
                 * beserta isian lama pengguna supaya tidak perlu mengetik ulang.
                 */
                pulihkan(data) {
                    if (data.gagal === 'ubah') {
                        this.ubahAksi = data.ubahAksi || '';
                        this.ubahNama = data.ubahNama || '';
                        this.ubahMulai = data.ubahMulai || '';
                        this.ubahSelesai = data.ubahSelesai || '';
                        this.ubahKeterangan = data.ubahKeterangan || '';
                        this.ubahLabel = data.ubahLabel || '';
                        this.openUbah = true;
                    } else if (data.gagal === 'massal') {
                        this.openMassal = true;
                    }
                },

                /*
                 * Data baris dibaca dari atribut data-* tombol lewat $el.dataset.
                 * Cara ini aman untuk nama libur yang memuat petik satu,
                 * misalnya "Isra Mi'raj", karena tidak pernah disisipkan
                 * langsung ke dalam kode JavaScript.
                 */
                mulaiUbah(data) {
                    this.ubahAksi = data.aksi || '';
                    this.ubahNama = data.nama || '';
                    this.ubahMulai = data.mulai || '';
                    this.ubahSelesai = data.selesai || '';
                    this.ubahKeterangan = data.keterangan || '';
                    this.ubahLabel = data.label || '';
                    this.openUbah = true;
                },

                mulaiHapus(data) {
                    this.hapusAksi = data.aksi || '';
                    this.hapusJudul = data.judul || 'Hapus?';
                    this.hapusPesan = data.pesan || '';
                    this.hapusTahun = data.tahun || '';
                    this.openHapus = true;
                },
            };
        }
    </script>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">
                Input Absensi Siswa
            </h2>
            <a href="{{ route('instruktur.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ===== KARTU REKAP ===== --}}
            <div class="mb-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm sm:p-5">
                    <p class="truncate text-xs font-bold uppercase tracking-wide text-[#05b169] sm:text-sm">Hadir</p>
                    <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['Hadir'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm sm:p-5">
                    <p class="truncate text-xs font-bold uppercase tracking-wide text-[#d98200] sm:text-sm">Izin</p>
                    <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['Izin'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm sm:p-5">
                    <p class="truncate text-xs font-bold uppercase tracking-wide text-[#0047d6] sm:text-sm">Sakit</p>
                    <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['Sakit'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 shadow-sm sm:p-5">
                    <p class="truncate text-xs font-bold uppercase tracking-wide text-[#cf202f] sm:text-sm">Alpha</p>
                    <p class="mt-0.5 text-2xl font-extrabold leading-none text-black sm:text-3xl">{{ $rekap['Alpha'] }}</p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                @if(session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ===== FILTER (GET) ===== --}}
                <form action="{{ route('instruktur.absensi.index') }}" method="GET" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Tanggal Absen</label>
                            <input type="date" name="tanggal" value="{{ $tanggal }}"
                                   class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status Kehadiran</label>
                            <select name="status"
                                    class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                                <option value="Izin"  @selected(request('status') === 'Izin')>Izin</option>
                                <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                                <option value="Alpha" @selected(request('status') === 'Alpha')>Alpha</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Tampilkan</button>
                            <a href="{{ route('instruktur.absensi.index') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                {{-- ===== INFO TANGGAL + TOOLBAR JAM FIX ===== --}}
                <div x-data="{
                        bukaFix: false,
                        masuk: $store.jadwal.masuk,
                        pulang: $store.jadwal.pulang,
                    }"
                     class="mb-6 flex flex-col gap-3 rounded-xl border-2 border-[#0047d6]/15 bg-[#0047d6]/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

                    <p class="text-xs font-medium text-black">
                        Mengisi absensi untuk tanggal
                        <span class="font-bold text-[#0047d6]">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</span>.
                        Jam memakai zona <span class="font-bold">WITA</span>.
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                                @click="masuk = $store.jadwal.masuk; pulang = $store.jadwal.pulang; bukaFix = true"
                                class="inline-flex items-center gap-1.5 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/10">
                            ⚙️ Atur Jam Fix
                            <template x-if="$store.jadwal.masuk || $store.jadwal.pulang">
                                <span class="text-[10px] font-semibold text-[#5b616e]">(<span x-text="$store.jadwal.masuk || '--:--'"></span>–<span x-text="$store.jadwal.pulang || '--:--'"></span>)</span>
                            </template>
                        </button>

                        <button type="button"
                                x-show="$store.jadwal.masuk || $store.jadwal.pulang"
                                @click="$dispatch('terapkan-fix')"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-[#0047d6] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                            ⏱️ Terapkan Jam Fix ke Semua
                        </button>
                    </div>

                    {{-- Modal: Atur Jam Fix --}}
                    <div x-show="bukaFix" x-cloak
                         class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4"
                         @keydown.escape.window="bukaFix = false">
                        <div class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-sm sm:rounded-2xl sm:p-6"
                             @click.outside="bukaFix = false" x-transition>
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-bold text-black">Atur Jam Fix</h3>
                                    <p class="text-xs font-medium text-[#5b616e]">Tersimpan di perangkat ini & dipakai sebagai jam default.</p>
                                </div>
                                <button type="button" @click="bukaFix = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Jam Masuk</label>
                                    <input type="time" x-model="masuk"
                                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-2 py-2.5 text-center text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Jam Pulang</label>
                                    <input type="time" x-model="pulang"
                                           class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-2 py-2.5 text-center text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                </div>
                            </div>

                            <div class="mt-5 flex gap-2">
                                <button type="button"
                                        @click="$store.jadwal.setFix(masuk, pulang); bukaFix = false"
                                        class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#0038aa]">
                                    Simpan Jam Fix
                                </button>
                                <button type="button"
                                        @click="$store.jadwal.setFix('', ''); masuk=''; pulang=''; bukaFix = false"
                                        class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== FORM ABSENSI (POST) ===== --}}
                <form action="{{ route('instruktur.absensi.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                    <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead>
                                <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                    <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                    <th class="px-4 py-3 font-bold">Nama Siswa</th>
                                    <th class="px-4 py-3 font-bold">NISN</th>
                                    <th class="px-4 py-3 font-bold w-56">Kehadiran</th>
                                    <th class="px-4 py-3 text-center font-bold w-52">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#0047d6]/10">
                                @forelse($siswas as $siswa)
                                    @php
                                        $absen        = $absensis->get($siswa->id);
                                        $jamMasukVal  = $absen && $absen->jam_masuk  ? substr($absen->jam_masuk, 0, 5)  : '';
                                        $jamPulangVal = $absen && $absen->jam_pulang ? substr($absen->jam_pulang, 0, 5) : '';
                                    @endphp
                                    <tr class="align-top transition hover:bg-[#0047d6]/5"
                                        x-data="barisAbsensi(@js([
                                            'status'    => optional($absen)->status ?? 'Hadir',
                                            'jamMasuk'  => $jamMasukVal,
                                            'jamPulang' => $jamPulangVal,
                                            'sudahAda'  => (bool) $absen,
                                        ]))"
                                        @terapkan-fix.window="terapkanFix()">

                                        <td class="px-4 py-3 text-center font-semibold text-black">{{ ($siswas->firstItem() ?? 1) + $loop->index }}</td>
                                        <td class="px-4 py-3 font-bold text-black">{{ $siswa->name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $siswa->nisn }}</td>

                                        {{-- Kolom Kehadiran (badge, tanpa buka pop up) --}}
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col gap-1">
                                                <span class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-bold"
                                                      :class="badgeClass()"
                                                      x-text="sudahAda ? status : 'Belum diisi'"></span>
                                                <span class="text-xs font-medium text-[#5b616e]" x-show="jamMasuk || jamPulang">
                                                     <span x-text="jamMasuk || '--:--'"></span> – <span x-text="jamPulang || '--:--'"></span> WITA
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Kolom Status Kehadiran (tombol → pop up) --}}
                                        <td class="px-4 py-3 text-center">
                                            <button type="button" @click="bukaModal()"
                                                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm transition sm:w-auto"
                                                    :class="sudahAda
                                                        ? 'border-2 border-[#0047d6]/30 bg-white text-[#0047d6] hover:bg-[#0047d6]/5'
                                                        : 'bg-[#0047d6] text-white hover:bg-[#0038aa]'">
                                                <span x-text="sudahAda ? ' Update Kehadiran' : '＋ Isi Kehadiran'"></span>
                                            </button>

                                            {{-- Modal per siswa --}}
                                            <div x-show="buka" x-cloak
                                                 class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 text-left sm:items-center sm:p-4"
                                                 @keydown.escape.window="buka = false">
                                                <div class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-md sm:rounded-2xl sm:p-6"
                                                     @click.outside="buka = false" x-transition>
                                                    <div class="mb-4 flex items-start justify-between gap-3">
                                                        <div>
                                                            <h3 class="text-base font-bold text-black">Status Kehadiran</h3>
                                                            <p class="text-xs font-medium text-[#5b616e]">{{ $siswa->name }} • {{ $siswa->nisn }}</p>
                                                        </div>
                                                        <button type="button" @click="buka = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                                                    </div>

                                                    <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Status</label>
                                                    <select name="absensi[{{ $siswa->id }}][status]" x-model="status"
                                                            class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                        <option value="Hadir">Hadir</option>
                                                        <option value="Izin">Izin</option>
                                                        <option value="Sakit">Sakit</option>
                                                        <option value="Alpha">Alpha (Tanpa Keterangan)</option>
                                                    </select>

                                                    <div class="mt-4 grid grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Jam Masuk (WITA)</label>
                                                            <input type="time" name="absensi[{{ $siswa->id }}][jam_masuk]" x-model="jamMasuk"
                                                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-2 py-2.5 text-center text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                        </div>
                                                        <div>
                                                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Jam Pulang (WITA)</label>
                                                            <input type="time" name="absensi[{{ $siswa->id }}][jam_pulang]" x-model="jamPulang"
                                                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-2 py-2.5 text-center text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 flex flex-wrap gap-2">
                                                        <button type="button" @click="pakaiJamFix()"
                                                                class="inline-flex items-center gap-1 rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                            Pakai Jam Fix
                                                        </button>
                                                        <button type="button" @click="jamSekarang()"
                                                                class="inline-flex items-center gap-1 rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                            Jam Sekarang (WITA)
                                                        </button>
                                                        <button type="button" @click="jamMasuk=''; jamPulang=''"
                                                                class="inline-flex items-center gap-1 rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-1.5 text-xs font-bold text-[#5b616e] transition hover:bg-black/5">
                                                            Kosongkan Jam
                                                        </button>
                                                    </div>

                                                    <div class="mt-5 flex gap-2">
                                                        <button type="button" @click="simpan()"
                                                                class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#0038aa]">
                                                            Simpan
                                                        </button>
                                                        <button type="button" @click="buka = false"
                                                                class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                                                            Batal
                                                        </button>
                                                    </div>

                                                    <p class="mt-3 text-[11px] font-medium text-[#5b616e]">
                                                        Perubahan baru tersimpan permanen setelah menekan <span class="font-bold">Simpan Absensi</span> di bawah tabel.
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada siswa bimbingan yang di-mapping ke Anda / tidak cocok dengan pencarian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($siswas->count() > 0)
                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30 sm:w-auto">
                                Simpan Absensi
                            </button>
                        </div>
                    @endif
                </form>

                <div class="mt-4">
                    {!! $siswas->links() !!}
                </div>

            </div>
        </div>
    </div>

    {{-- ===== LOGIKA ALPINE ===== --}}
    <script>
        document.addEventListener('alpine:init', () => {
            // Jam fix disimpan di perangkat (tidak butuh migration/DB)
            Alpine.store('jadwal', {
                masuk: localStorage.getItem('absensi_jam_fix_masuk') || '',
                pulang: localStorage.getItem('absensi_jam_fix_pulang') || '',
                setFix(masuk, pulang) {
                    this.masuk = masuk || '';
                    this.pulang = pulang || '';
                    localStorage.setItem('absensi_jam_fix_masuk', this.masuk);
                    localStorage.setItem('absensi_jam_fix_pulang', this.pulang);
                },
            });

            Alpine.data('barisAbsensi', (init) => ({
                buka: false,
                status: init.status,
                jamMasuk: init.jamMasuk,
                jamPulang: init.jamPulang,
                sudahAda: init.sudahAda,

                bukaModal() {
                    // Auto-isi dari jam fix bila masih kosong
                    if (!this.jamMasuk && this.$store.jadwal.masuk) this.jamMasuk = this.$store.jadwal.masuk;
                    if (!this.jamPulang && this.$store.jadwal.pulang) this.jamPulang = this.$store.jadwal.pulang;
                    this.buka = true;
                },
                pakaiJamFix() {
                    this.jamMasuk = this.$store.jadwal.masuk;
                    this.jamPulang = this.$store.jadwal.pulang;
                },
                jamSekarang() {
                    const wita = new Intl.DateTimeFormat('en-GB', {
                        timeZone: 'Asia/Makassar', hour: '2-digit', minute: '2-digit', hour12: false,
                    }).format(new Date());
                    if (!this.jamMasuk) this.jamMasuk = wita;
                    else this.jamPulang = wita;
                },
                terapkanFix() {
                    if (this.$store.jadwal.masuk)  this.jamMasuk = this.$store.jadwal.masuk;
                    if (this.$store.jadwal.pulang) this.jamPulang = this.$store.jadwal.pulang;
                },
                simpan() {
                    this.sudahAda = true;
                    this.buka = false;
                },
                badgeClass() {
                    if (!this.sudahAda) return 'bg-[#5b616e]/15 text-[#5b616e]';
                    return {
                        'Hadir': 'bg-[#05b169] text-white',
                        'Izin':  'bg-[#d98200] text-white',
                        'Sakit': 'bg-[#0047d6] text-white',
                        'Alpha': 'bg-[#cf202f] text-white',
                    }[this.status] || 'bg-[#5b616e] text-white';
                },
            }));
        });
    </script>
</x-app-layout>
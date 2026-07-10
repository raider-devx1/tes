@php
    use App\Models\Jurnal;
    use App\Models\Observasi;
    use App\Models\Absency; // Sesuaikan jika ada typo pada nama model Absensi asli Anda
    use App\Models\Absensi;
    use App\Models\CatatanKegiatan;
    use App\Models\User;

    $me          = auth()->user();
    $hariIni     = \Carbon\Carbon::today();
    $tanggalIndo = $hariIni->locale('id')->translatedFormat('d F Y'); // contoh: 05 Juli 2026
    $bulanIndo   = $hariIni->locale('id')->translatedFormat('F');      // contoh: Juli

    $notifikasi = [];

    /* ================= SISWA PKL ================= */
    if ($me->role === 'siswa_pkl') {
        $sudahIsiJurnalHariIni = Jurnal::where('siswa_id', $me->id)
            ->whereDate('hari_tanggal', $hariIni)
            ->exists();

        if (! $sudahIsiJurnalHariIni) {
            $notifikasi[] = [
                'key'      => 'siswa-jurnal-' . $hariIni->toDateString(),
                'warna'    => 'kuning',
                'judul'    => 'Jurnal Hari Ini Belum Diisi',
                'pesan'    => "Anda belum mengisi jurnal kegiatan PKL untuk tanggal {$tanggalIndo}. "
                            . "Silakan isi jurnal untuk diverifikasi oleh instruktur.",
                'aksi_url' => route('siswa.jurnal.create'),
                'aksi'     => 'Isi Sekarang',
            ];
        }
    }

    /* ============== GURU PEMBIMBING ============== */
    if ($me->role === 'guru_pembimbing') {
        $siswaAktifIds = User::where('role', 'siswa_pkl')
            ->where('guru_id', $me->id)
            ->where('status_pkl', 'aktif')
            ->pluck('id');

        $sudahDiobservasiIds = Observasi::where('guru_id', $me->id)
            ->whereMonth('created_at', $hariIni->month)
            ->whereYear('created_at', $hariIni->year)
            ->pluck('user_id');

        $belumObservasi = $siswaAktifIds->diff($sudahDiobservasiIds)->count();

        if ($belumObservasi > 0) {
            $notifikasi[] = [
                'key'      => 'guru-observasi-' . $hariIni->format('Y-m'),
                'warna'    => 'kuning',
                'judul'    => 'Monitoring PKL Belum Dilakukan',
                'pesan'    => "Masih terdapat {$belumObservasi} siswa yang belum mendapatkan observasi bulan {$bulanIndo}.",
                'aksi_url' => route('guru.observasi.index'),
                'aksi'     => 'Lihat Daftar',
            ];
        }
    }

    /* ============ INSTRUKTUR INDUSTRI ============ */
    if ($me->role === 'instruktur_industri') {
        $siswaAktifIds = User::where('role', 'siswa_pkl')
            ->where('instruktur_id', $me->id)
            ->where('status_pkl', 'aktif')
            ->pluck('id');

        // 1) Persetujuan Jurnal
        $jurnalMenunggu = Jurnal::whereIn('siswa_id', $siswaAktifIds)
            ->where('status_persetujuan', 'pending')
            ->count();

        if ($jurnalMenunggu > 0) {
            $notifikasi[] = [
                'key'      => 'instruktur-jurnal',
                'warna'    => 'biru',
                'judul'    => 'Persetujuan Jurnal',
                'pesan'    => "Ada {$jurnalMenunggu} jurnal siswa yang menunggu persetujuan Anda.",
                'aksi_url' => route('instruktur.jurnal.index'),
                'aksi'     => 'Tinjau Jurnal',
            ];
        }

        // 2) Absensi Hari Ini
        $sudahDiabsen = Absensi::where('instruktur_id', $me->id)
            ->whereDate('tanggal', $hariIni)
            ->distinct('siswa_id')
            ->count('siswa_id');

        $belumDiabsen = max($siswaAktifIds->count() - $sudahDiabsen, 0);

        if ($belumDiabsen > 0) {
            $notifikasi[] = [
                'key'      => 'instruktur-absensi-' . $hariIni->toDateString(),
                'warna'    => 'kuning',
                'judul'    => 'Absensi Hari Ini',
                'pesan'    => "Anda belum mengisi absensi untuk {$belumDiabsen} siswa hari ini ({$tanggalIndo}).",
                'aksi_url' => route('instruktur.absensi.index'),
                'aksi'     => 'Isi Absensi',
            ];
        }

        // 3) Persetujuan Observasi
        $observasiMenunggu = Observasi::whereIn('user_id', $siswaAktifIds)
            ->where('is_approved', false)
            ->count();

        if ($observasiMenunggu > 0) {
            $notifikasi[] = [
                'key'      => 'instruktur-observasi',
                'warna'    => 'biru',
                'judul'    => 'Persetujuan Observasi',
                'pesan'    => "Ada {$observasiMenunggu} lembar observasi yang menunggu persetujuan Anda.",
                'aksi_url' => route('instruktur.observasi.index'),
                'aksi'     => 'Tinjau Observasi',
            ];
        }

        // 4) Persetujuan Catatan
        $catatanMenunggu = CatatanKegiatan::whereIn('user_id', $siswaAktifIds)
            ->where('is_approved', false)
            ->count();

        if ($catatanMenunggu > 0) {
            $notifikasi[] = [
                'key'      => 'instruktur-catatan',
                'warna'    => 'biru',
                'judul'    => 'Persetujuan Catatan',
                'pesan'    => "Ada {$catatanMenunggu} catatan kegiatan yang menunggu persetujuan Anda.",
                'aksi_url' => route('instruktur.catatan.index'),
                'aksi'     => 'Tinjau Catatan',
            ];
        }
    }

    $jumlahNotif = count($notifikasi);
@endphp

@if ($jumlahNotif > 0)
    <div x-data="{
            open: false,
            activeKeys: [
                @foreach ($notifikasi as $n)
                    '{{ $n['key'] }}',
                @endforeach
            ]
         }"
         x-show="activeKeys.length > 0"
         x-cloak
         class="mb-6">

        <button type="button" @click="open = true"
                class="group flex w-full items-center gap-4 rounded-2xl border-2 border-[#0047d6]/20 bg-white p-4 text-left shadow-sm transition hover:border-[#0047d6] hover:bg-[#0047d6]/5 focus:outline-none focus:ring-4 focus:ring-[#0047d6]/25 sm:p-5">

            <span class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#0047d6]/10 text-[#0047d6]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>
                <span class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-[#cf202f] px-1 text-xs font-bold text-white" x-text="activeKeys.length"></span>
            </span>

            <span class="min-w-0 flex-1">
                <span class="block text-sm font-bold text-black sm:text-base">Anda Memiliki <span x-text="activeKeys.length"></span> Notifikasi</span>
                <span class="mt-0.5 block text-sm font-medium text-[#5b616e]">Ada tugas yang perlu ditindaklanjuti. Ketuk untuk melihat detailnya.</span>
            </span>

            <span class="hidden shrink-0 items-center rounded-lg bg-[#0047d6] px-4 py-2 text-sm font-semibold text-white transition group-hover:bg-[#0038aa] sm:inline-flex">
                Lihat Notifikasi
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 shrink-0 text-[#0047d6] sm:hidden">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>

        <div x-cloak x-show="open" style="display: none;"
             class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">

            <div x-show="open" x-transition.opacity
                 @click="open = false"
                 class="absolute inset-0 bg-black/50"></div>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                 class="relative flex max-h-[88vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:mx-4 sm:max-w-lg sm:rounded-2xl">

                <div class="flex items-center justify-between gap-3 border-b border-[#e6e9ef] px-5 py-4">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-black sm:text-lg">Notifikasi</h3>
                        <span class="flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-[#0047d6]/10 px-2 text-xs font-bold text-[#0047d6]" x-text="activeKeys.length"></span>
                    </div>
                    <button type="button" @click="open = false"
                            class="rounded-lg p-1.5 text-[#8a909a] transition hover:bg-black/5 hover:text-black"
                            aria-label="Tutup notifikasi">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-3 overflow-y-auto px-4 py-4 sm:px-5">
                    @foreach ($notifikasi as $n)
                        @php
                            $tema = [
                                'kuning' => [
                                    'ring' => 'border-[#f5b301]/45',
                                    'bg'   => 'bg-[#fff8e6]',
                                    'chip' => 'bg-[#f5b301]/20 text-[#9a6b00]',
                                    'btn'  => 'bg-[#d98200] hover:bg-[#b56d00] focus:ring-[#d98200]/30',
                                ],
                                'biru' => [
                                    'ring' => 'border-[#0047d6]/25',
                                    'bg'   => 'bg-[#eef3ff]',
                                    'chip' => 'bg-[#0047d6]/12 text-[#0047d6]',
                                    'btn'  => 'bg-[#0047d6] hover:bg-[#0038aa] focus:ring-[#0047d6]/30',
                                ],
                            ][$n['warna']];
                        @endphp

                        <div x-data="{ key: '{{ $n['key'] }}' }"
                             x-show="activeKeys.includes(key)"
                             class="flex items-start gap-3 rounded-2xl border-2 {{ $tema['ring'] }} {{ $tema['bg'] }} p-4">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $tema['chip'] }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                                </svg>
                            </span>

                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-bold text-black">{{ $n['judul'] }}</h4>
                                <p class="mt-1 text-sm leading-relaxed text-[#3f4550]">{{ $n['pesan'] }}</p>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <a href="{{ $n['aksi_url'] }}"
                                       class="inline-flex items-center rounded-lg {{ $tema['btn'] }} px-4 py-2 text-sm font-semibold text-white transition focus:outline-none focus:ring-4">
                                         {{ $n['aksi'] }} 
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-[#e6e9ef] px-5 py-3">
                    <button type="button" @click="open = false"
                            class="w-full rounded-lg border-2 border-[#d5d9e0] bg-white px-4 py-2.5 text-sm font-semibold text-[#3f4550] transition hover:bg-[#f3f5f9]">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
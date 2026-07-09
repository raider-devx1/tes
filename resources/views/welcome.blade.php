<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LMS PKL - SMKN 1 Majene</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-white">

    {{-- ===== NAVBAR ===== --}}
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-blue-100">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
         
                <span class="font-semibold text-lg text-slate-900">LMS PKL</span>
            </div>
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                Masuk
            </a>
        </div>
    </header>

    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50 via-white to-white"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-100 rounded-full blur-3xl opacity-60"></div>

        <div class="relative max-w-6xl mx-auto px-6 py-24 md:py-32 text-center">
            <span class="inline-block px-4 py-1.5 rounded-full bg-blue-100 text-blue-700 text-sm font-medium mb-6">
                Sistem Informasi Manajemen PKL
            </span>
            <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-slate-900 leading-tight">
                Kelola Praktik Kerja Lapangan<br class="hidden md:block">
                <span class="text-blue-600">Lebih Mudah &amp; Terpadu</span>
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-lg text-slate-600">
                Platform digital untuk siswa, guru pembimbing, instruktur industri, dan admin dalam
                memantau jurnal, absensi, observasi, dan penilaian PKL secara real-time.
            </p>
            <div class="mt-10 flex items-center justify-center gap-4">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">
                    Masuk ke Akun
                </a>
                <a href="#fitur"
                   class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white text-blue-700 font-medium border border-blue-200 hover:bg-blue-50 transition">
                    Look Fitur
                </a>
            </div>
        </div>
    </section>

    {{-- ===== FITUR ===== --}}
    <section id="fitur" class="max-w-6xl mx-auto px-6 py-20">
        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold text-slate-900">Fitur Utama</h2>
            <p class="mt-3 text-slate-600">Semua kebutuhan pengelolaan PKL dalam satu aplikasi.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            @php
                $fitur = [
                    ['Jurnal Harian', 'Siswa mencatat kegiatan harian, instruktur memberi persetujuan & catatan.'],
                    ['Absensi Digital', 'Rekap kehadiran siswa lengkap dengan jam masuk dan pulang.'],
                    ['Observasi & Monitoring', 'Guru pembimbing memantau perkembangan siswa di lapangan.'],
                    ['Penilaian Terpadu', 'Nilai instruktur & guru otomatis direkap menjadi nilai akhir.'],
                    ['Dokumen PKL', 'Kelola surat tugas, surat penerimaan, dan laporan akhir.'],
                    ['Multi Peran', 'Akses berbeda untuk admin, guru, instruktur, dan siswa.'],
                ];
            @endphp

            @foreach ($fitur as [$judul, $isi])
                <div class="p-6 rounded-2xl border border-slate-100 bg-white hover:shadow-lg hover:border-blue-100 transition">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 font-bold">
                        {{ $loop->iteration }}
                    </div>
                    <h3 class="font-semibold text-lg text-slate-900">{{ $judul }}</h3>
                    <p class="mt-2 text-slate-600 text-sm leading-relaxed">{{ $isi }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== CTA ===== --}}
    <section class="max-w-6xl mx-auto px-6 pb-20">
        <div class="rounded-3xl bg-blue-600 px-8 py-14 text-center text-white shadow-xl shadow-blue-600/20">
            <h2 class="text-3xl font-bold">Siap memulai?</h2>
            <p class="mt-3 text-blue-100">Masuk menggunakan akun yang telah diberikan oleh admin sekolah.</p>
            <a href="{{ route('login') }}"
               class="mt-8 inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white text-blue-700 font-medium hover:bg-blue-50 transition">
                Masuk Sekarang
            </a>
        </div>
    </section>

    {{-- ===== FOOTER ===== --}}
    <footer class="border-t border-slate-100">
        <div class="max-w-6xl mx-auto px-6 py-8 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} SIM PKL — UPTD SMKN 1 Majene. Semua hak dilindungi.
        </div>
    </footer>

</body>
</html>
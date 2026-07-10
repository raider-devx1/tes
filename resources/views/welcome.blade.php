<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LMS PKL - UPTD SMK Negeri 1 Majene</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-white">

    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-blue-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
               
                <div class="leading-tight">
                    <span class="block font-bold text-slate-900 text-sm sm:text-base">LMS PKL</span>
                    <span class="block text-[11px] sm:text-xs text-slate-500">SMK Negeri 1 Majene</span>
                </div>
            </div>
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                Masuk
            </a>
        </div>
    </header>

    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50 via-white to-white"></div>
        <div class="absolute -top-24 -right-24 w-72 h-72 sm:w-96 sm:h-96 bg-blue-100 rounded-full blur-3xl opacity-60"></div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-24 md:py-32 text-center">
            <span class="inline-block px-4 py-1.5 rounded-full bg-blue-100 text-blue-700 text-xs sm:text-sm font-semibold mb-6">
                Kreatif, Inovatif, &amp; Profesional
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-6xl font-extrabold tracking-tight text-slate-900 leading-tight">
                Membangun Masa Depan<br class="hidden md:block">
                <span class="text-blue-600">Bersama SMK Negeri 1 Majene</span>
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-base sm:text-lg text-slate-600">
                Platform digital pengelolaan Praktik Kerja Lapangan (PKL) untuk siswa, guru pembimbing,
                instruktur industri, dan admin. Karena <span class="font-semibold text-slate-800">SMK BISA! SMK SIAP KERJA!</span>
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                <a href="{{ route('login') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">
                    Masuk ke Akun
                </a>
                <a href="#fitur"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-white text-blue-700 font-medium border border-blue-200 hover:bg-blue-50 transition">
                    Lihat Fitur
                </a>
            </div>

            <div class="mt-14 flex flex-wrap items-center justify-center gap-x-8 sm:gap-x-10 gap-y-4 text-slate-600">
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-extrabold text-blue-600">50+</div>
                    <div class="text-xs sm:text-sm">Guru Berpengalaman</div>
                </div>
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-extrabold text-blue-600">5</div>
                    <div class="text-xs sm:text-sm">Program Keahlian</div>
                </div>
                <div class="text-center">
                    <div class="text-xl sm:text-2xl font-extrabold text-blue-600">100%</div>
                    <div class="text-xs sm:text-sm">Siap Kerja</div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
        <div class="text-center mb-10 sm:mb-14">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Program Keahlian</h2>
            <p class="mt-3 text-slate-600">Kompetensi unggulan UPTD SMK Negeri 1 Majene.</p>
        </div>

        <div class="grid gap-5 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $programKeahlian = [
                    ['Tata Busana', 'Merancang, membuat, dan mengelola produk busana yang kreatif dan bernilai jual.'],
                    ['Kuliner', 'Mengolah dan menyajikan makanan serta minuman dengan standar industri.'],
                    ['Kecantikan & Spa', 'Perawatan kecantikan kulit, rambut, dan pelayanan spa profesional.'],
                    ['Perhotelan', 'Pelayanan akomodasi dan tata graha sesuai standar industri perhotelan.'],
                    ['Teknik Jaringan Komputer & Telekomunikasi', 'Membangun, mengonfigurasi, dan memelihara jaringan komputer serta telekomunikasi.'],
                ];
            @endphp

            @foreach ($programKeahlian as [$namaProgram, $deskripsiProgram])
                <div class="p-6 rounded-2xl border border-slate-100 bg-white hover:shadow-lg hover:border-blue-100 transition">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 font-bold">
                         {{ substr($namaProgram, 0, 1) }} 
                    </div>
                    <h3 class="font-semibold text-lg text-slate-900"> {{ $namaProgram }} </h3>
                    <p class="mt-2 text-slate-600 text-sm leading-relaxed"> {{ $deskripsiProgram }} </p>
                </div>
            @endforeach
        </div>
    </section>

    <section id="fitur" class="scroll-mt-20 bg-blue-50/50 border-y border-blue-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
            <div class="text-center mb-10 sm:mb-14">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Fitur Utama Aplikasi PKL</h2>
                <p class="mt-3 text-slate-600">Semua kebutuhan pengelolaan PKL dalam satu aplikasi.</p>
            </div>

            <div class="grid gap-5 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
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
                             {{ substr($judul, 0, 1) }} 
                        </div>
                        <h3 class="font-semibold text-lg text-slate-900"> {{ $judul }} </h3>
                        <p class="mt-2 text-slate-600 text-sm leading-relaxed"> {{ $isi }} </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="max-w-4xl mx-auto px-4 sm:px-6 py-16 sm:py-20 text-center">
        <span class="inline-block px-4 py-1.5 rounded-full bg-blue-100 text-blue-700 text-xs sm:text-sm font-semibold mb-6">Visi Kami</span>
        <p class="text-lg sm:text-xl md:text-2xl font-medium text-slate-800 leading-relaxed">
            "Mewujudkan lembaga pendidikan yang berkarakter, berbudaya, beriman, bertaqwa, profesional,
            berwawasan lingkungan, mampu berwirausaha dan berdaya saing global."
        </p>
    </section>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 pb-16 sm:pb-20">
        <div class="rounded-3xl bg-blue-600 px-6 sm:px-8 py-12 sm:py-14 text-center text-white shadow-xl shadow-blue-600/20">
            <h2 class="text-3xl font-bold">Siap memulai?</h2>
            <p class="mt-3 text-blue-100">Masuk menggunakan akun yang telah diberikan oleh admin sekolah.</p>
            <a href="{{ route('login') }}"
               class="mt-8 inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-white text-blue-700 font-medium hover:bg-blue-50 transition">
                Masuk Sekarang
            </a>
        </div>
    </section>

    <footer class="border-t border-slate-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} LMS PKL — UPTD SMK Negeri 1 Majene. Semua hak dilindungi.
        </div>
    </footer>

    <button x-data="{ show: false }"
            x-init="window.addEventListener('scroll', () => { show = window.scrollY > 300 })"
            x-show="show"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-3"
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            aria-label="Kembali ke atas"
            class="fixed bottom-5 right-5 z-50 flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg shadow-blue-600/30 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-600/25 transition">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
        </svg>
    </button>

</body>
</html>
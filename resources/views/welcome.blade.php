<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LMS PKL — UPTD SMK Negeri 1 Majene</title>

    {{-- Logo tampil di tab / URL title --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>[x-cloak]{display:none!important;}</style>
</head>
<body class="font-sans antialiased text-slate-800 bg-white">

    {{-- ===== HEADER ===== --}}
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-blue-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Negeri 1 Majene" class="h-10 w-10 object-contain">
                <div class="leading-tight">
                    <span class="block font-bold text-slate-900 text-sm sm:text-base">LMS PKL</span>
                    <span class="block text-[11px] sm:text-xs text-[#8B5E34] font-medium">SMK Negeri 1 Majene</span>
                </div>
            </div>
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition shadow-sm">
                Masuk
            </a>
        </div>
    </header>

    {{-- ===== HERO SECTION ===== --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50 via-white to-white"></div>
        <div class="absolute -top-24 -right-24 w-72 h-72 sm:w-96 sm:h-96 bg-blue-100 rounded-full blur-3xl opacity-60"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 sm:w-96 sm:h-96 bg-[#8B5E34]/10 rounded-full blur-3xl opacity-70"></div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 py-16 sm:py-24 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Negeri 1 Majene"
                 class="mx-auto h-24 w-24 sm:h-28 sm:w-28 object-contain mb-6 drop-shadow-sm">

            <span class="inline-block px-4 py-1.5 rounded-full bg-[#8B5E34]/10 text-[#8B5E34] text-xs sm:text-sm font-semibold mb-6">
                Learning Management System PKL
            </span>
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">
                <span class="text-blue-600">LMS PKL</span> SMK Negeri 1 Majene
            </h1>
            <p class="mt-5 max-w-2xl mx-auto text-base sm:text-lg text-slate-600 leading-relaxed">
                Platform digital untuk mengelola seluruh kegiatan Praktik Kerja Lapangan (PKL) —
                dari jurnal harian, absensi, monitoring, hingga penilaian — dalam satu tempat untuk siswa, guru pembimbing, dan admin.
            </p>
            <div class="mt-8">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">
                    Masuk ke Akun
                </a>
            </div>
        </div>
    </section>

    {{-- ===== APA ITU LMS PKL (INFORMASI INTI) ===== --}}
    <section class="max-w-4xl mx-auto px-4 sm:px-6 py-14 sm:py-16">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">Apa itu LMS PKL?</h2>
            <p class="mt-3 text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto">
                LMS PKL adalah sistem informasi Praktik Kerja Lapangan SMK Negeri 1 Majene yang
                menggantikan pencatatan manual menjadi digital, sehingga proses PKL lebih rapi, cepat, dan mudah dipantau.
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-3">
            @php
                $inti = [
                    ['Catat Digital', 'Siswa mengisi jurnal harian dan absensi PKL secara online, kapan saja.'],
                    ['Dipantau Guru', 'Guru pembimbing memantau dan memvalidasi kegiatan siswa dari mana saja.'],
                    ['Nilai & Dokumen', 'Penilaian dan dokumen PKL tersimpan rapi dan otomatis direkap.'],
                ];
            @endphp

            @foreach ($inti as $index => [$judul, $isi])
                <div class="p-6 rounded-2xl border border-slate-100 bg-white hover:shadow-lg transition text-center">
                    <div class="mx-auto w-12 h-12 rounded-xl {{ $index % 2 === 0 ? 'bg-blue-50 text-blue-600' : 'bg-[#8B5E34]/10 text-[#8B5E34]' }} flex items-center justify-center mb-4 font-bold text-lg">
                        {{ substr($judul, 0, 1) }}
                    </div>
                    <h3 class="font-semibold text-lg text-slate-900">{{ $judul }}</h3>
                    <p class="mt-2 text-slate-600 text-sm leading-relaxed">{{ $isi }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== FAQ SECTION ===== --}}
    @if(isset($faq) && $faq->count())
    <section id="faq" class="scroll-mt-20 bg-blue-50/50 border-y border-blue-100">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
            <div class="text-center mb-10 sm:mb-12">
                <span class="inline-block px-4 py-1.5 rounded-full bg-[#8B5E34]/10 text-[#8B5E34] text-sm font-semibold mb-4">Pertanyaan Umum</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">Pertanyaan yang Sering Diajukan</h2>
                <p class="mt-3 text-base sm:text-lg text-slate-600">Cari tahu lebih lanjut seputar LMS PKL SMK Negeri 1 Majene.</p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                @foreach($faq as $i => $item)
                    <div class="rounded-2xl border border-blue-100 bg-white overflow-hidden">
                        <button type="button"
                                @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                                class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left hover:bg-blue-50/40 transition">
                            <span class="font-semibold text-slate-900 text-lg sm:text-xl leading-snug">{{ $item->judul }}</span>
                            <svg class="h-6 w-6 flex-shrink-0 text-[#8B5E34] transition-transform duration-200"
                                 :class="open === {{ $i }} ? 'rotate-180' : ''"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse.duration.200ms x-cloak
                             class="px-6 pb-6 -mt-1 text-slate-700 text-base sm:text-lg leading-relaxed prose prose-lg max-w-none prose-a:text-blue-600">
                            {!! $item->konten !!}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ===== FOOTER ===== --}}
    <footer class="border-t border-slate-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 flex flex-col sm:flex-row items-center justify-center gap-3 text-center text-sm text-slate-500">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-8 object-contain">
            <span>&copy; {{ date('Y') }} LMS PKL — UPTD SMK Negeri 1 Majene. Semua hak dilindungi.</span>
        </div>
    </footer>

    {{-- ===== TOMBOL SCROLL TO TOP ===== --}}
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
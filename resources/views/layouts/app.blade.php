@php
    $authUser = auth()->user();
    // Admin asli SELALU pakai cangkang admin. Guru pembimbing yang ditetapkan admin
    // hanya memakai cangkang admin (sidebar + seluruh menu) saat membuka halaman admin/*,
    // sehingga di halaman guru miliknya sendiri tetap tampil cangkang guru seperti biasa.
    $isAdmin = $authUser && ($authUser->role === 'admin' || ($authUser->is_admin && request()->routeIs('admin.*')));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LMS PKL') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if($isAdmin)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        [x-cloak]{display:none!important;}
        html.sidebar-terbuka .app-sidebar        { transform: translateX(0); }
        html:not(.sidebar-terbuka) .app-sidebar  { transform: translateX(-100%); }
        @media (min-width: 1024px) {
            html.sidebar-terbuka .app-konten { margin-left: 16rem; }
        }
    </style>
    <script>
        (function () {
            var tersimpan = localStorage.getItem('sidebarOpen');
            var terbuka = tersimpan !== null ? tersimpan === 'true' : window.innerWidth >= 1024;
            document.documentElement.classList.toggle('sidebar-terbuka', terbuka);
        })();
    </script>
    @endif
</head>
<body class="font-sans antialiased text-gray-600 bg-slate-50/50">
    {{-- =================================================================== --}}
    {{-- LAYOUT ADMIN                                                        --}}
    {{-- =================================================================== --}}
    @if($isAdmin)
    <div
        x-data="{
            sidebarOpen: localStorage.getItem('sidebarOpen') !== null
                ? localStorage.getItem('sidebarOpen') === 'true'
                : window.innerWidth >= 1024,
            loaded: false
        }"
        x-init="
            $watch('sidebarOpen', value => {
                localStorage.setItem('sidebarOpen', value);
                document.documentElement.classList.toggle('sidebar-terbuka', value);
            });
            $nextTick(() => loaded = true);

            // === Auto-close sidebar di mode MOBILE setelah halaman termuat penuh ===
            // Saat sebuah menu ditekan, kita hanya menandai (flag) di sessionStorage,
            // lalu navigasi berjalan normal. Setelah halaman TUJUAN selesai dimuat
            // sepenuhnya, sidebar akan menutup sendiri dengan animasi halus.
            // Di tampilan laptop / PC ( >= 1024px ) tidak ada perubahan sama sekali.
            if (window.innerWidth < 1024 && sessionStorage.getItem('tutupSetelahLoad') === 'true') {
                sessionStorage.removeItem('tutupSetelahLoad');
                // Pastikan sidebar tampil terbuka dulu saat halaman baru muncul...
                sidebarOpen = true;
                // ...lalu tutup halus setelah halaman benar-benar termuat penuh.
                window.addEventListener('load', () => {
                    setTimeout(() => { sidebarOpen = false; }, 450);
                });
            }"
        class="min-h-screen">
        {{-- ===== SIDEBAR MINIMALIS ===== --}}
        <aside
            class="app-sidebar fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-100 transform overflow-y-auto"
            :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', loaded ? 'transition-transform duration-300 ease-in-out' : '']">
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-base shadow-sm shadow-blue-200">
                        P
                    </div>
                    <span class="font-bold text-slate-800 text-base tracking-tight">
                        LMS <span class="text-blue-600 font-extrabold">PKL</span>
                    </span>
                </div>
                <button @click="sidebarOpen = false"
                    class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors"
                    title="Tutup sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
            {{-- Saat salah satu link di dalam <nav> ditekan pada mode mobile, tandai agar
                 sidebar menutup halus SETELAH halaman tujuan termuat penuh (lihat x-init) --}}
            <nav class="px-4 py-6 space-y-1.5 text-sm"
                @click="if ($event.target.closest('a') && window.innerWidth < 1024) sessionStorage.setItem('tutupSetelahLoad', 'true')">
                @if($authUser && $authUser->is_admin && $authUser->role === 'guru_pembimbing')
                    {{-- Guru pembimbing yang ditetapkan admin: tombol kembali ke dashboard guru miliknya --}}
                    <a href="{{ route('guru.dashboard') }}"
                        class="group flex items-center gap-3 px-3 py-2.5 mb-3 rounded-xl font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-all duration-150">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Kembali ke Dashboard Guru
                    </a>
                @endif
                <div class="px-3 mb-2 text-xs font-semibold text-slate-400 tracking-wider uppercase">Menu Utama</div>
                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-all duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Dashboard
                </a>
                <div class="pt-4 px-3 mb-2 text-xs font-semibold text-slate-400 tracking-wider uppercase">Manajemen</div>
                {{-- Master Data --}}
                <div x-data="{ open: {{ request()->routeIs('admin.siswa.*', 'admin.guru.*', 'admin.instruktur.*', 'admin.periode.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="group w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all duration-150">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Master Data
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="ml-5 mt-1 border-l-2 border-slate-100 pl-3 space-y-1" x-cloak>
                        <a href="{{ route('admin.siswa.index') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.siswa.*') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Data Siswa
                        </a>
                        <a href="{{ route('admin.guru.index') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.guru.*') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Data Guru Pembimbing
                        </a>
                        <a href="{{ route('admin.instruktur.index') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.instruktur.*') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Data Instruktur Industri
                        </a>
                        <a href="{{ route('admin.periode.index') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.periode.*') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Periode PKL
                        </a>
                    </div>
                </div>
                {{-- Monitoring PKL --}}
                <div x-data="{ open: {{ request()->routeIs('admin.monitoring.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="group w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all duration-150">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M2 12h20M12 2v20M4.93 4.93l14.14 14.14M4.93 19.07L19.07 4.93"/>
                            </svg>
                            Monitoring PKL
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="ml-5 mt-1 border-l-2 border-slate-100 pl-3 space-y-1" x-cloak>
                        <a href="{{ route('admin.monitoring.jurnal') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.monitoring.jurnal') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Jurnal Kegiatan
                        </a>
                        <a href="{{ route('admin.monitoring.catatan') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.monitoring.catatan') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Catatan Kegiatan
                        </a>
                        <a href="{{ route('admin.monitoring.absensi') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.monitoring.absensi') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Absensi Siswa
                        </a>
                    </div>
                </div>
                {{-- Evaluasi & Nilai --}}
                <div x-data="{ open: {{ request()->routeIs('admin.evaluasi.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="group w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all duration-150">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            Evaluasi &amp; Nilai
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="ml-5 mt-1 border-l-2 border-slate-100 pl-3 space-y-1" x-cloak>
                        <a href="{{ route('admin.evaluasi.observasi') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.evaluasi.observasi') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Observasi Guru
                        </a>
                        <a href="{{ route('admin.evaluasi.penilaian') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.evaluasi.penilaian') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Penilaian PKL
                        </a>
                    </div>
                </div>
                {{-- Dokumen --}}
                <div x-data="{ open: {{ request()->routeIs('admin.dokumen.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="group w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all duration-150">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                            Dokumen Berkas
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="ml-5 mt-1 border-l-2 border-slate-100 pl-3 space-y-1" x-cloak>
                        <a href="{{ route('admin.dokumen.index') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.dokumen.index') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Dokumen Siswa
                        </a>
                        <a href="{{ route('admin.dokumen.surat-tugas.index') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.dokumen.surat-tugas.*') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Surat Tugas
                        </a>
                    </div>
                </div>
                {{-- Informasi Umum PKL --}}
                <a href="{{ route('admin.informasi.index') }}"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition-all duration-150 {{ request()->routeIs('admin.informasi.*') ? 'bg-blue-50 text-blue-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 {{ request()->routeIs('admin.informasi.*') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    Informasi Umum
                </a>
                <div class="pt-4 px-3 mb-2 text-xs font-semibold text-slate-400 tracking-wider uppercase">Konfigurasi</div>
                {{-- Pengaturan --}}
                <div x-data="{ open: {{ request()->routeIs('admin.riwayat.index', 'admin.akun-admin.*', 'profile.edit') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="group w-full flex items-center justify-between px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all duration-150">
                        <span class="flex items-center gap-3">
                            <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                            </svg>
                            Pengaturan
                        </span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="ml-5 mt-1 border-l-2 border-slate-100 pl-3 space-y-1" x-cloak>
                        <a href="{{ route('admin.riwayat.index') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.riwayat.index') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Riwayat Aktivitas
                        </a>
                        <a href="{{ route('admin.akun-admin.index') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('admin.akun-admin.*') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Kelola Akun Admin
                        </a>
                        <a href="{{ route('profile.edit') }}"
                            class="block px-3 py-2 text-[13px] rounded-lg transition-all {{ request()->routeIs('profile.edit') ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-900' }}">
                            Profil Admin
                        </a>
                    </div>
                </div>
            </nav>
        </aside>
        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" @click="sidebarOpen=false" x-transition.opacity
            class="fixed inset-0 z-30 bg-slate-900/30 lg:hidden backdrop-blur-xs"></div>
        {{-- ===== WRAPPER ===== --}}
        <div class="app-konten flex flex-col min-h-screen"
            :class="[sidebarOpen ? 'lg:ml-64' : 'ml-0', loaded ? 'transition-all duration-300 ease-in-out' : '']">
            {{-- NAVBAR sticky --}}
            <header
                class="sticky top-0 z-20 h-16 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="p-2 rounded-xl text-slate-600 hover:bg-slate-50 transition-colors"
                        title="Buka / tutup menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 class="text-base font-semibold text-slate-800">{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    {{-- Profile dropdown --}}
                    <div x-data="{ openP: false }" class="relative">
                        <button @click="openP=!openP"
                            class="flex items-center gap-2.5 p-1.5 pr-3 rounded-xl hover:bg-slate-50 transition-all">
                            @if(auth()->user()->foto)
                                <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="Foto profil"
                                     class="w-7 h-7 rounded-lg object-cover shadow-sm">
                            @else
                                <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-sm shadow-blue-200">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            @endif
                            <span class="hidden sm:block text-sm font-medium text-slate-700">
                                {{ auth()->user()->name ?? 'Admin' }}
                            </span>
                        </button>
                        <div x-show="openP" @click.outside="openP=false" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white border border-slate-100 rounded-xl shadow-xl overflow-hidden text-sm py-1">
                            <a href="{{ route('profile.edit') }}"
                                class="block px-4 py-2 text-slate-600 hover:bg-slate-50 hover:text-slate-900">Profil Admin</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-blue-600 hover:bg-blue-50/50 font-medium">Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            {{-- Header slot opsional --}}
            @isset($header)
            <div class="px-4 sm:px-6 pt-5">
                <div class="w-full max-w-[1920px] mx-auto">{{ $header }}</div>
            </div>
            @endisset
            {{-- CONTENT --}}
            <main class="flex-1 p-4 sm:p-6">
                <div class="w-full max-w-[1920px] mx-auto">
                    {{ $slot }}
                </div>
            </main>
            {{-- FOOTER ADMIN --}}
            <footer class="border-t border-slate-100 px-4 sm:px-6 py-4 text-xs text-slate-400 bg-white">
                <div class="w-full max-w-[1920px] mx-auto flex flex-col sm:flex-row justify-between gap-2">
                    <span>&copy; {{ date('Y') }} LMS PKL &mdash; SMK</span>
                    <span>Panel Admin &middot; v1.0</span>
                </div>
            </footer>
        </div>
    </div>
    {{-- =================================================================== --}}
    {{-- LAYOUT NON-ADMIN (Fixed Sticky Bottom & Clean Center Alignment)     --}}
    {{-- =================================================================== --}}
    @else
    <div class="min-h-screen flex flex-col bg-slate-50">
        @include('layouts.navigation')
        @isset($header)
        <header class="bg-white border-b border-slate-100">
            <div class="w-full max-w-[1920px] mx-auto py-5 px-4 sm:px-6 lg:px-8 2xl:px-12">
                {{ $header }}
            </div>
        </header>
        @endisset
        {{-- Menggunakan flex-1 mendorong footer agar selalu menempel di bawah --}}
        <main class="flex-1">
            {{ $slot }}
        </main>
        {{-- FOOTER NON-ADMIN --}}
        <footer class="border-t border-slate-100 bg-white w-full">
            <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12 py-5 flex items-center justify-center text-xs font-medium text-slate-400 tracking-wide text-center">
                <div class="flex items-center gap-1.5 justify-center">
                    <span>Copyright</span>
                    <span>&copy; {{ date('Y') }}</span>
                </div>
            </div>
        </footer>
    </div>
    @endif
    @stack('scripts')
    {{-- SweetAlert global: flash message + konfirmasi form --}}
    <script>
        // 1) Flash message (berlaku untuk semua role)
        @if(session('success'))
        document.addEventListener('DOMContentLoaded', () => Swal.fire({
            icon: 'success', title: 'Berhasil',
            text: @json(session('success')),
            timer: 2200, timerProgressBar: true, showConfirmButton: false
        }));
        @endif
        @if(session('error'))
        document.addEventListener('DOMContentLoaded', () => Swal.fire({
            icon: 'error', title: 'Gagal',
            text: @json(session('error'))
        }));
        @endif
        // 2) Konfirmasi untuk SETIAP <form data-confirm="...">
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;
            if (form.dataset.confirmed === 'true') return; // sudah dikonfirmasi
            e.preventDefault();
            Swal.fire({
                title: form.getAttribute('data-confirm') || 'Apakah Anda yakin?',
                text: form.getAttribute('data-confirm-text') || '',
                icon: form.getAttribute('data-confirm-icon') || 'warning',
                showCancelButton: true,
                confirmButtonText: form.getAttribute('data-confirm-yes') || 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#cf202f',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit(); // submit tanpa memicu event lagi
                }
            });
        }, true);
    </script>
</body>
</html>

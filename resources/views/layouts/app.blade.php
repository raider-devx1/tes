@php
    $isAdmin = auth()->check() && auth()->user()->role === 'admin';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LMS PKL') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if($isAdmin)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endif
</head>
<body class="font-sans antialiased text-gray-700 bg-white">

{{-- =================================================================== --}}
{{-- LAYOUT ADMIN                                                        --}}
{{-- =================================================================== --}}
@if($isAdmin)
<div x-data="{ sidebarOpen: false }" class="min-h-screen bg-blue-50/40">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-blue-100 transform transition-transform duration-200 lg:translate-x-0 overflow-y-auto"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="h-16 flex items-center gap-2 px-5 border-b border-blue-100">
            <span class="w-9 h-9 rounded-lg bg-[#2563EB] text-white flex items-center justify-center font-bold">P</span>
            <span class="font-bold text-lg tracking-tight text-gray-800">LMS <span class="text-[#2563EB]">PKL</span></span>
        </div>

        <nav class="px-3 py-4 space-y-1 text-sm">

            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-[#2563EB]' : 'text-gray-700 hover:bg-blue-50/50' }}">
                <span>📊</span> Dashboard
            </a>

            {{-- Master Data --}}
          <div x-data="{ open: {{ request()->routeIs('admin.siswa.*', 'admin.guru.*', 'admin.instruktur.*', 'admin.periode.*') ? 'true' : 'false' }} }">
    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50">
        <span class="flex items-center gap-3 font-medium"><span>🗂️</span> Master Data</span>
        <svg class="w-4 h-4 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-blue-100 pl-3" x-cloak>
        
        <a href="{{ route('admin.siswa.index') }}"
           class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.siswa.*') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' }}">
            Data Siswa
        </a>

        <a href="{{ route('admin.guru.index') }}"
           class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.guru.*') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' }}">
            Data Guru Pembimbing
        </a>

        <a href="{{ route('admin.instruktur.index') }}"
           class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.instruktur.*') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' }}">
            Data Instruktur Industri
        </a>

        <a href="{{ route('admin.periode.index') }}"
           class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.periode.*') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' }}">
            Periode PKL
        </a>
        
    </div>
</div>

            {{-- Monitoring PKL --}}
          <div x-data="{ open: {{ request()->routeIs('admin.monitoring.*') ? 'true' : 'false' }} }">
    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50">
        <span class="flex items-center gap-3 font-medium"><span>📡</span> Monitoring PKL</span>
        <svg class="w-4 h-4 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-blue-100 pl-3">
        <a href="<?= e(route('admin.monitoring.jurnal')) ?>"
           class="block px-3 py-2 rounded-lg <?= request()->routeIs('admin.monitoring.jurnal') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' ?>">
            Jurnal Kegiatan
        </a>
        <a href="<?= e(route('admin.monitoring.catatan')) ?>"
           class="block px-3 py-2 rounded-lg <?= request()->routeIs('admin.monitoring.catatan') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' ?>">
            Catatan Kegiatan
        </a>
        <a href="<?= e(route('admin.monitoring.absensi')) ?>"
           class="block px-3 py-2 rounded-lg <?= request()->routeIs('admin.monitoring.absensi') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' ?>">
            Absensi Siswa
        </a>
    </div>
</div>

            {{-- Evaluasi & Nilai --}}
            <div x-data="{ open: <?= request()->routeIs('admin.evaluasi.*') ? 'true' : 'false' ?> }">
    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50">
        <span class="flex items-center gap-3 font-medium"><span>📝</span> Evaluasi & Nilai</span>
        <svg class="w-4 h-4 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-blue-100 pl-3">
        <a href="<?= e(route('admin.evaluasi.observasi')) ?>"
           class="block px-3 py-2 rounded-lg <?= request()->routeIs('admin.evaluasi.observasi') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' ?>">
            Observasi Guru
        </a>
        <a href="<?= e(route('admin.evaluasi.penilaian')) ?>"
           class="block px-3 py-2 rounded-lg <?= request()->routeIs('admin.evaluasi.penilaian') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' ?>">
            Penilaian PKL
        </a>
        <a href="<?= e(route('admin.evaluasi.rekap')) ?>"
           class="block px-3 py-2 rounded-lg <?= request()->routeIs('admin.evaluasi.rekap') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' ?>">
            Rekap Penilaian
        </a>
    </div>
</div>

           <div x-data="{ open: {{ request()->routeIs('admin.dokumen.*') ? 'true' : 'false' }} }">
    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50">
        <span class="flex items-center gap-3 font-medium"><span>📁</span> Dokumen</span>
        <svg class="w-4 h-4 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-blue-100 pl-3" x-cloak>

        <a href="{{ route('admin.dokumen.index') }}"
           class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.dokumen.index') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' }}">
            Dokumen Siswa
        </a>

        <a href="{{ route('admin.dokumen.surat-tugas.index') }}"
           class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.dokumen.surat-tugas.*') ? 'bg-blue-50 text-[#2563EB] font-medium' : 'text-gray-600 hover:bg-blue-50' }}">
            Surat Tugas
        </a>

    </div>
</div>

            {{-- Informasi Umum PKL --}}
            <a href="{{ route('admin.informasi.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium {{ request()->routeIs('admin.informasi.*') ? 'bg-blue-50 text-[#2563EB]' : 'text-gray-700 hover:bg-blue-50/50' }}">
                <span>ℹ️</span> Informasi Umum PKL
            </a>

            {{-- Pengaturan --}}
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-gray-700 hover:bg-blue-50">
                    <span class="flex items-center gap-3 font-medium"><span>⚙️</span> Pengaturan</span>
                    <svg class="w-4 h-4 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse class="ml-4 mt-1 space-y-1 border-l border-blue-100 pl-3">
                    <a href="{{ route('admin.riwayat.index') }}"
   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
    Riwayat Aktivitas
</a>
                    <a href="#" class="block px-3 py-2 rounded-lg text-gray-400 cursor-not-allowed">Manajemen User <span class="text-[10px]">(segera)</span></a>
                    <a href="#" class="block px-3 py-2 rounded-lg text-gray-400 cursor-not-allowed">Notifikasi <span class="text-[10px]">(segera)</span></a>
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50">Profil Admin</a>
                </div>
            </div>
        </nav>
    </aside>

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" @click="sidebarOpen=false" x-transition.opacity class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    {{-- ===== WRAPPER ===== --}}
    <div class="lg:ml-64 flex flex-col min-h-screen">

        {{-- NAVBAR sticky (tanpa notification center) --}}
        <header class="sticky top-0 z-20 h-16 bg-white border-b border-blue-100 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen=true" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-blue-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
            </div>

            <div class="flex items-center gap-2">
                {{-- Profile dropdown --}}
                <div x-data="{ openP: false }" class="relative">
                    <button @click="openP=!openP" class="flex items-center gap-2 p-1.5 pr-2 rounded-lg hover:bg-blue-50">
                        <span class="w-8 h-8 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-bold text-sm">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </button>
                    <div x-show="openP" @click.outside="openP=false" x-transition class="absolute right-0 mt-2 w-48 bg-white border border-blue-100 rounded-xl shadow-lg overflow-hidden text-sm">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-gray-700 hover:bg-blue-50">Profil Admin</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-[#2563EB] hover:bg-blue-50">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Header slot opsional --}}
        @isset($header)
            <div class="px-4 sm:px-6 pt-4">{{ $header }}</div>
        @endisset

        {{-- CONTENT --}}
        <main class="flex-1 p-4 sm:p-6">
            @if(session('success'))
                <script>document.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Berhasil',text:@json(session('success')),timer:2200,showConfirmButton:false}));</script>
            @endif
            @if(session('error'))
                <script>document.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'error',title:'Gagal',text:@json(session('error'))}));</script>
            @endif

            {{ $slot }}
        </main>

        {{-- FOOTER --}}
        <footer class="border-t border-blue-100 px-6 py-4 text-sm text-gray-500 flex flex-col sm:flex-row justify-between gap-2">
            <span>© {{ date('Y') }} LMS PKL — SMK</span>
            <span>Panel Admin · v1.0</span>
        </footer>
    </div>
</div>

{{-- =================================================================== --}}
{{-- LAYOUT NON-ADMIN (Breeze bawaan)                                    --}}
{{-- =================================================================== --}}
@else
<div class="min-h-screen bg-gray-100">
    @include('layouts.navigation')

    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                 {{ $header }}
            </div>
        </header>
    @endisset

    <main>
         {{ $slot }}
    </main>
</div>
@endif

@stack('scripts')
</body>
</html>

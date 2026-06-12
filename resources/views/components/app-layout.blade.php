<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LMS PKL') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<div x-data="{ sidebar: false }" class="min-h-full">
    <div x-show="sidebar" x-cloak @click="sidebar = false" class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 w-64 transform overflow-y-auto bg-slate-900 px-4 py-6 text-slate-200 transition-transform duration-200 lg:translate-x-0">
        <div class="mb-8 flex items-center gap-2 px-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 font-bold text-white">P</div>
            <span class="text-lg font-semibold text-white">LMS PKL</span>
        </div>
        <x-nav-sidebar />
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 shadow-sm sm:px-6">
            <button @click="sidebar = true" class="text-slate-500 lg:hidden" aria-label="Buka menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
            </button>
            <div class="flex-1"></div>
            <a href="{{ route('notifikasi.index') }}" class="relative text-slate-500 hover:text-slate-700" aria-label="Notifikasi">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                @if(($jumlahNotifikasiBelumDibaca ?? 0) > 0)
                    <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $jumlahNotifikasiBelumDibaca }}</span>
                @endif
            </a>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ auth()->user()->name }}</span>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                    <div class="border-b border-slate-100 px-4 py-2 text-xs text-slate-400">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</div>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-slate-50">Keluar</button></form>
                </div>
            </div>
        </header>
        <main class="px-4 py-6 sm:px-6 lg:px-8">
            <x-alert />
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>

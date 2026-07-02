<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - SIM PKL</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">

    <div class="min-h-screen grid lg:grid-cols-2">

        {{-- ===== PANEL KIRI (BRANDING) ===== --}}
        <div class="relative hidden lg:flex flex-col justify-between p-12 bg-blue-600 text-white overflow-hidden">
            <div class="absolute -top-20 -left-20 w-80 h-80 bg-blue-500 rounded-full blur-3xl opacity-50"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-700 rounded-full blur-3xl opacity-40"></div>

            <a href="{{ url('/') }}" class="relative flex items-center gap-2">
           
                <span class="font-semibold text-lg">LMS PKL</span>
            </a>

            <div class="relative">
                <h1 class="text-4xl font-bold leading-tight">Selamat Datang Kembali</h1>
                <p class="mt-4 text-blue-100 max-w-sm">
                    Masuk untuk mengakses jurnal, absensi, observasi, dan penilaian PKL sesuai peran Anda.
                </p>
            </div>

            <p class="relative text-sm text-blue-200">
                &copy; {{ date('Y') }} UPTD SMKN 1 Majene
            </p>
        </div>

        {{-- ===== PANEL KANAN (FORM) ===== --}}
        <div class="flex items-center justify-center p-6 sm:p-12 bg-slate-50">
            <div class="w-full max-w-md">

                {{-- ----- Logo Mobile ----- --}}
                <a href="{{ url('/') }}" class="lg:hidden flex items-center justify-center gap-2 mb-8">
                    <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold">P</div>
                    <span class="font-semibold text-lg text-slate-900">SIM PKL</span>
                </a>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                    <h2 class="text-2xl font-bold text-slate-900">Masuk ke Akun</h2>
                    <p class="mt-1 text-sm text-slate-500">Gunakan kredensial yang diberikan admin sekolah.</p>

                    {{-- ----- Status Sesi ----- --}}
                    @if (session('status'))
                        <div class="mt-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                             {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                        @csrf

                        {{-- ----- Login (NISN / NIP / Email) ----- --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                NISN / NIP / Email
                            </label>
                            <input id="login" name="login" type="text" value="{{ old('login') }}"
                                   required autofocus autocomplete="username"
                                   class="block w-full rounded-lg border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 shadow-sm
                                          focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 @error('login') border-red-400 @enderror">
                            @error('login')
                                <p class="mt-1.5 text-sm text-red-600"> {{ $message }} </p>
                            @enderror
                            <p class="mt-1.5 text-xs text-slate-500">
                                Siswa: <strong>NISN</strong> &middot; Guru: <strong>NIP</strong> &middot; Admin/Instruktur: <strong>Email</strong>.
                            </p>
                        </div>

                        {{-- ----- Password ----- --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Password
                            </label>
                            <input id="password" name="password" type="password"
                                   required autocomplete="current-password"
                                   class="block w-full rounded-lg border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 shadow-sm
                                          focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 @error('password') border-red-400 @enderror">
                            @error('password')
                                <p class="mt-1.5 text-sm text-red-600"> {{ $message }} </p>
                            @enderror
                        </div>

                        {{-- ----- Remember + Lupa Password ----- --}}
                        <div class="flex items-center justify-between">
                            <label class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" name="remember"
                                       class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ms-2 text-sm text-slate-600">Ingat saya</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                                    Lupa password?
                                </a>
                            @endif
                        </div>

                        {{-- ----- Tombol Submit ----- --}}
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5
                                       text-white font-medium hover:bg-blue-700 transition shadow-sm shadow-blue-600/20
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Masuk
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-slate-400">
                    Belum punya akun? Hubungi admin sekolah untuk pembuatan akun.
                </p>
            </div>
        </div>
    </div>

</body>
</html>
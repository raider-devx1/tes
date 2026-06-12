<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS PKL - SMKN 1 Majene</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-800 antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center px-6">
        <div class="w-full max-w-2xl text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-indigo-700 mb-4">
                Sistem Informasi PKL
            </h1>
            <p class="text-lg text-gray-600 mb-10">
                Learning Management System Praktik Kerja Lapangan<br>
                SMK Negeri 1 Majene
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="/dashboard"
                       class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold shadow hover:bg-indigo-700 transition">
                        Masuk Dashboard
                    </a>
                @else
                    <a href="/login"
                       class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-semibold shadow hover:bg-indigo-700 transition">
                        Login
                    </a>
                    <a href="/register"
                       class="px-8 py-3 bg-white text-indigo-600 border border-indigo-600 rounded-lg font-semibold shadow hover:bg-indigo-50 transition">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>

        <footer class="mt-16 text-sm text-gray-400">
            &copy; <?php echo date('Y'); ?> SMKN 1 Majene
        </footer>
    </div>
</body>
</html>
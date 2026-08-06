<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Jumlah percobaan gagal sebelum login dikunci sementara.
     */
    private const MAKS_PERCOBAAN = 5;

    /**
     * Mode pesan peringatan.
     *
     * false = pesan seragam  -> "NISN/NIP atau password yang Anda masukkan salah."  << AKTIF
     *         Lebih aman karena tidak membocorkan NISN/NIP mana yang terdaftar.
     * true  = pesan spesifik -> "NISN/NIP tidak terdaftar" ATAU "Password salah".
     *         Lebih ramah, tetapi memberi tahu akun mana yang ada di database.
     *
     * Cukup ubah true/false di sini, tidak perlu mengubah kode lain.
     */
    private const PESAN_SPESIFIK = false;

    /**
     * Pesan tunggal yang dipakai saat mode seragam aktif.
     */
    private const PESAN_SERAGAM = 'NIS/NIP atau password yang Anda masukkan salah.';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field login generik: bisa NISN (siswa), NIP (guru), atau Email (admin/instruktur).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Pesan validasi dasar (kolom kosong / format salah) dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required'    => 'NIS/NIP wajib diisi.',
            'login.string'      => 'Format NIS/NIP tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.string'   => 'Format password tidak valid.',
        ];
    }

    /**
     * Nama field yang tampil di pesan error bawaan Laravel.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'login'    => 'NISN/NIP',
            'password' => 'Password',
        ];
    }

    /**
     * Autentikasi berdasarkan NISN / NIP / Email.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Rapikan input: spasi, tab, baris baru, dan spasi tak-terputus (U+00A0)
        // sering ikut terbawa saat NIP/NISN disalin dari Excel atau WhatsApp.
        $login = preg_replace('/[\s\x{00A0}]+/u', '', (string) $this->input('login'));

        // Laravel SENGAJA tidak memangkas spasi pada field password
        // (lihat $except pada middleware TrimStrings). Versi rapi disiapkan
        // sebagai cadangan pencocokan.
        $passwordRapi = trim((string) $this->input('password'));

        // Cari user: prioritas NISN -> NIP -> Email.
        $user = User::query()
            ->where(function ($query) use ($login) {
                $query->where('nisn', $login)
                      ->orWhere('nip', $login)
                      ->orWhere('email', $login);
            })
            ->first();

        // ------------------------------------------------------------------
        // KASUS 1: NISN/NIP (atau email) tidak ditemukan di database
        // ------------------------------------------------------------------
        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            $pesan = self::PESAN_SPESIFIK
                ? 'NIS/NIP tidak terdaftar. Periksa kembali penulisannya atau hubungi admin sekolah.'
                : self::PESAN_SERAGAM;

            throw ValidationException::withMessages([
                'login' => $pesan . $this->sisaPercobaan(),
            ]);
        }

        // ------------------------------------------------------------------
        // KASUS 2: akun ditemukan, tetapi password tidak cocok
        // ------------------------------------------------------------------
        $cocok = Hash::check((string) $this->input('password'), $user->password)
            || Hash::check($passwordRapi, $user->password);

        if (! $cocok) {
            RateLimiter::hit($this->throttleKey());

            $field = self::PESAN_SPESIFIK ? 'password' : 'login';

            $pesan = self::PESAN_SPESIFIK
                ? 'Password salah. Perhatikan huruf besar/kecil dan pastikan Caps Lock tidak aktif.'
                : self::PESAN_SERAGAM;

            throw ValidationException::withMessages([
                $field => $pesan . $this->sisaPercobaan(),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Info tambahan "sisa percobaan" supaya pengguna tidak kaget saat terkunci.
     * Hanya muncul ketika sisa percobaan tinggal 1-2 kali.
     */
    private function sisaPercobaan(): string
    {
        $sisa = self::MAKS_PERCOBAAN - RateLimiter::attempts($this->throttleKey());

        if ($sisa > 0 && $sisa <= 2) {
            return ' Sisa ' . $sisa . ' kali percobaan sebelum login dikunci sementara.';
        }

        return '';
    }

    /**
     * Peringatan saat percobaan login terlalu sering.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAKS_PERCOBAAN)) {
            return;
        }

        event(new Lockout($this));

        $detik = RateLimiter::availableIn($this->throttleKey());

        $tunggu = $detik >= 60
            ? ((int) ceil($detik / 60)) . ' menit'
            : $detik . ' detik';

        throw ValidationException::withMessages([
            'login' => 'Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam ' . $tunggu . '.',
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')) . '|' . $this->ip());
    }
}

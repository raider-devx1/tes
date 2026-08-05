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
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field login sekarang generik: bisa NISN (siswa), NIP (guru), atau Email (admin/instruktur).
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
        // orWhere dibungkus satu grup agar tidak bertabrakan dengan penyaring
        // lain (mis. soft delete) dan tidak salah mengambil baris.
        $user = User::query()
            ->where(function ($query) use ($login) {
                $query->where('nisn', $login)
                      ->orWhere('nip', $login)
                      ->orWhere('email', $login);
            })
            ->first();

        // Cocokkan password apa adanya lebih dulu, lalu versi yang sudah
        // dirapikan. Ini menyelamatkan akun yang password tersimpannya
        // terlanjur mengandung spasi hasil salin-tempel admin.
        $cocok = $user && (
            Hash::check($this->input('password'), $user->password)
            || Hash::check($passwordRapi, $user->password)
        );

        if (! $cocok) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')) . '|' . $this->ip());
    }
}
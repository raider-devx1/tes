<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 *
 * CATATAN:
 * Factory ini ditambahi "state" per peran (admin/guru/wakasek/siswa) supaya
 * tes tidak perlu menulis ulang kolom yang sama berkali-kali.
 *
 * Kolom `role` di database hanya menerima tiga nilai:
 *   admin | guru_pembimbing | siswa_pkl
 * Wakasek BUKAN role tersendiri, melainkan guru_pembimbing dengan is_wakasek.
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    // =================================================================
    // STATE PERAN
    // =================================================================

    /** Admin penuh. */
    public function admin(): static
    {
        return $this->state(fn () => [
            'role'     => 'admin',
            'is_admin' => true,
        ]);
    }

    /**
     * Guru pembimbing biasa.
     * NIP dibuat unik agar tidak bentrok dengan constraint unique.
     */
    public function guru(): static
    {
        return $this->state(fn () => [
            'role' => 'guru_pembimbing',
            'nip'  => (string) fake()->unique()->numerify('###########'),
        ]);
    }

    /** Guru pembimbing yang ditetapkan sebagai Wakasek. */
    public function wakasek(): static
    {
        return $this->guru()->state(fn () => [
            'is_wakasek' => true,
        ]);
    }

    /**
     * Guru pembimbing yang juga diberi akses panel admin.
     * Dipakai untuk menguji cabang is_admin pada middleware CheckRole.
     */
    public function guruMerangkapAdmin(): static
    {
        return $this->guru()->state(fn () => [
            'is_admin' => true,
        ]);
    }

    /** Siswa PKL. NISN dibuat unik karena kolomnya unique. */
    public function siswa(): static
    {
        return $this->state(fn () => [
            'role'       => 'siswa_pkl',
            'nisn'       => (string) fake()->unique()->numerify('##########'),
            'kelas'      => 'XII RPL 1',
            'jurusan'    => 'Rekayasa Perangkat Lunak',
            'status_pkl' => 'aktif',
        ]);
    }

    /** Siswa yang absensinya dibuka bebas waktu (mengabaikan jadwal jam). */
    public function absensiDibuka(): static
    {
        return $this->state(fn () => [
            'absensi_dibuka' => true,
        ]);
    }
}

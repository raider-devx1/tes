<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Rapikan input sebelum divalidasi.
     *
     * Pengguna sering mengetik nomor HP dengan spasi ganda, tanda kurung, atau
     * titik. Tanpa dirapikan, "0812  3456 7890" bisa gagal hanya karena spasi
     * berlebih, dan kotak yang sengaja dikosongkan akan tersimpan sebagai
     * string kosong ('') alih-alih NULL. Keduanya dibereskan di sini.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('no_hp')) {
            return;
        }

        $nomor = (string) $this->input('no_hp');

        // Buang karakter selain angka, '+', spasi, dan strip.
        $nomor = preg_replace('/[^0-9+\- ]/', '', $nomor) ?? '';
        // Mampatkan spasi & strip beruntun menjadi satu.
        $nomor = preg_replace('/\s+/', ' ', $nomor) ?? '';
        $nomor = preg_replace('/-{2,}/', '-', $nomor) ?? '';
        $nomor = trim($nomor, ' -');

        // Tanda '+' hanya masuk akal di posisi paling depan (kode negara).
        if (str_contains($nomor, '+')) {
            $nomor = (str_starts_with($nomor, '+') ? '+' : '') . str_replace('+', '', $nomor);
        }

        $this->merge([
            'no_hp' => $nomor === '' ? null : $nomor,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],

            // --- Nomor HP: boleh diubah SEMUA peran (siswa, guru pembimbing, admin) ---
            // Kolom users.no_hp berukuran VARCHAR(20), jadi batas 20 karakter di
            // sini sengaja menyamai batas kolom supaya nomor tidak pernah
            // terpotong diam-diam oleh database.
            //
            // Boleh dikosongkan; mengosongkan berarti menghapus nomor lama.
            // Pola yang diterima: diawali angka atau '+', lalu angka yang boleh
            // dipisah spasi atau strip. Contoh sah:
            //   081234567890     0812-3456-7890     +62 812 3456 7890
            'no_hp' => [
                'nullable',
                'string',
                'min:9',
                'max:20',
                'regex:/^\+?[0-9]+(?:[ -][0-9]+)*$/',
            ],

            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'], // maks 2MB
        ];

        // Email hanya divalidasi (dan boleh diubah) oleh instruktur & admin.
        if (in_array($this->user()->role, ['admin'], true)) {
            $rules['email'] = [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ];
        }

        return $rules;
    }

    /**
     * Pesan galat berbahasa Indonesia khusus nomor HP.
     */
    public function messages(): array
    {
        return [
            'no_hp.min'   => 'Nomor HP terlalu pendek. Isi minimal 9 karakter, atau kosongkan saja bila memang tidak ada.',
            'no_hp.max'   => 'Nomor HP maksimal 20 karakter.',
            'no_hp.regex' => 'Format nomor HP tidak dikenali. Gunakan angka saja, boleh diawali "+" dan dipisah spasi atau strip. Contoh: 081234567890 atau +62 812-3456-7890.',
        ];
    }

    /**
     * Nama ramah supaya pesan galat bawaan Laravel tidak menyebut "no hp".
     */
    public function attributes(): array
    {
        return [
            'name'  => 'nama',
            'no_hp' => 'nomor HP',
            'foto'  => 'foto profil',
        ];
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InstrukturController extends Controller
{
    /** Validasi akun instruktur + data industri (dipakai store & update). */
    private function validateData(Request $request, ?User $instruktur = null): array
    {
        return $request->validate([
            // --- Akun instruktur ---
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($instruktur?->id)],
            'jabatan'  => ['nullable', 'string', 'max:100'],
            'no_hp'    => ['nullable', 'string', 'max:20'],
            'password' => [$instruktur ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            // --- Data industri (ditulis langsung) ---
            'nama_perusahaan' => ['required', 'string', 'max:150'],
            'bidang_usaha'    => ['nullable', 'string', 'max:150'],
            'alamat'          => ['required', 'string', 'max:255'],
            'telepon'         => ['nullable', 'string', 'max:20'],
            'kuota'           => ['required', 'integer', 'min:0', 'max:1000'],
        ]);
    }

    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $instruktur = User::query()
            ->where('role', 'instruktur_industri')
            ->with('perusahaan')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('jabatan', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.instruktur.index', compact('instruktur', 'q'));
    }

    public function create()
    {
        // Tidak perlu daftar perusahaan lagi — diketik langsung.
        return view('admin.instruktur.create', ['instruktur' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data) {
            // 1) Buat data industri
            $perusahaan = Perusahaan::create([
                'nama_perusahaan'     => $data['nama_perusahaan'],
                'bidang_usaha'        => $data['bidang_usaha'] ?? null,
                'alamat'              => $data['alamat'],
                'telepon'             => $data['telepon'] ?? null,
                'pembimbing_industri' => $data['name'], // = nama instruktur
                'kuota'               => $data['kuota'],
            ]);

            // 2) Buat akun instruktur & tautkan ke industri
            User::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'jabatan'       => $data['jabatan'] ?? null,
                'no_hp'         => $data['no_hp'] ?? null,
                'role'          => 'instruktur_industri',
                'perusahaan_id' => $perusahaan->id,
                'password'      => Hash::make($data['password']),
            ]);
        });

        return redirect()->route('admin.instruktur.index')
            ->with('success', 'Instruktur & data industrinya berhasil ditambahkan.');
    }

    public function edit(User $instruktur)
    {
        $instruktur->load('perusahaan');
        return view('admin.instruktur.edit', ['instruktur' => $instruktur]);
    }

    public function update(Request $request, User $instruktur)
    {
        $data = $this->validateData($request, $instruktur);

        DB::transaction(function () use ($data, $instruktur) {
            $perusahaanData = [
                'nama_perusahaan'     => $data['nama_perusahaan'],
                'bidang_usaha'        => $data['bidang_usaha'] ?? null,
                'alamat'              => $data['alamat'],
                'telepon'             => $data['telepon'] ?? null,
                'pembimbing_industri' => $data['name'],
                'kuota'               => $data['kuota'],
            ];

            // Perbarui industri tertaut; buat baru bila belum ada
            if ($instruktur->perusahaan) {
                $instruktur->perusahaan->update($perusahaanData);
                $perusahaanId = $instruktur->perusahaan->id;
            } else {
                $perusahaanId = Perusahaan::create($perusahaanData)->id;
            }

            $payload = [
                'name'          => $data['name'],
                'email'         => $data['email'],
                'jabatan'       => $data['jabatan'] ?? null,
                'no_hp'         => $data['no_hp'] ?? null,
                'perusahaan_id' => $perusahaanId,
            ];
            if (!empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $instruktur->update($payload);
        });

        return redirect()->route('admin.instruktur.index')
            ->with('success', 'Instruktur & data industrinya berhasil diperbarui.');
    }

    public function destroy(User $instruktur)
    {
        $perusahaan = $instruktur->perusahaan;
        $instruktur->delete();

        // Hapus industri tertaut hanya jika tidak dipakai siswa
        // maupun instruktur lain (mencegah data yatim).
        if ($perusahaan
            && !$perusahaan->siswa()->exists()
            && !User::where('perusahaan_id', $perusahaan->id)->exists()) {
            $perusahaan->delete();
        }

        return back()->with('success', 'Akun instruktur industri berhasil dihapus.');
    }
}
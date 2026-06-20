<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InstrukturController extends Controller
{
    /** Validasi akun instruktur (dipakai store & update). */
    private function validateData(Request $request, ?User $instruktur = null): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($instruktur?->id)],
            'jabatan'       => ['nullable', 'string', 'max:100'],
            'no_hp'         => ['nullable', 'string', 'max:20'],
            'perusahaan_id' => ['required', 'exists:perusahaans,id'],
            'password'      => [$instruktur ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
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
        return view('admin.instruktur.create', [
            'instruktur' => new User(),
            'perusahaan' => Perusahaan::orderBy('nama_perusahaan')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['role'] = 'instruktur_industri';
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.instruktur.index')
            ->with('success', 'Akun instruktur industri berhasil ditambahkan.');
    }

    public function edit(User $instruktur)
    {
        return view('admin.instruktur.edit', [
            'instruktur' => $instruktur,
            'perusahaan' => Perusahaan::orderBy('nama_perusahaan')->get(),
        ]);
    }

    public function update(Request $request, User $instruktur)
    {
        $data = $this->validateData($request, $instruktur);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $instruktur->update($data);

        return redirect()->route('admin.instruktur.index')
            ->with('success', 'Akun instruktur industri berhasil diperbarui.');
    }

    public function destroy(User $instruktur)
    {
        $instruktur->delete();
        return back()->with('success', 'Akun instruktur industri berhasil dihapus.');
    }
}
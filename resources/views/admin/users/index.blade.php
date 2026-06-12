<x-app-layout>
    <x-page-header title="Kelola Pengguna" subtitle="Akun & pemetaan PKL" />
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach(['siswa_pkl' => 'Siswa', 'guru_pembimbing' => 'Guru', 'instruktur_industri' => 'Instruktur', 'admin' => 'Admin'] as $r => $label)
            <a href="{{ route('admin.users.index', ['role' => $r]) }}" class="rounded-lg px-4 py-2 text-sm font-medium {{ $role === $r ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">{{ $label }}</a>
        @endforeach
    </div>

    <details class="mb-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <summary class="cursor-pointer text-sm font-medium text-indigo-600">+ Tambah Pengguna Baru</summary>
        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">@csrf
            <input type="text" name="name" placeholder="Nama" required class="rounded-lg border-slate-300 text-sm">
            <input type="email" name="email" placeholder="Email" required class="rounded-lg border-slate-300 text-sm">
            <input type="text" name="nis" placeholder="NIS (opsional)" class="rounded-lg border-slate-300 text-sm">
            <select name="role" required class="rounded-lg border-slate-300 text-sm"><option value="siswa_pkl">Siswa</option><option value="guru_pembimbing">Guru</option><option value="instruktur_industri">Instruktur</option><option value="admin">Admin</option></select>
            <input type="password" name="password" placeholder="Password" required class="rounded-lg border-slate-300 text-sm">
            <div class="sm:col-span-2 lg:col-span-5"><button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button></div>
        </form>
    </details>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Email</th>@if($role === 'siswa_pkl')<th class="px-4 py-3">Pemetaan PKL</th>@endif<th class="px-4 py-3">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $u->name }}<div class="text-xs text-slate-400">{{ $u->nis }}</div></td>
                        <td class="px-4 py-3 text-slate-500">{{ $u->email }}</td>
                        @if($role === 'siswa_pkl')
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.users.mapping', $u) }}" class="grid grid-cols-2 gap-2">@csrf @method('PUT')
                                    <input type="text" name="kelas" value="{{ $u->kelas }}" placeholder="Kelas" class="rounded border-slate-300 text-xs">
                                    <input type="text" name="jurusan" value="{{ $u->jurusan }}" placeholder="Jurusan" class="rounded border-slate-300 text-xs">
                                    <select name="perusahaan_id" class="rounded border-slate-300 text-xs"><option value="">- Industri -</option>@foreach($perusahaans as $p)<option value="{{ $p->id }}" {{ $u->perusahaan_id == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>@endforeach</select>
                                    <select name="guru_id" class="rounded border-slate-300 text-xs"><option value="">- Guru -</option>@foreach($gurus as $g)<option value="{{ $g->id }}" {{ $u->guru_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>@endforeach</select>
                                    <select name="instruktur_id" class="rounded border-slate-300 text-xs"><option value="">- Instruktur -</option>@foreach($instrukturs as $i)<option value="{{ $i->id }}" {{ $u->instruktur_id == $i->id ? 'selected' : '' }}>{{ $i->name }}</option>@endforeach</select>
                                    <button class="col-span-2 rounded bg-slate-700 py-1 text-xs font-medium text-white">Simpan Pemetaan</button>
                                </form>
                            </td>
                        @endif
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus akun ini?')">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Hapus</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
</x-app-layout>

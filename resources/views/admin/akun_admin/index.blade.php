<x-app-layout title="Kelola Akun Admin">
    <style>[x-cloak]{display:none!important;}</style>

    <div x-data="akunAdminCrud()">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Pengaturan — Kelola Akun Admin</h2>
                <p class="text-sm text-gray-500">Tambah, edit, dan hapus akun administrator sistem.</p>
            </div>
            <a href="{{ route('admin.akun-admin.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                + Tambah Admin
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                 {{ session('success') }} 
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                 {{ session('error') }} 
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                <p class="text-xs font-medium text-gray-500">Total Admin</p>
                <p class="mt-2 text-2xl font-bold text-gray-800"> {{ $rekap['total'] }} </p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                <p class="text-xs font-medium text-gray-500">Akun Anda</p>
                <p class="mt-2 text-2xl font-bold text-green-600"> {{ $rekap['akun_anda'] }} </p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4">
                <p class="text-xs font-medium text-gray-500">Admin Lain</p>
                <p class="mt-2 text-2xl font-bold text-amber-600"> {{ $rekap['admin_lain'] }} </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">

            <form method="GET" class="mb-4 flex gap-2">
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / email..."
                       class="w-full sm:w-72 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
                @if($q)
                    <a href="{{ route('admin.akun-admin.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-blue-100">
                            <th class="py-3 px-4 w-16 text-center">No</th>
                            <th class="py-3 px-6 min-w-[200px]">Nama</th>
                            <th class="py-3 px-6 min-w-[220px]">Email</th>
                            <th class="py-3 px-6 min-w-[150px]">No. HP</th>
                            <th class="py-3 px-6 text-right w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($admins as $admin)
                            <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                                <td class="py-4 px-4 text-center text-gray-500">
                                     {{ $admins->firstItem() + $loop->index }} 
                                </td>
                                <td class="py-4 px-6 font-medium text-gray-800 whitespace-nowrap">
                                     {{ $admin->name }} 
                                    @if($admin->id === auth()->id())
                                        <span class="ml-2 text-[10px] px-2 py-0.5 rounded-full bg-green-50 text-green-600 inline-block align-middle font-normal">Akun Anda</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-gray-600 whitespace-nowrap"> {{ $admin->email }} </td>
                                <td class="py-4 px-6 text-gray-600 whitespace-nowrap"> {{ $admin->no_hp ?? '-' }} </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('admin.akun-admin.edit', $admin->id) }}"
                                           class="text-xs px-3 py-1.5 rounded-lg bg-blue-50 text-[#2563EB] hover:bg-blue-100 font-medium">Edit</a>

                                        @if($admin->id !== auth()->id())
                                            <button type="button"
                                                    @click="konfirmHapus(@js(route('admin.akun-admin.destroy', $admin->id)))"
                                                    class="text-xs px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-medium">Hapus</button>
                                        @else
                                            <span class="text-xs px-3 py-1.5 rounded-lg bg-gray-50 text-gray-400 cursor-not-allowed select-none">Hapus</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada akun admin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {!! $admins->links() !!}
            </div>
        </div>

        <div x-show="hapusOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
             @keydown.escape.window="hapusOpen = false">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false" x-transition>
                <h3 class="text-base font-bold text-black">Hapus Akun Admin</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Yakin ingin menghapus akun admin ini? Tindakan ini tidak dapat dibatalkan.</p>
                <form :action="hapusUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#cf202f] px-4 py-2 text-sm font-bold text-white hover:bg-[#b01926]">Ya, Hapus</button>
                </form>
            </div>
        </div>

    </div>

    <script>
        window.akunAdminCrud = function () {
            return {
                hapusOpen: false,
                hapusUrl: '',
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },
            };
        };
    </script>
</x-app-layout>
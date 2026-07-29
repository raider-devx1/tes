<x-app-layout title="Arsip Siswa PKL">
    <style>[x-cloak]{display:none!important;}</style>

    <div class="py-6 sm:py-8 md:py-10 bg-white" x-data="{ pulihkanPeriodeOpen: false }">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- ===== HEADER + AKSI ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Arsip Siswa PKL</h2>
                    <p class="text-sm text-gray-500">Data siswa yang telah diarsipkan. Seluruh riwayat PKL-nya tetap tersimpan dan dapat dipulihkan.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($jumlahArsip > 0)
                        <button @click="pulihkanPeriodeOpen = true"
                            class="inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700">
                            Pulihkan per Periode
                        </button>
                    @endif
                    <a href="{{ route('admin.siswa.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2563EB] text-white text-sm font-medium hover:bg-blue-700">
                        Kembali ke Data Siswa
                    </a>
                </div>
            </div>

            {{-- ===== PESAN SISTEM ===== --}}
            @if (session('success'))
                <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ===== PENJELASAN SINGKAT ===== --}}
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-900">Tentang halaman ini</p>
                <ul class="mt-2 space-y-1 text-sm text-amber-800 list-disc list-inside">
                    <li>Menghapus siswa tidak memusnahkan datanya. Jurnal, absensi, nilai, observasi, catatan, dan dokumennya tetap utuh.</li>
                    <li>NISN siswa yang diarsipkan <strong>masih terkunci</strong>. NISN tersebut belum bisa dipakai untuk mendaftarkan siswa baru sebelum data lamanya dipulihkan atau NISN-nya diubah.</li>
                    <li>Baris bertanda <span class="font-semibold text-red-700">NISN bentrok</span> tidak dapat dipulihkan karena NISN-nya sedang dipakai siswa aktif.</li>
                </ul>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-4 sm:p-5">
                {{-- ===== SEARCH & FILTER ===== --}}
                <form method="GET" class="mb-4 flex flex-wrap gap-2">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / NISN..."
                        class="w-full sm:w-64 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                    <select name="periode" class="rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                        <option value="">Semua Periode</option>
                        @foreach ($periodeList as $p)
                            <option value="{{ $p->id }}" {{ (string) $periode === (string) $p->id ? 'selected' : '' }}>{{ $p->nama }}@if($p->is_active) (Aktif)@endif</option>
                        @endforeach
                    </select>
                    <button class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
                    @if($q || $periode)
                        <a href="{{ route('admin.siswa.arsip') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
                    @endif
                </form>

                {{-- ============================================================= --}}
                {{-- TABEL DESKTOP / LAPTOP (>= lg)                               --}}
                {{-- ============================================================= --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 border-b border-blue-100">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-6 min-w-[200px]">Siswa</th>
                                <th class="py-3 px-4">NISN</th>
                                <th class="py-3 px-4">Kelas / Jurusan</th>
                                <th class="py-3 px-4">Periode</th>
                                <th class="py-3 px-4">Tempat PKL</th>
                                <th class="py-3 px-4">Diarsipkan</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            @forelse ($siswa as $i => $s)
                                @php $bentrok = $s->nisn && in_array($s->nisn, $nisnBentrok, true); @endphp
                                <tr class="hover:bg-blue-50/40">
                                    <td class="py-3 px-4 text-center text-gray-500">{{ $siswa->firstItem() + $i }}</td>
                                    <td class="py-3 px-6">
                                        <div class="flex items-center gap-3">
                                            @if ($s->foto)
                                                <img src="{{ asset('storage/' . $s->foto) }}" alt=""
                                                    loading="lazy" decoding="async"
                                                    class="w-9 h-9 rounded-full object-cover grayscale">
                                            @else
                                                <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-xs font-semibold text-gray-500">
                                                    {{ strtoupper(substr($s->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-medium text-gray-700">{{ $s->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $s->no_hp ?: '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $s->nisn ?: '-' }}
                                        @if ($bentrok)
                                            <span class="mt-1 block w-fit rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">NISN bentrok</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">{{ $s->kelas ?: '-' }} / {{ $s->jurusan ?: '-' }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ optional($s->periode)->nama ?: '-' }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ optional($s->perusahaan)->nama_perusahaan ?: '-' }}</td>
                                    <td class="py-3 px-4 text-gray-500">{{ optional($s->deleted_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="flex justify-center">
                                            @if ($bentrok)
                                                <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-xs font-medium cursor-not-allowed"
                                                    title="NISN {{ $s->nisn }} sedang dipakai siswa aktif">
                                                    Tidak bisa dipulihkan
                                                </span>
                                            @else
                                                <form method="POST" action="{{ route('admin.siswa.pulihkan', $s->id) }}"
                                                    onsubmit="return confirm('Pulihkan data siswa {{ $s->name }}?')">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit"
                                                        class="px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-semibold hover:bg-green-100">
                                                        Pulihkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-gray-400">
                                        Arsip kosong. Belum ada data siswa yang diarsipkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ============================================================= --}}
                {{-- KARTU MOBILE / TABLET (< lg)                                 --}}
                {{-- ============================================================= --}}
                <div class="lg:hidden space-y-3">
                    @forelse ($siswa as $s)
                        @php $bentrok = $s->nisn && in_array($s->nisn, $nisnBentrok, true); @endphp
                        <div class="rounded-xl border border-blue-100 p-4">
                            <div class="flex items-start gap-3">
                                @if ($s->foto)
                                    <img src="{{ asset('storage/' . $s->foto) }}" alt=""
                                        loading="lazy" decoding="async"
                                        class="w-11 h-11 rounded-full object-cover grayscale shrink-0">
                                @else
                                    <div class="w-11 h-11 shrink-0 rounded-full bg-gray-200 flex items-center justify-center text-sm font-semibold text-gray-500">
                                        {{ strtoupper(substr($s->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-800 break-words">{{ $s->name }}</p>
                                    <p class="text-xs text-gray-500">NISN {{ $s->nisn ?: '-' }}</p>
                                    @if ($bentrok)
                                        <span class="mt-1 inline-block rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">NISN bentrok</span>
                                    @endif
                                </div>
                            </div>

                            <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2 text-xs">
                                <div>
                                    <dt class="text-gray-400">Kelas / Jurusan</dt>
                                    <dd class="text-gray-700 break-words">{{ $s->kelas ?: '-' }} / {{ $s->jurusan ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">Periode</dt>
                                    <dd class="text-gray-700 break-words">{{ optional($s->periode)->nama ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">Tempat PKL</dt>
                                    <dd class="text-gray-700 break-words">{{ optional($s->perusahaan)->nama_perusahaan ?: '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-400">Diarsipkan</dt>
                                    <dd class="text-gray-700">{{ optional($s->deleted_at)->format('d/m/Y H:i') ?: '-' }}</dd>
                                </div>
                            </dl>

                            <div class="mt-3">
                                @if ($bentrok)
                                    <p class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">
                                        Tidak bisa dipulihkan: NISN {{ $s->nisn }} sedang dipakai siswa aktif.
                                    </p>
                                @else
                                    <form method="POST" action="{{ route('admin.siswa.pulihkan', $s->id) }}"
                                        onsubmit="return confirm('Pulihkan data siswa {{ $s->name }}?')">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit"
                                            class="w-full px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700">
                                            Pulihkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="py-10 text-center text-gray-400 text-sm">Arsip kosong. Belum ada data siswa yang diarsipkan.</p>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $siswa->links() }}
                </div>
            </div>

            {{-- ===== MODAL: PULIHKAN PER PERIODE ===== --}}
            <div x-cloak x-show="pulihkanPeriodeOpen"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 p-0 sm:p-4"
                @keydown.escape.window="pulihkanPeriodeOpen = false">
                <div @click.outside="pulihkanPeriodeOpen = false"
                    class="w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl p-5 shadow-xl">
                    <h3 class="text-base font-bold text-gray-800">Pulihkan Siswa per Periode</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Seluruh siswa terarsip pada periode terpilih akan dikembalikan ke daftar aktif.
                        Data yang NISN-nya bentrok akan dilewati secara otomatis.
                    </p>

                    <form method="POST" action="{{ route('admin.siswa.arsip.pulihkan-periode') }}" class="mt-4">
                        @csrf
                        @method('PUT')
                        <label for="periode_id_pulihkan" class="block text-sm font-medium text-gray-700">Periode PKL</label>
                        <select name="periode_id" id="periode_id_pulihkan" required
                            class="mt-1 w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                            <option value="">-- Pilih Periode --</option>
                            @foreach ($periodeList as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }}@if($p->is_active) (Aktif)@endif</option>
                            @endforeach
                        </select>

                        <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                            <button type="button" @click="pulihkanPeriodeOpen = false"
                                class="px-4 py-2.5 rounded-lg text-gray-600 text-sm font-medium hover:bg-gray-50">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700">
                                Pulihkan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

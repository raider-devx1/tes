<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Evaluasi Lembar Observasi</h2>
            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali
            </button>
        </div>
    </x-slot>

    <div x-data="observasiCrud()" class="py-6 sm:py-8 md:py-12 bg-white">
        {{-- WRAPPER RESPONSIVE: full kiri-kanan, min 360px, max 1920px --}}
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- REKAP STATISTICS CARDS --}}
            <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-5 shadow-sm">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Observasi</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-4 sm:p-5 shadow-sm">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Divalidasi</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#05b169]">{{ $rekap['disetujui'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-4 sm:p-5 shadow-sm">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-[#5b616e]">Draft</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#d98200]">{{ $rekap['menunggu'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-5 shadow-sm">
                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wide text-[#5b616e]">Jumlah Guru</p>
                    <p class="mt-1 text-2xl sm:text-3xl font-bold text-black">{{ $jumlahGuru }}</p>
                </div>
            </div>

            {{-- MAIN CONTAINER --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                {{-- ALERTS --}}
                @if (session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                {{-- HEADER SECTION --}}
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Lembar Observasi Seluruh Siswa</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Admin dapat menambah, mengubah, menghapus, memvalidasi, membatalkan validasi, dan mencetak lembar observasi.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="tambah()"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa]">Tambah Observasi</button>
                        <a href="{{ route('cetak.observasi.semua') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Cetak Semua PDF</a>
                    </div>
                </div>

                {{-- FILTER FORM --}}
                <form method="GET" action="{{ route('admin.evaluasi.observasi') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div class="w-full md:w-44">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Kelas</label>
                            <select name="kelas" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Kelas</option>
                                @foreach($kelasList as $opsiKelas)
                                    <option value="{{ $opsiKelas }}" @selected(request('kelas') === $opsiKelas)>{{ $opsiKelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-44">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jurusan</label>
                            <select name="jurusan" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Jurusan</option>
                                @foreach($jurusanList as $opsiJurusan)
                                    <option value="{{ $opsiJurusan }}" @selected(request('jurusan') === $opsiJurusan)>{{ $opsiJurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-48">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                            <select name="status" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="1" @selected(request('status') === '1')>Sudah Divalidasi</option>
                                <option value="0" @selected(request('status') === '0')>Belum (Draft)</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Cari</button>
                            <a href="{{ route('admin.evaluasi.observasi') }}" class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                {{-- ============================================================= --}}
                {{-- TABEL DESKTOP / LAPTOP (>= lg): tampilkan SEMUA informasi   --}}
                {{-- ============================================================= --}}
                <div class="hidden lg:block overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1600px] text-left text-sm table-fixed border-collapse">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3.5 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3.5 font-bold w-28">Tanggal</th>
                                <th class="px-4 py-3.5 font-bold w-44">Siswa</th>
                                <th class="px-4 py-3.5 font-bold w-28">NISN</th>
                                <th class="px-4 py-3.5 font-bold w-44">Guru Pembimbing</th>
                                <th class="px-4 py-3.5 font-bold w-48">Pekerjaan/Projek</th>
                                <th class="px-4 py-3.5 font-bold w-80">Permasalahan</th>
                                <th class="px-4 py-3.5 font-bold w-80">Solusi Pemecahan</th>
                                <th class="px-4 py-3.5 text-center font-bold w-36">Status</th>
                                <th class="px-4 py-3.5 text-center font-bold w-32">Foto</th>
                                <th class="px-4 py-3.5 text-center font-bold w-24">Cetak</th>
                                <th class="px-4 py-3.5 text-center font-bold w-44">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($observasi as $obs)
                                @php $poin = $obs->items; @endphp
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">{{ $observasi->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ optional($obs->hari_tanggal)->format('d M Y') }}</td>
                                    <td class="px-4 py-3 font-bold text-black break-words">{{ $obs->user->name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black">{{ $obs->user->nisn }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">{{ $obs->guru?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-black break-words">{{ $obs->pekerjaan_projek ?? '-' }}</td>

                                    {{-- PERMASALAHAN --}}
                                    <td class="px-4 py-3 text-black whitespace-normal break-words">
                                        @if($poin->count())
                                            <div x-data="{ open: false }">
                                                <div class="flex items-start gap-1.5">
                                                    <span class="font-bold text-[#0047d6] flex-shrink-0">1.</span>
                                                    <span class="font-medium break-words">{{ $poin->first()->permasalahan }}</span>
                                                </div>
                                                @if($poin->count() > 1)
                                                    <button type="button" @click="open = !open"
                                                            class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                        <span x-show="!open">+ {{ $poin->count() - 1 }} lainnya</span>
                                                        <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    </button>
                                                    <ol start="2" x-show="open" x-cloak x-transition class="mt-2 list-decimal list-inside space-y-1 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                        @foreach($poin->slice(1) as $poinLainnya)
                                                            <li class="break-words">{{ $poinLainnya->permasalahan }}</li>
                                                        @endforeach
                                                    </ol>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>

                                    {{-- SOLUSI --}}
                                    <td class="px-4 py-3 text-black whitespace-normal break-words">
                                        @if($poin->count())
                                            <div x-data="{ open: false }">
                                                <div class="flex items-start gap-1.5">
                                                    <span class="font-bold text-[#0047d6] flex-shrink-0">1.</span>
                                                    <span class="font-medium break-words">{{ $poin->first()->solusi }}</span>
                                                </div>
                                                @if($poin->count() > 1)
                                                    <button type="button" @click="open = !open"
                                                            class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">
                                                        <span x-show="!open">+ {{ $poin->count() - 1 }} lainnya</span>
                                                        <span x-show="open" style="display:none;">Sembunyikan</span>
                                                    </button>
                                                    <ol start="2" x-show="open" x-cloak x-transition class="mt-2 list-decimal list-inside space-y-1 border-t border-[#0047d6]/15 pt-2 font-medium">
                                                        @foreach($poin->slice(1) as $poinLainnya)
                                                            <li class="break-words">{{ $poinLainnya->solusi }}</li>
                                                        @endforeach
                                                    </ol>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-4 py-3 text-center whitespace-normal">
                                        <div class="inline-flex flex-col items-center justify-center min-w-[110px]">
                                            @if ($obs->status === 'tervalidasi')
                                                <span class="inline-flex items-center justify-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white w-full shadow-sm">Tervalidasi</span>
                                                @if($obs->validated_at)
                                                    <p class="mt-1 text-[10px] font-medium text-[#5b616e] whitespace-nowrap">{{ \Carbon\Carbon::parse($obs->validated_at)->format('d M Y') }}</p>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center justify-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white w-full shadow-sm">Draft</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- FOTO --}}
                                    <td class="px-4 py-3 text-center">
                                        @if ($obs->foto_dokumentasi || $obs->foto_lembar_observasi)
                                            <div class="flex flex-col items-center justify-center gap-1.5">
                                                @if ($obs->foto_dokumentasi)
                                                    <a href="{{ asset('storage/' . $obs->foto_dokumentasi) }}" download target="_blank" rel="noopener"
                                                       class="inline-flex items-center justify-center rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 w-24">Dokumentasi</a>
                                                @endif
                                                @if ($obs->foto_lembar_observasi)
                                                    <a href="{{ asset('storage/' . $obs->foto_lembar_observasi) }}" download target="_blank" rel="noopener"
                                                       class="inline-flex items-center justify-center rounded-full bg-[#05b169]/10 px-2.5 py-1 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/20 w-24">Lembar</a>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-[#5b616e]">-</span>
                                        @endif
                                    </td>

                                    {{-- CETAK --}}
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('cetak.observasi', $obs->user_id) }}" target="_blank"
                                           class="inline-flex items-center justify-center rounded-full bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">PDF</a>
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-center gap-2">
                                            <button type="button" @click="konfirmValidasi(@js(route('admin.evaluasi.observasi.validasi', $obs->id)))"
                                                    class="inline-flex w-full items-center justify-center rounded-xl bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049457]">
                                                {{ $obs->status === 'tervalidasi' ? 'Validasi Ulang' : 'Validasi' }}
                                            </button>
                                            @if ($obs->status === 'tervalidasi')
                                                <button type="button" @click="konfirmBatal(@js(route('admin.evaluasi.observasi.batal', $obs->id)))"
                                                        class="inline-flex w-full items-center justify-center rounded-xl bg-[#d98200]/10 px-3 py-1.5 text-xs font-bold text-[#d98200] transition hover:bg-[#d98200]/20">
                                                    Batalkan Validasi
                                                </button>
                                            @endif
                                            <div class="flex items-center justify-center gap-2 w-full">
                                                <button type="button"
                                                        @click="edit(@js([
                                                            'id' => $obs->id,
                                                            'user_id' => $obs->user_id,
                                                            'hari_tanggal' => optional($obs->hari_tanggal)->format('Y-m-d'),
                                                            'pekerjaan_projek' => $obs->pekerjaan_projek,
                                                            'items' => $obs->items->map(fn($it) => ['id' => $it->id, 'permasalahan' => $it->permasalahan, 'solusi' => $it->solusi])->values(),
                                                        ]))"
                                                        class="flex-1 rounded-lg border-2 border-[#0047d6]/30 px-2 py-1.5 text-xs font-bold text-[#0047d6] text-center hover:bg-[#0047d6]/5">Edit</button>
                                                <button type="button"
                                                        @click="konfirmHapus(@js(route('admin.evaluasi.observasi.destroy', $obs->id)))"
                                                        class="flex-1 rounded-lg border-2 border-red-200 px-2 py-1.5 text-xs font-bold text-red-600 text-center hover:bg-red-50">Hapus</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada data observasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ============================================================= --}}
                {{-- TABEL MOBILE / TABLET (< lg): hanya Nama + tombol Detail    --}}
                {{-- ============================================================= --}}
                <div class="lg:hidden overflow-hidden rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-3 py-3 text-center w-10 font-bold">No</th>
                                <th class="px-3 py-3 font-bold">Siswa</th>
                                <th class="px-3 py-3 text-center w-28 font-bold">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($observasi as $obs)
                                <tr class="align-middle transition hover:bg-[#0047d6]/5">
                                    <td class="px-3 py-4 text-center font-semibold text-black">{{ $observasi->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-4 text-black">
                                        <div class="font-bold leading-snug break-words">{{ $obs->user->name }}</div>
                                        <div class="text-[11px] text-[#5b616e] mt-0.5 font-mono">NISN: {{ $obs->user->nisn }}</div>
                                        @if ($obs->status === 'tervalidasi')
                                            <span class="mt-1 inline-block rounded-full bg-[#05b169] px-2.5 py-0.5 text-[10px] font-bold text-white">Tervalidasi</span>
                                        @else
                                            <span class="mt-1 inline-block rounded-full bg-[#d98200] px-2.5 py-0.5 text-[10px] font-bold text-white">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <button type="button"
                                                @click="lihatDetail(@js([
                                                    'id' => $obs->id,
                                                    'user_id' => $obs->user_id,
                                                    'nama' => $obs->user->name,
                                                    'nisn' => $obs->user->nisn,
                                                    'guru' => $obs->guru?->name ?? '-',
                                                    'hari_tanggal' => optional($obs->hari_tanggal)->format('Y-m-d'),
                                                    'tanggal_label' => optional($obs->hari_tanggal)->format('d M Y') ?? '-',
                                                    'pekerjaan_projek' => $obs->pekerjaan_projek ?? '-',
                                                    'status' => $obs->status,
                                                    'validated_at' => $obs->validated_at ? \Carbon\Carbon::parse($obs->validated_at)->format('d M Y') : null,
                                                    'items' => $obs->items->map(fn($it) => ['id' => $it->id, 'permasalahan' => $it->permasalahan, 'solusi' => $it->solusi])->values(),
                                                    'foto_dokumentasi_url' => $obs->foto_dokumentasi ? asset('storage/'.$obs->foto_dokumentasi) : null,
                                                    'foto_lembar_url' => $obs->foto_lembar_observasi ? asset('storage/'.$obs->foto_lembar_observasi) : null,
                                                    'cetak_url' => route('cetak.observasi', $obs->user_id),
                                                    'validasi_url' => route('admin.evaluasi.observasi.validasi', $obs->id),
                                                    'batal_url' => route('admin.evaluasi.observasi.batal', $obs->id),
                                                    'destroy_url' => route('admin.evaluasi.observasi.destroy', $obs->id),
                                                ]))"
                                                class="inline-flex items-center justify-center gap-1 rounded-lg bg-[#0047d6] px-3 py-2 text-xs font-bold text-white transition active:scale-95 hover:bg-[#0038aa]">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">Belum ada data observasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="mt-4">{{ $observasi->links() }}</div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL DETAIL (mobile) - animasi smooth slide-up / fade          --}}
        {{-- ================================================================= --}}
        <div x-show="detailOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
             @keydown.escape.window="detailOpen = false">
            <div x-show="detailOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl text-left"
                 @click.outside="detailOpen = false">
                <div class="sticky top-0 z-10 flex items-start justify-between gap-3 border-b-2 border-[#0047d6]/10 bg-white px-5 py-4">
                    <div>
                        <h3 class="text-base font-bold text-black" x-text="detailData.nama"></h3>
                        <p class="text-xs font-mono text-[#5b616e]">NISN: <span x-text="detailData.nisn"></span></p>
                    </div>
                    <button type="button" @click="detailOpen = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-bold"
                              :class="detailData.status === 'tervalidasi' ? 'bg-[#05b169] text-white' : 'bg-[#d98200] text-white'"
                              x-text="detailData.status === 'tervalidasi' ? 'Tervalidasi' : 'Draft'"></span>
                        <template x-if="detailData.status === 'tervalidasi' && detailData.validated_at">
                            <span class="ml-2 text-[11px] font-medium text-[#5b616e]" x-text="'Divalidasi: ' + detailData.validated_at"></span>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p>
                            <p class="mt-0.5 text-sm font-medium text-black" x-text="detailData.tanggal_label"></p>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Guru Pembimbing</p>
                            <p class="mt-0.5 text-sm font-medium text-black" x-text="detailData.guru"></p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e]">Pekerjaan / Projek</p>
                        <p class="mt-0.5 text-sm font-semibold text-black" x-text="detailData.pekerjaan_projek"></p>
                    </div>

                    {{-- POIN PERMASALAHAN & SOLUSI --}}
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Permasalahan &amp; Solusi</p>
                        <template x-if="!detailData.items || detailData.items.length === 0">
                            <p class="text-sm italic text-[#5b616e]">-</p>
                        </template>
                        <div class="space-y-2">
                            <template x-for="(it, i) in detailData.items" :key="i">
                                <div class="rounded-xl border-2 border-[#0047d6]/15 p-3">
                                    <p class="text-xs font-bold text-[#0047d6]" x-text="'Poin ' + (i + 1)"></p>
                                    <div class="mt-1">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-[#5b616e]">Permasalahan</p>
                                        <p class="text-sm text-black whitespace-pre-line" x-text="it.permasalahan || '-'"></p>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-[#5b616e]">Solusi</p>
                                        <p class="text-sm text-black whitespace-pre-line" x-text="it.solusi || '-'"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- FOTO --}}
                    <template x-if="detailData.foto_dokumentasi_url || detailData.foto_lembar_url">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-[#5b616e] mb-1">Foto</p>
                            <div class="grid grid-cols-2 gap-2">
                                <template x-if="detailData.foto_dokumentasi_url">
                                    <a :href="detailData.foto_dokumentasi_url" download target="_blank" class="block">
                                       
                                        <span  class="inline-flex items-center gap-1 rounded-xl bg-[#d98200] px-4 py-2 text-sm font-bold text-white transition hover:opacity-90">Lihat Dokumentasi</span>
                                    </a>
                                </template>
                                <template x-if="detailData.foto_lembar_url">
                                    <a :href="detailData.foto_lembar_url" target="_blank" class="block">
                                       
                                        <span  class="inline-flex items-center gap-1 rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:opacity-90">Lihat Lembar Observasi</span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- AKSI DALAM MODAL DETAIL --}}
                <div class="sticky bottom-0 z-10 space-y-2 border-t-2 border-[#0047d6]/10 bg-white px-5 py-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="detailOpen = false; konfirmValidasi(detailData.validasi_url)"
                                class="flex-1 min-w-[110px] rounded-xl bg-[#05b169] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#049457]"
                                x-text="detailData.status === 'tervalidasi' ? 'Validasi Ulang' : 'Validasi'"></button>
                        <template x-if="detailData.status === 'tervalidasi'">
                            <button type="button" @click="detailOpen = false; konfirmBatal(detailData.batal_url)"
                                    class="flex-1 min-w-[110px] rounded-xl bg-[#d98200]/10 px-3 py-2.5 text-xs font-bold text-[#d98200] transition hover:bg-[#d98200]/20">Batalkan Validasi</button>
                        </template>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a :href="detailData.cetak_url" target="_blank"
                           class="flex-1 min-w-[90px] rounded-xl border-2 border-[#0047d6] px-3 py-2.5 text-center text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6] hover:text-white">Cetak PDF</a>
                        <button type="button" @click="editDariDetail()"
                                class="flex-1 min-w-[90px] rounded-xl bg-[#0047d6] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">Edit</button>
                        <button type="button" @click="detailOpen = false; konfirmHapus(detailData.destroy_url)"
                                class="flex-1 min-w-[90px] rounded-xl bg-[#cf202f] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#b01926]">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL GLOBAL: VALIDASI (upload foto)                            --}}
        {{-- ================================================================= --}}
        <div x-show="validasiOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-0 sm:p-4"
             @keydown.escape.window="validasiOpen = false">
            <div x-show="validasiOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white p-6 text-left shadow-xl" @click.outside="validasiOpen = false">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-black">Validasi Lembar Observasi</h3>
                    <button type="button" @click="validasiOpen = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                </div>
                <p class="mb-4 text-sm text-[#5b616e]">
                    Unggah foto dokumentasi kegiatan dan foto lembar observasi yang sudah diparaf
                    <span class="font-semibold text-black">instruktur &amp; guru pembimbing</span>.
                    Setelah divalidasi, hasil cetak PDF menampilkan keterangan <span class="font-bold text-black">SUDAH DIVALIDASI</span>.
                </p>
                <form :action="validasiUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-bold text-black mb-1">Foto Dokumentasi Kegiatan <span class="text-red-500">*</span></label>
                        <input type="file" name="foto_dokumentasi" accept="image/*" capture="environment" required
                               class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#0047d6] file:px-4 file:py-2 file:text-white file:font-bold">
                        <p class="mt-1 text-xs text-gray-500">Wajib. Format JPG/JPEG/PNG, maksimal 2 MB.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-black mb-1">Foto Lembar Observasi (Sudah Diparaf) <span class="text-red-500">*</span></label>
                        <input type="file" name="foto_lembar_observasi" accept="image/*" capture="environment" required
                               class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-[#05b169] file:px-4 file:py-2 file:text-white file:font-bold">
                        <p class="mt-1 text-xs text-gray-500">Wajib. Foto lembar fisik yang sudah diparaf instruktur &amp; guru pembimbing.</p>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="validasiOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Batal</button>
                        <button type="submit" class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049457]">Simpan &amp; Validasi</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL GLOBAL: BATAL VALIDASI                                    --}}
        {{-- ================================================================= --}}
        <div x-show="batalOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
             @keydown.escape.window="batalOpen = false">
            <div x-show="batalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="batalOpen = false">
                <h3 class="text-base font-bold text-black">Batalkan Validasi</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Status lembar observasi ini akan kembali ke <span class="font-bold text-black">draft</span>. Foto yang sudah diunggah tetap disimpan.</p>
                <form :action="batalUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('PUT')
                    <button type="button" @click="batalOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#d98200] px-4 py-2 text-sm font-bold text-white hover:bg-[#b06a00]">Ya, Batalkan</button>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL GLOBAL: TAMBAH / EDIT OBSERVASI                           --}}
        {{-- ================================================================= --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4" @keydown.escape.window="open = false">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                 class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-2xl sm:rounded-2xl sm:p-6 max-h-[90vh] overflow-y-auto" @click.outside="open = false">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-bold text-black" x-text="mode === 'create' ? 'Tambah Observasi' : 'Edit Observasi'"></h3>
                    <button type="button" @click="open = false" class="rounded-lg px-2 py-1 text-lg font-bold text-[#5b616e] hover:bg-black/5">&times;</button>
                </div>
                <form :action="actionUrl" method="POST" @submit="simpan($event)" class="space-y-3">
                    @csrf
                    <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                    <input type="hidden" name="user_id" :value="siswaCocok ? siswaCocok.id : ''">

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">NISN Siswa</label>
                        <input type="text" x-model="form.nisn" placeholder="Masukkan NISN siswa"
                               class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        <template x-if="siswaCocok"><p class="mt-1 text-xs font-semibold text-[#05b169]">&#10003; <span x-text="siswaCocok.name"></span></p></template>
                        <template x-if="form.nisn.trim() !== '' && !siswaCocok"><p class="mt-1 text-xs font-semibold text-[#cf202f]">NISN tidak cocok</p></template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Tanggal</label>
                            <input type="date" name="hari_tanggal" x-model="form.hari_tanggal" required
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">Pekerjaan / Projek</label>
                            <input type="text" name="pekerjaan_projek" x-model="form.pekerjaan_projek"
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black">Poin Permasalahan &amp; Solusi</label>
                            <button type="button" @click="tambahItem()" class="rounded-lg bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] hover:bg-[#0047d6]/20">Tambah poin</button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(it, i) in form.items" :key="i">
                                <div class="rounded-xl border-2 border-[#0047d6]/15 p-3">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-xs font-bold text-[#0047d6]" x-text="'Poin ' + (i + 1)"></span>
                                        <button type="button" @click="hapusItem(i)" x-show="form.items.length > 1" class="rounded-lg border-2 border-red-200 px-2 py-1 text-xs font-bold text-red-600 hover:bg-red-50">Hapus poin</button>
                                    </div>
                                    <input type="hidden" :name="'items[' + i + '][id]'" :value="it.id ?? ''">
                                    <textarea :name="'items[' + i + '][permasalahan]'" x-model="it.permasalahan" rows="2" placeholder="Permasalahan..."
                                              class="mb-2 w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                                    <textarea :name="'items[' + i + '][solusi]'" x-model="it.solusi" rows="2" placeholder="Solusi pemecahan..."
                                              class="w-full rounded-lg border-2 border-[#0047d6]/25 bg-white px-3 py-2 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"></textarea>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-xl bg-[#0047d6]/5 px-3 py-2.5 text-xs font-medium text-[#5b616e]">
                        Foto dokumentasi &amp; foto lembar observasi diunggah saat proses <span class="font-bold text-black">Validasi</span> (tombol hijau di kolom Aksi / dalam Detail).
                    </div>
                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''" class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Simpan</button>
                        <button type="button" @click="open = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL GLOBAL: CONFIRM HAPUS                                     --}}
        {{-- ================================================================= --}}
        <div x-show="hapusOpen" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="hapusOpen = false">
            <div x-show="hapusOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false">
                <h3 class="text-base font-bold text-black">Hapus Observasi</h3>
                <p class="mt-1 text-sm text-[#5b616e]">Yakin ingin menghapus lembar observasi ini beserta seluruh poinnya? Tindakan ini tidak dapat dibatalkan.</p>
                <form :action="hapusUrl" method="POST" class="mt-4 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="hapusOpen = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#cf202f] px-4 py-2 text-sm font-bold text-white hover:bg-[#b01926]">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ALPINE JS HANDLER --}}
    <script>
        window.observasiCrud = function () {
            const daftarSiswa = @js($siswaList);
            const today = @js(date('Y-m-d'));
            const storeUrl = @js(route('admin.evaluasi.observasi.store'));
            const kosong = () => ({ id: null, nisn: '', hari_tanggal: today, pekerjaan_projek: '', items: [{ id: null, permasalahan: '', solusi: '' }] });

            return {
                open: false,
                mode: 'create',
                form: kosong(),
                hapusOpen: false,
                hapusUrl: '',
                detailOpen: false,
                detailData: {},
                validasiOpen: false,
                validasiUrl: '',
                batalOpen: false,
                batalUrl: '',

                init() {
                    this.$watch('open',        () => this.kunciScroll());
                    this.$watch('hapusOpen',   () => this.kunciScroll());
                    this.$watch('detailOpen',  () => this.kunciScroll());
                    this.$watch('validasiOpen',() => this.kunciScroll());
                    this.$watch('batalOpen',   () => this.kunciScroll());
                },
                kunciScroll() {
                    document.body.style.overflow = (this.open || this.hapusOpen || this.detailOpen || this.validasiOpen || this.batalOpen) ? 'hidden' : '';
                },

                get siswaCocok() {
                    const nisn = String(this.form.nisn || '').trim();
                    if (!nisn) return null;
                    return daftarSiswa.find(s => String(s.nisn).trim() === nisn) || null;
                },
                get actionUrl() { return this.mode === 'create' ? storeUrl : storeUrl + '/' + this.form.id; },

                tambah() { this.mode = 'create'; this.form = kosong(); this.open = true; },

                edit(d) {
                    const s = daftarSiswa.find(x => String(x.id) === String(d.user_id));
                    let items = Array.isArray(d.items) ? d.items.map(it => ({ id: it.id, permasalahan: it.permasalahan || '', solusi: it.solusi || '' })) : [];
                    if (items.length === 0) items = [{ id: null, permasalahan: '', solusi: '' }];
                    this.mode = 'edit';
                    this.form = { id: d.id, nisn: s ? String(s.nisn) : '', hari_tanggal: d.hari_tanggal, pekerjaan_projek: d.pekerjaan_projek || '', items: items };
                    this.open = true;
                },

                // buka detail (mobile)
                lihatDetail(d) { this.detailData = d; this.detailOpen = true; },

                // dari modal detail -> buka form edit
                editDariDetail() {
                    const d = this.detailData;
                    this.detailOpen = false;
                    this.edit({
                        id: d.id,
                        user_id: d.user_id,
                        hari_tanggal: d.hari_tanggal,
                        pekerjaan_projek: d.pekerjaan_projek === '-' ? '' : d.pekerjaan_projek,
                        items: d.items,
                    });
                },

                tambahItem() { this.form.items.push({ id: null, permasalahan: '', solusi: '' }); },
                hapusItem(i) { this.form.items.splice(i, 1); },

                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },
                konfirmValidasi(url) { this.validasiUrl = url; this.validasiOpen = true; },
                konfirmBatal(url) { this.batalUrl = url; this.batalOpen = true; },
            };
        };
    </script>
</x-app-layout>

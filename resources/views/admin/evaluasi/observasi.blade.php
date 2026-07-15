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

    <div x-data="observasiCrud()" class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- REKAP --}}
            <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Observasi</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Divalidasi</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['disetujui'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Menunggu Validasi</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['menunggu'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Jumlah Guru</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $jumlahGuru }}</p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">
                @if (session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border-2 border-[#cf202f] bg-[#cf202f]/10 px-4 py-3 text-sm font-semibold text-[#cf202f]">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Lembar Observasi Seluruh Siswa</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Admin dapat menambah, mengubah, menghapus, memvalidasi, membatalkan validasi, dan mencetak lembar observasi.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="tambah()"
                                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#0038aa]">
                            Tambah Observasi
                        </button>
                        <a href="{{ route('cetak.observasi.semua') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                            Cetak Semua PDF
                        </a>
                    </div>
                </div>

                {{-- FILTER --}}
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
                                @foreach($kelasList as $opsiKelas)<option value="{{ $opsiKelas }}" @selected(request('kelas') === $opsiKelas)>{{ $opsiKelas }}</option>@endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-44">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Jurusan</label>
                            <select name="jurusan" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Jurusan</option>
                                @foreach($jurusanList as $opsiJurusan)<option value="{{ $opsiJurusan }}" @selected(request('jurusan') === $opsiJurusan)>{{ $opsiJurusan }}</option>@endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-48">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                            <select name="status" class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="1" @selected(request('status') === '1')>Sudah Divalidasi</option>
                                <option value="0" @selected(request('status') === '0')>Belum (Menunggu)</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Cari</button>
                            <a href="{{ route('admin.evaluasi.observasi') }}" class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                {{-- TABEL --}}
                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
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
                                                <span class="inline-flex items-center justify-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white w-full shadow-sm">Menunggu</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- FOTO --}}
                                    <td class="px-4 py-3 text-center">
                                        @if ($obs->foto_dokumentasi || $obs->foto_lembar_observasi)
                                            <div class="flex flex-col items-center justify-center gap-1.5">
                                                @if ($obs->foto_dokumentasi)
                                                    <a href="{{ asset('storage/' . $obs->foto_dokumentasi) }}" target="_blank" rel="noopener"
                                                       class="inline-flex items-center justify-center rounded-full bg-[#0047d6]/10 px-2.5 py-1 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20 w-24">Dokumentasi</a>
                                                @endif
                                                @if ($obs->foto_lembar_observasi)
                                                    <a href="{{ asset('storage/' . $obs->foto_lembar_observasi) }}" target="_blank" rel="noopener"
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
                                        <div x-data="{ showValidasi:false, showBatal:false }" class="flex flex-col items-center gap-2">
                                            <button type="button" @click="showValidasi = true"
                                                    class="inline-flex w-full items-center justify-center rounded-xl bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049457]">
                                                {{ $obs->status === 'tervalidasi' ? 'Validasi Ulang' : 'Validasi' }}
                                            </button>

                                            @if ($obs->status === 'tervalidasi')
                                                <button type="button" @click="showBatal = true"
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

                                            {{-- MODAL VALIDASI --}}
                                            <div x-show="showValidasi" x-cloak
                                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                                                 @keydown.escape.window="showValidasi = false">
                                                <div class="w-full max-w-lg rounded-2xl bg-white p-6 text-left shadow-xl" @click.outside="showValidasi = false" x-transition>
                                                    <div class="mb-4 flex items-center justify-between">
                                                        <h3 class="text-lg font-bold text-black">Validasi Lembar Observasi</h3>
                                                        <button type="button" @click="showValidasi = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                                    </div>
                                                    <p class="mb-4 text-sm text-[#5b616e]">
                                                        Unggah foto dokumentasi kegiatan dan foto lembar observasi yang sudah diparaf
                                                        <span class="font-semibold text-black">instruktur &amp; guru pembimbing</span>.
                                                        Setelah divalidasi, hasil cetak PDF menampilkan keterangan <span class="font-bold text-black">SUDAH DIVALIDASI</span>.
                                                    </p>
                                                    <form method="POST" action="{{ route('admin.evaluasi.observasi.validasi', $obs->id) }}"
                                                          enctype="multipart/form-data" class="space-y-4">
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
                                                            <button type="button" @click="showValidasi = false" class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Batal</button>
                                                            <button type="submit" class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#049457]">Simpan &amp; Validasi</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            {{-- MODAL BATAL VALIDASI --}}
                                            <div x-show="showBatal" x-cloak
                                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
                                                 @keydown.escape.window="showBatal = false">
                                                <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="showBatal = false" x-transition>
                                                    <h3 class="text-base font-bold text-black">Batalkan Validasi</h3>
                                                    <p class="mt-1 text-sm text-[#5b616e]">Status lembar observasi ini akan kembali ke <span class="font-bold text-black">menunggu</span>. Foto yang sudah diunggah tetap disimpan.</p>
                                                    <form method="POST" action="{{ route('admin.evaluasi.observasi.batal', $obs->id) }}" class="mt-4 flex justify-end gap-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="button" @click="showBatal = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                                                        <button type="submit" class="rounded-xl bg-[#d98200] px-4 py-2 text-sm font-bold text-white hover:bg-[#b06a00]">Ya, Batalkan</button>
                                                    </form>
                                                </div>
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

                <div class="mt-4">{{ $observasi->links() }}</div>
            </div>
        </div>

        {{-- MODAL TAMBAH / EDIT OBSERVASI --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4" @keydown.escape.window="open = false">
            <div class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-2xl sm:rounded-2xl sm:p-6 max-h-[90vh] overflow-y-auto" @click.outside="open = false" x-transition>
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
                        <template x-if="siswaCocok"><p class="mt-1 text-xs font-semibold text-[#05b169]">✓ <span x-text="siswaCocok.name"></span></p></template>
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
                        Foto dokumentasi &amp; foto lembar observasi diunggah saat proses <span class="font-bold text-black">Validasi</span> (tombol hijau di kolom Aksi).
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" :disabled="!siswaCocok" :class="!siswaCocok ? 'opacity-50 cursor-not-allowed' : ''" class="flex-1 rounded-xl bg-[#0047d6] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#0038aa]">Simpan</button>
                        <button type="button" @click="open = false" class="rounded-xl border-2 border-[#0047d6]/25 px-4 py-2.5 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL HAPUS --}}
        <div x-show="hapusOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @keydown.escape.window="hapusOpen = false">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" @click.outside="hapusOpen = false" x-transition>
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
                tambahItem() { this.form.items.push({ id: null, permasalahan: '', solusi: '' }); },
                hapusItem(i) { this.form.items.splice(i, 1); },
                simpan(e) { if (!this.siswaCocok) e.preventDefault(); },
                konfirmHapus(url) { this.hapusUrl = url; this.hapusOpen = true; },
            };
        };
    </script>
</x-app-layout>
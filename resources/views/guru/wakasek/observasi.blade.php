<x-app-layout>
    <style>[x-cloak]{display:none!important;}</style>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">
                    Wakasek &mdash; Validasi Observasi
                </h2>
                <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-purple-700">WAKASEK</span>
            </div>
            <a href="{{ route('guru.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 md:py-12 bg-white min-h-screen">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12">

            {{-- ===== KARTU REKAP ===== --}}
            <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Lembar Observasi</p>
                    <p class="mt-1 text-3xl font-bold text-black">{{ $rekap['total'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Menunggu Divalidasi</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]">{{ $rekap['menunggu'] }}</p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Tervalidasi</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]">{{ $rekap['tervalidasi'] }}</p>
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
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-5">
                    <h3 class="text-lg font-bold tracking-tight text-black">Daftar Pengajuan Validasi</h3>
                    <p class="text-xs font-medium text-[#5b616e]">
                        Sebagai Wakasek, Anda memvalidasi lembar observasi yang diajukan para guru pembimbing.
                        Guru yang bukan Wakasek hanya bisa <span class="font-bold text-black">mengajukan</span> dan menunggu validasi Anda di sini.
                    </p>
                </div>

                {{-- ===== FILTER ===== --}}
                <form method="GET" action="{{ route('guru.wakasek.observasi') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Siswa / NISN / Guru / NIP)</label>
                            <input type="text" name="q" value="{{ $q }}" placeholder="Ketik nama siswa, NISN, nama guru, atau NIP..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                            <select name="status"
                                    class="w-full md:w-52 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="diajukan"    {{ $status === 'diajukan' ? 'selected' : '' }}>Menunggu Divalidasi</option>
                                <option value="tervalidasi" {{ $status === 'tervalidasi' ? 'selected' : '' }}>Sudah Tervalidasi</option>
                                <option value="semua"       {{ !in_array($status, ['diajukan','tervalidasi']) ? 'selected' : '' }}>Semua</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa]">Filter</button>
                            <a href="{{ route('guru.wakasek.observasi') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                {{-- ===== DAFTAR (KARTU) ===== --}}
                <div class="space-y-3">
                    @forelse ($observasi as $obs)
                        @php
                            $poin          = $obs->items;
                            $isTervalidasi = ($obs->status ?? 'draft') === 'tervalidasi';
                            $isDiajukan    = ($obs->status ?? 'draft') === 'diajukan';
                        @endphp
                        <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-5 shadow-sm"
                             x-data="{ detail: false }"
                             x-effect="document.body.style.overflow = detail ? 'hidden' : ''">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-bold text-black">{{ $obs->user->name ?? '-' }}</p>
                                        <span class="text-xs font-medium text-[#5b616e]">NISN: {{ $obs->user->nisn ?? '-' }}</span>
                                        @if($isTervalidasi)
                                            <span class="inline-flex items-center rounded-full bg-[#05b169]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#05b169]">Tervalidasi</span>
                                        @elseif($isDiajukan)
                                            <span class="inline-flex items-center rounded-full bg-[#d98200]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#d98200]">Menunggu Divalidasi</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#5b616e]/10 px-2.5 py-0.5 text-[11px] font-bold text-[#5b616e]">Draft</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-xs font-medium text-[#5b616e]">
                                        Guru Pembimbing: <span class="font-bold text-black">{{ $obs->guru->name ?? '-' }}</span>
                                        (NIP: {{ $obs->guru->nip ?? '-' }})
                                        &middot; {{ \Carbon\Carbon::parse($obs->hari_tanggal)->format('d M Y') }}
                                    </p>
                                </div>
                                <div class="flex flex-shrink-0 items-center gap-2">
                                    <button type="button" @click="detail = true"
                                            class="inline-flex items-center justify-center rounded-xl bg-[#0047d6] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                        Lihat Detail
                                    </button>
                                    @if($isDiajukan)
                                        <form method="POST" action="{{ route('guru.wakasek.observasi.validasi', $obs->id) }}"
                                              onsubmit="return confirm('Validasi lembar observasi ini? Setelah divalidasi, hasil cetak akan menampilkan SUDAH DIVALIDASI.')">
                                            @csrf @method('PUT')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-xl bg-[#05b169] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#049457]">
                                                Validasi
                                            </button>
                                        </form>
                                    @elseif($isTervalidasi)
                                        <form method="POST" action="{{ route('guru.wakasek.observasi.batal', $obs->id) }}"
                                              onsubmit="return confirm('Batalkan validasi lembar observasi ini? Status akan kembali menunggu divalidasi.')">
                                            @csrf @method('PUT')
                                            <button type="submit"
                                                    class="inline-flex items-center justify-center rounded-xl bg-[#cf202f]/10 px-4 py-2 text-xs font-bold text-[#cf202f] transition hover:bg-[#cf202f]/20">
                                                Batalkan Validasi
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- ===== MODAL DETAIL ===== --}}
                            <div x-show="detail" x-cloak
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 p-0 sm:p-4"
                                 @keydown.escape.window="detail = false">
                                <div x-show="detail"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                                     class="w-full sm:max-w-lg max-h-[90vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl text-left"
                                     @click.outside="detail = false">
                                    <div class="sticky top-0 flex items-center justify-between border-b-2 border-[#0047d6]/15 bg-white px-5 py-3">
                                        <h3 class="text-base font-bold text-black">Detail Lembar Observasi</h3>
                                        <button type="button" @click="detail = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                                    </div>
                                    <div class="space-y-4 px-5 py-4">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Siswa</p>
                                                <p class="text-sm font-bold text-black break-words">{{ $obs->user->name ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">NISN</p>
                                                <p class="text-sm font-medium text-black">{{ $obs->user->nisn ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Guru Pembimbing</p>
                                                <p class="text-sm font-medium text-black break-words">{{ $obs->guru->name ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Tanggal</p>
                                                <p class="text-sm font-medium text-black">{{ \Carbon\Carbon::parse($obs->hari_tanggal)->format('d M Y') }}</p>
                                            </div>
                                            <div class="col-span-2">
                                                <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Pekerjaan/Projek</p>
                                                <p class="text-sm font-medium text-black break-words">{{ $obs->pekerjaan_projek ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Permasalahan</p>
                                            @if($poin && $poin->count())
                                                <ol class="list-decimal list-inside space-y-0.5 text-sm font-medium text-black">
                                                    @foreach($poin as $it)<li class="break-words">{{ $it->permasalahan }}</li>@endforeach
                                                </ol>
                                            @else
                                                <span class="text-sm text-[#5b616e]">-</span>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-1">Solusi Pemecahan</p>
                                            @if($poin && $poin->count())
                                                <ol class="list-decimal list-inside space-y-0.5 text-sm font-medium text-black">
                                                    @foreach($poin as $it)<li class="break-words">{{ $it->solusi }}</li>@endforeach
                                                </ol>
                                            @else
                                                <span class="text-sm text-[#5b616e]">-</span>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e] mb-2">Bukti Foto</p>
                                            @if ($obs->foto_dokumentasi || $obs->foto_lembar_observasi)
                                                <div class="flex flex-wrap gap-2">
                                                    @if ($obs->foto_dokumentasi)
                                                        <a href="{{ asset('storage/' . $obs->foto_dokumentasi) }}" target="_blank" rel="noopener"
                                                           class="inline-flex items-center gap-1 rounded-full bg-[#0047d6]/10 px-3 py-1.5 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/20">Foto Dokumentasi</a>
                                                    @endif
                                                    @if ($obs->foto_lembar_observasi)
                                                        <a href="{{ asset('storage/' . $obs->foto_lembar_observasi) }}" target="_blank" rel="noopener"
                                                           class="inline-flex items-center gap-1 rounded-full bg-[#05b169]/10 px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/20">Lembar Berparaf</a>
                                                    @endif
                                                </div>
                                            @else
                                                <p class="text-sm text-[#5b616e]">Belum ada foto yang diunggah guru.</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="sticky bottom-0 space-y-2 border-t-2 border-[#0047d6]/15 bg-white px-5 py-4">
                                        <a href="{{ route('cetak.observasi', ['siswa_id' => $obs->user_id, 'observasi_id' => $obs->id]) }}" target="_blank"
                                           class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-3 py-2.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                            Cetak PDF
                                        </a>
                                        @if($isDiajukan)
                                            <form method="POST" action="{{ route('guru.wakasek.observasi.validasi', $obs->id) }}"
                                                  onsubmit="return confirm('Validasi lembar observasi ini?')">
                                                @csrf @method('PUT')
                                                <button type="submit"
                                                        class="flex w-full items-center justify-center rounded-xl bg-[#05b169] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#049457]">
                                                    Validasi Lembar Observasi
                                                </button>
                                            </form>
                                        @elseif($isTervalidasi)
                                            <form method="POST" action="{{ route('guru.wakasek.observasi.batal', $obs->id) }}"
                                                  onsubmit="return confirm('Batalkan validasi lembar observasi ini?')">
                                                @csrf @method('PUT')
                                                <button type="submit"
                                                        class="flex w-full items-center justify-center rounded-xl bg-[#cf202f]/10 px-4 py-3 text-sm font-bold text-[#cf202f] transition hover:bg-[#cf202f]/20">
                                                    Batalkan Validasi
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white px-4 py-8 text-center font-medium text-[#5b616e] italic">
                            Tidak ada lembar observasi pada filter ini.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {!! $observasi->withQueryString()->links() !!}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

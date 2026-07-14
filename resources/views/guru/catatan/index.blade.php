<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">
                Catatan Kegiatan Siswa Bimbingan
            </h2>
            <a href="{{ route('guru.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <style>[x-cloak]{display:none!important}</style>

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-[#0047d6]/15 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Total Catatan</p>
                    <p class="mt-1 text-3xl font-bold text-black"> {{ $rekap['total'] }} </p>
                </div>
                <div class="rounded-2xl border-2 border-[#05b169]/30 bg-[#05b169]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Sudah Disetujui</p>
                    <p class="mt-1 text-3xl font-bold text-[#05b169]"> {{ $rekap['disetujui'] }} </p>
                </div>
                <div class="rounded-2xl border-2 border-[#0047d6]/30 bg-[#0047d6]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Perlu Divalidasi</p>
                    <p class="mt-1 text-3xl font-bold text-[#0047d6]"> {{ $rekap['diajukan'] }} </p>
                </div>
                <div class="rounded-2xl border-2 border-[#d98200]/30 bg-[#d98200]/5 p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-[#5b616e]">Masih Draft</p>
                    <p class="mt-1 text-3xl font-bold text-[#d98200]"> {{ $rekap['draft'] }} </p>
                </div>
            </div>

            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 md:p-8 shadow-sm">

                @if (session('success'))
                    <div class="mb-4 rounded-xl border-2 border-[#05b169] bg-[#05b169]/10 px-4 py-3 text-sm font-semibold text-black">
                         {{ session('success') }} 
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-xl border-2 border-red-500 bg-red-500/10 px-4 py-3 text-sm font-semibold text-black">
                         {{ session('error') }} 
                    </div>
                @endif

                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-black">Catatan Kegiatan Siswa</h3>
                        <p class="text-xs font-medium text-[#5b616e]">Periksa <span class="font-bold text-black">bukti fisik</span> (lihat/download), lalu tekan <span class="font-bold text-black">Validasi</span> untuk menyetujui atau menolak.</p>
                    </div>

                    <a href="{{ route('cetak.catatan.semua') }}" target="_blank"
                       class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                        Cetak Semua PDF
                    </a>
                </div>

                <form method="GET" action="{{ route('guru.catatan.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2.5 text-sm font-medium text-black placeholder-[#a8acb3] focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                        </div>

                        <div class="w-full md:w-56">
                            <label class="block text-xs font-bold uppercase tracking-wide text-black mb-1">Status</label>
                            <select name="status"
                                    class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30">
                                <option value="">Semua Status</option>
                                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                                <option value="diajukan" @selected(request('status') === 'diajukan')>Diajukan</option>
                                <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center rounded-xl bg-[#0047d6] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">Cari</button>
                            <a href="{{ route('guru.catatan.index') }}"
                               class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-5 py-2.5 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="overflow-x-auto rounded-xl border-2 border-[#0047d6]/15">
                    <table class="w-full min-w-[1200px] text-left text-sm table-fixed">
                        <thead>
                            <tr class="bg-[#0047d6] text-xs uppercase tracking-wide text-white">
                                <th class="px-4 py-3 text-center w-12 font-bold">No</th>
                                <th class="px-4 py-3 font-bold w-40">Nama Siswa</th>
                                <th class="px-4 py-3 font-bold w-28">NISN</th>
                                <th class="px-4 py-3 font-bold w-40">Pekerjaan</th>
                                <th class="px-4 py-3 font-bold w-[22%]">Perencanaan</th>
                                <th class="px-4 py-3 font-bold w-[22%]">Hasil/Pelaksanaan</th>
                                <th class="px-4 py-3 font-bold w-44">Catatan Instruktur</th>
                                <th class="px-4 py-3 text-center font-bold w-28">Status</th>
                                <th class="px-4 py-3 text-center font-bold w-44">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#0047d6]/10">
                            @forelse ($catatan as $item)
                                <tr class="align-top transition hover:bg-[#0047d6]/5">
                                    <td class="px-4 py-3 text-center font-semibold text-black">
                                         {{ $catatan->firstItem() + $loop->index }} 
                                    </td>
                                    <td class="px-4 py-3 font-bold text-black break-words"> {{ $item->user->name ?? '-' }} </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-black"> {{ $item->user->nisn ?? '-' }} </td>
                                    <td class="px-4 py-3 font-medium text-black break-words"> {{ $item->nama_pekerjaan }} </td>
                                    <td class="px-4 py-3 font-medium text-black break-words"> {{ $item->perencanaan_kegiatan }} </td>
                                    <td class="px-4 py-3 font-medium text-black break-words"> {{ $item->pelaksanaan_kegiatan }} </td>
                                    <td class="px-4 py-3 text-black break-words">
                                        @if($item->catatan_instruktur)
                                            <div class="rounded-lg border-l-4 border-[#d98200] bg-[#d98200]/5 p-2 text-xs font-medium italic text-black">
                                                 {{ $item->catatan_instruktur }} 
                                            </div>
                                        @else
                                            <span class="text-[#5b616e]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->status === 'disetujui')
                                            <span class="inline-flex items-center rounded-full bg-[#05b169] px-3 py-1 text-xs font-bold text-white">Disetujui</span>
                                        @elseif($item->status === 'diajukan')
                                            <span class="inline-flex items-center rounded-full bg-[#0047d6] px-3 py-1 text-xs font-bold text-white">Diajukan</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-[#d98200] px-3 py-1 text-xs font-bold text-white">Draft</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-col items-stretch gap-1.5">
                                            <a href="{{ route('cetak.catatan', ['siswa_id' => $item->user_id, 'catatan_id' => $item->id]) }}" target="_blank"
                                               class="inline-flex items-center justify-center rounded-xl bg-[#0047d6] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                                                PDF
                                            </a>

                                            @if($item->foto_bukti)
                                                <a href="{{ asset('storage/' . $item->foto_bukti) }}" target="_blank" rel="noopener"
                                                   class="inline-flex items-center justify-center rounded-xl bg-[#05b169] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#049458]">
                                                    Lihat Bukti
                                                </a>

                                                <a href="{{ asset('storage/' . $item->foto_bukti) }}"
                                                   download="bukti-{{ $item->user->nisn ?? $item->user_id }}-{{ $item->id . '.' . pathinfo($item->foto_bukti, PATHINFO_EXTENSION) }}"
                                                   class="inline-flex items-center justify-center rounded-xl border-2 border-[#05b169] bg-white px-3 py-1.5 text-xs font-bold text-[#05b169] transition hover:bg-[#05b169]/5">
                                                    Download Bukti
                                                </a>
                                            @else
                                                <span class="text-center text-xs italic text-[#5b616e]">Belum ada bukti</span>
                                            @endif

                                            @if($item->status === 'diajukan')
                                                <div x-data="{ openValidasi: false }" class="w-full">
                                                    <button type="button" @click="openValidasi = true"
                                                            class="w-full inline-flex items-center justify-center rounded-xl bg-[#d98200] px-3 py-1.5 text-xs font-bold text-white transition hover:bg-[#b56d00]">
                                                        Validasi
                                                    </button>

                                                    <div x-show="openValidasi" x-cloak
                                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                                                         @keydown.escape.window="openValidasi = false">
                                                        <div @click.outside="openValidasi = false" x-transition
                                                             class="w-full max-w-md rounded-2xl bg-white shadow-xl text-left">
                                                            <div class="flex items-center justify-between border-b px-5 py-3">
                                                                <h3 class="font-bold text-black">Validasi Catatan — {{ $item->user->name ?? '-' }} </h3>
                                                                <button type="button" @click="openValidasi = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                                                            </div>

                                                            <div class="p-5 space-y-3">
                                                                <p class="text-sm font-medium text-black">Pekerjaan: <span class="font-bold"> {{ $item->nama_pekerjaan }} </span></p>
                                                                @if($item->catatan_instruktur)
                                                                    <div class="rounded-lg bg-[#d98200]/5 border-l-4 border-[#d98200] p-3 text-sm text-black">
                                                                        <span class="font-bold">Catatan Instruktur:</span><br>
                                                                         {{ $item->catatan_instruktur }} 
                                                                    </div>
                                                                @endif
                                                                <p class="text-xs text-[#5b616e]">Pastikan Anda sudah memeriksa foto bukti fisik (tombol Lihat/Download Bukti) sebelum memvalidasi.</p>
                                                            </div>

                                                            <div class="flex justify-end gap-2 border-t px-5 py-3">
                                                                <form action="{{ route('guru.catatan.validasi', $item->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="aksi" value="valid">
                                                                    <button type="submit"
                                                                            class="rounded-xl bg-[#05b169] px-4 py-2 text-sm font-bold text-white hover:bg-[#049458]">
                                                                        Valid (Setujui)
                                                                    </button>
                                                                </form>
                                                                <form action="{{ route('guru.catatan.validasi', $item->id) }}" method="POST"
                                                                      onsubmit="return confirm('Tolak pengajuan ini? Catatan dikembalikan ke siswa (draft).')">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <input type="hidden" name="aksi" value="tolak">
                                                                    <button type="submit"
                                                                            class="rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">
                                                                        Tolak
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($item->status === 'disetujui')
                                                <span class="text-center text-xs font-bold text-[#05b169]">Sudah divalidasi</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center font-medium text-[#5b616e] italic">
                                        Tidak ada catatan yang cocok / belum ada catatan dari siswa bimbingan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {!! $catatan->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
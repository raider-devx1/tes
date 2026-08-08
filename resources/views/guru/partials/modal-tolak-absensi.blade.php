{{--
|--------------------------------------------------------------------------
| Partial: Tombol + Pop Up Konfirmasi Tolak Absensi
|--------------------------------------------------------------------------
| Dipakai di halaman monitoring absensi guru.
|
| Alur:
|   1. Guru menekan tombol "Tolak".
|   2. Muncul pop up untuk mengisi CATATAN penolakan (wajib, minimal 5 karakter).
|   3. Guru menekan "Konfirmasi Tolak" untuk benar-benar menolak,
|      atau "Batal" jika tidak jadi.
|
| Penting: penolakan TIDAK menghapus data absensi siswa.
| Status, jam masuk, dan jam pulang tetap tersimpan.
| Siswa hanya perlu mengganti FOTO sampai jam pulang berakhir.
|
| Parameter:
|   $a      -> objek Absensi
|   $varian -> 'desktop' | 'mobile' (mempengaruhi lebar tombol saja)
--}}
@props(['a', 'varian' => 'desktop'])

@php
    $modeMobile  = ($varian ?? 'desktop') === 'mobile';
    $lebarTombol = $modeMobile ? 'w-full' : '';
    $padTombol   = $modeMobile ? 'py-2.5' : 'py-2';
    $namaSiswa   = $a->siswa->name ?? 'Siswa';
    $tglLabelTlk = \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y');
@endphp

<div x-data="{ openTolak: false, catatan: '' }" class="{{ $lebarTombol }}">

    {{-- Tombol pemicu --}}
    <button type="button" @click="openTolak = true; catatan = ''"
            class="{{ $lebarTombol }} {{ $padTombol }} inline-flex items-center justify-center gap-1.5 rounded-xl border-2 border-[#cf202f]/40 bg-white px-4 text-sm font-bold text-[#cf202f] transition hover:bg-[#cf202f]/5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
        Tolak
    </button>

    {{-- Pop up konfirmasi (z-index di atas modal validasi) --}}
    <template x-teleport="body">
        {{-- Responsif: lembar bawah (bottom sheet) di HP, dialog tengah di laptop --}}
        <div x-show="openTolak" x-cloak
             class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center sm:p-4"
             role="dialog" aria-modal="true"
             @keydown.escape.window="openTolak = false">

            <div class="absolute inset-0 bg-black/50" @click="openTolak = false"></div>

            <div class="relative flex max-h-[92vh] w-full flex-col overflow-hidden rounded-t-3xl bg-white shadow-xl sm:max-h-[88vh] sm:max-w-lg sm:rounded-2xl">

                {{-- Kepala --}}
                <div class="flex-none items-start justify-between gap-3 bg-[#cf202f] px-4 py-3.5 text-white sm:px-5">
                    <div class="mx-auto mb-2.5 h-1.5 w-10 rounded-full bg-white/40 sm:hidden"></div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                            </svg>
                            <h3 class="text-base font-bold">Tolak Absensi</h3>
                        </div>
                        <button type="button" @click="openTolak = false" aria-label="Tutup"
                                class="-mr-1 flex h-8 w-8 flex-none items-center justify-center rounded-lg text-xl leading-none text-white/80 transition hover:bg-white/15 hover:text-white">&times;</button>
                    </div>
                </div>

                <form method="POST" action="{{ route('guru.absensi.validasi', $a->id) }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="aksi" value="tolak">

                    {{-- Isi yang bisa digulir --}}
                    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-5">

                    <p class="text-sm text-black">
                        Anda akan menolak absensi
                        <span class="font-bold">{{ $namaSiswa }}</span>
                        tanggal <span class="font-bold">{{ $tglLabelTlk }}</span>.
                    </p>

                    {{-- Catatan penolakan (WAJIB) --}}
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">
                            Catatan Penolakan <span class="text-[#cf202f]">*</span>
                        </label>
                        <textarea name="catatan_penolakan" x-model="catatan" rows="3" required minlength="5" maxlength="1000"
                                  placeholder="Contoh: Foto tidak menampilkan wajah / latar belakang bukan tempat industri."
                                  class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-0"></textarea>
                        <div class="mt-1 flex items-center justify-between text-[11px] font-medium">
                            <span class="text-[#5b616e]">Catatan ini akan dibaca siswa di dashboard mereka.</span>
                            <span :class="catatan.trim().length < 5 ? 'text-[#cf202f]' : 'text-[#5b616e]'"
                                  x-text="catatan.length + '/1000'"></span>
                        </div>
                    </div>

                    {{-- Info: data absensi tidak hilang --}}
                    <div class="rounded-xl border-2 border-[#f5b301]/50 bg-[#fff8e6] px-3 py-2.5">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-[#8a6100]">Yang Terjadi Setelah Ditolak</p>
                        <ul class="mt-1 list-disc space-y-0.5 pl-4 text-[12px] font-medium text-black">
                            <li>Data absensi <span class="font-bold">tidak dihapus</span> &mdash; status, jam masuk, dan jam pulang tetap.</li>
                            <li>Siswa hanya perlu <span class="font-bold">mengganti foto</span> dan tidak dihitung Alpha selama foto sudah diganti.</li>
                            <li>Batas ganti foto sampai <span class="font-bold">jam pulang berakhir</span>.</li>
                            <li>Siswa <span class="font-bold">tidak bisa absen pulang</span> sebelum foto diganti.</li>
                        </ul>
                    </div>

                    </div>

                    {{-- Kaki: tombol selalu terlihat, menumpuk di HP --}}
                    <div class="flex-none border-t border-[#e6e9ef] bg-white px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] sm:px-5 sm:pb-3">
                        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button" @click="openTolak = false"
                                    class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-3 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5 sm:w-auto sm:py-2.5">
                                Batal
                            </button>
                            <button type="submit" :disabled="catatan.trim().length < 5"
                                    :class="catatan.trim().length < 5
                                        ? 'bg-[#5b616e]/40 cursor-not-allowed'
                                        : 'bg-[#cf202f] hover:bg-[#a81824] cursor-pointer'"
                                    class="w-full rounded-xl px-4 py-3 text-sm font-bold text-white transition sm:w-auto sm:py-2.5">
                                Konfirmasi Tolak
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

@php
    /**
     * Modal "Ajukan" dengan TANDA TANGAN DIGITAL instruktur.
     * Dipakai bersama oleh halaman Jurnal & Catatan, versi tabel (desktop) maupun kartu (HP).
     *
     * Variabel yang dikirim lewat @include:
     * - $action      : URL form (route ajukan)
     * - $judul       : judul modal
     * - $catatanLama : nilai awal textarea catatan instruktur (opsional)
     * - $namaAwal    : nama instruktur yang otomatis tercetak di bawah paraf (opsional)
     * - $lapis       : kelas z-index, mis. 'z-50' (desktop) / 'z-[60]' (HP)
     *
     * Modal ini memakai variabel Alpine `openAjukan` dari x-data induknya.
     */
    $judul       = $judul ?? 'Ajukan Pengesahan';
    $catatanLama = $catatanLama ?? '';
    $namaAwal    = $namaAwal ?? (auth()->user()->instruktur->name ?? '');
    $namaAwal    = $namaAwal === 'Belum Diatur' ? '' : $namaAwal;
    $lapis       = $lapis ?? 'z-50';
@endphp

<div x-show="openAjukan" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 {{ $lapis }} flex items-end justify-center bg-black/60 p-0 sm:items-center sm:p-4"
     @keydown.escape.window="openAjukan = false">
    <div x-show="openAjukan"
         x-transition:enter="transition ease-out duration-300 delay-[50ms]"
         x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
         class="w-full max-h-[92vh] overflow-y-auto rounded-t-2xl bg-white text-left shadow-xl sm:max-w-lg sm:rounded-2xl"
         @click.outside="openAjukan = false">

        <div class="sticky top-0 z-10 flex items-center justify-between border-b-2 border-[#0047d6]/15 bg-white px-5 py-3">
            <h3 class="text-base font-bold text-black">{{ $judul }}</h3>
            <button type="button" @click="openAjukan = false"
                    class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
        </div>

        <form method="POST" action="{{ $action }}" class="space-y-4 p-5">
            @csrf
            @method('PUT')

            <p class="rounded-xl bg-[#0047d6]/5 p-3 text-xs font-medium leading-relaxed text-[#5b616e]">
                Serahkan HP ini kepada instruktur. Instruktur mengisi catatan lalu
                <b class="text-black">menandatangani langsung di kotak tanda tangan</b> di bawah.
                Tanda tangan tersebut otomatis muncul pada kolom
                <b class="text-black">Paraf Instruktur</b> saat lembar dicetak.
            </p>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-black">
                    Catatan / Nilai dari Instruktur <span class="text-[#cf202f]">*</span>
                </label>
                <textarea name="catatan_instruktur" rows="3" required
                          class="w-full rounded-xl border-2 border-[#0047d6]/25 bg-white px-3 py-2.5 text-sm font-medium text-black focus:border-[#0047d6] focus:ring-2 focus:ring-[#0047d6]/30"
                          placeholder="Catatan / nilai dari instruktur...">{{ old('catatan_instruktur', $catatanLama) }}</textarea>
            </div>

            {{-- Kanvas tanda tangan digital (responsif, bisa dipakai dengan jari di HP) --}}
            <div>
                <x-ttd-pad name="ttd_instruktur" label="Tanda Tangan / Paraf Instruktur" :tinggi="190" />
                <p class="mt-1 text-[11px] text-[#5b616e]">Diparaf oleh:
                    <span class="font-semibold text-black">{{ $namaAwal ?: 'Instruktur (nama belum diatur admin)' }}</span>
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <button type="button" @click="openAjukan = false"
                        class="rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] hover:bg-[#0047d6]/5">
                    Batal
                </button>
                <button type="submit"
                        class="rounded-xl bg-[#05b169] px-5 py-2 text-sm font-bold text-white transition hover:bg-[#049a5b]">
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

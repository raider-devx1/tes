@props([
    'name'      => 'ttd_guru',
    'label'     => 'Tanda Tangan Digital Guru Pembimbing',
    'tinggi'    => 150,
    'tersimpan' => null,
])

{{--
    PEMILIH SUMBER TANDA TANGAN
    -------------------------------------------------------------------------
    Membungkus komponen kanvas tanda tangan dan menambah pilihan bagi guru:

      1. "Pakai tanda tangan tersimpan"  -> memakai berkas yang sudah diunggah
         lewat tombol "Tanda Tangan Saya" (kolom users.ttd_tersimpan).
         Kanvas disembunyikan, guru tidak perlu menggores apa pun.

      2. "Tanda tangan di kanvas"        -> perilaku lama, gores langsung.

    Pilihan dikirim ke server lewat radio bernama "sumber_ttd".
    Bila guru belum punya tanda tangan tersimpan, pilihan tidak ditampilkan dan
    komponen ini berperilaku persis seperti kanvas biasa.

    CATATAN 1
    Kanvas di dalam komponen ini dipasang dengan wajib=false. Penjaga "wajib
    tanda tangan" bawaan kanvas akan memblokir pengiriman form saat kanvas
    kosong, padahal kanvas MEMANG boleh kosong kalau guru memilih tanda tangan
    tersimpan. Penggantinya ada di skrip pada bagian bawah berkas ini.

    CATATAN 2 - PENTING SAAT MENYUNTING BERKAS INI
    Semua nilai kondisional dihitung lebih dulu sebagai variabel PHP di bawah,
    lalu dipakai sebagai variabel biasa. Jangan menulis direktif kondisional
    Blade di dalam tag HTML (contohnya sebagai penambah atribut), karena
    penutupnya yang menempel langsung pada tanda lebih-besar tidak ikut
    terkompilasi dan memicu ParseError di akhir berkas.
    Jangan pula menulis nama direktif Blade sebagai teks di dalam komentar ini,
    karena blok PHP diproses sebelum komentar dibuang.
--}}

@php
    $adaTersimpan = filled($tersimpan);
    $sumberAwal   = $adaTersimpan ? 'tersimpan' : 'canvas';
    $labelKanvas  = $adaTersimpan ? 'Gores tanda tangan di kotak ini' : $label;

    // Kanvas hanya perlu disembunyikan sebelum Alpine siap kalau pilihan awalnya
    // "tersimpan". Kalau guru belum punya tanda tangan tersimpan, kanvas wajib
    // langsung terlihat walaupun Alpine belum termuat.
    $cloakKanvas = $adaTersimpan ? 'x-cloak' : '';
@endphp

<div class="space-y-2" data-ttd-pilih x-data="{ sumber: '{{ $sumberAwal }}' }">

    @if($adaTersimpan)
        <label class="block text-xs font-bold uppercase tracking-wide text-black">
            {{ $label }}<span class="text-[#cf202f]">*</span>
        </label>

        {{-- Dua kartu pilihan. Menumpuk di HP, berdampingan di layar lebar. --}}
        <div class="grid gap-2 sm:grid-cols-2">
            <label class="flex cursor-pointer items-start gap-2 rounded-xl border-2 px-3 py-2 transition"
                   :class="sumber === 'tersimpan' ? 'border-[#05b169] bg-[#05b169]/5' : 'border-[#e6e9ef] bg-white hover:bg-gray-50'">
                <input type="radio" name="sumber_ttd" value="tersimpan" x-model="sumber"
                       class="mt-0.5 h-4 w-4 flex-none border-gray-300 text-[#05b169] focus:ring-[#05b169]">
                <span class="min-w-0">
                    <span class="block text-xs font-bold text-black">Pakai tanda tangan tersimpan</span>
                    <span class="block text-[11px] text-[#5b616e]">Tidak perlu menggores ulang.</span>
                </span>
            </label>

            <label class="flex cursor-pointer items-start gap-2 rounded-xl border-2 px-3 py-2 transition"
                   :class="sumber === 'canvas' ? 'border-[#0047d6] bg-[#0047d6]/5' : 'border-[#e6e9ef] bg-white hover:bg-gray-50'">
                <input type="radio" name="sumber_ttd" value="canvas" x-model="sumber"
                       class="mt-0.5 h-4 w-4 flex-none border-gray-300 text-[#0047d6] focus:ring-[#0047d6]">
                <span class="min-w-0">
                    <span class="block text-xs font-bold text-black">Tanda tangan di kanvas</span>
                    <span class="block text-[11px] text-[#5b616e]">Gores langsung seperti biasa.</span>
                </span>
            </label>
        </div>

        {{-- Pratinjau tanda tangan tersimpan --}}
        <div x-show="sumber === 'tersimpan'" x-cloak
             class="rounded-xl border-2 border-[#05b169] bg-white p-3">
            <div class="flex h-16 items-center justify-center">
                <img src="{{ $tersimpan }}" alt="Tanda tangan tersimpan"
                     class="max-h-full max-w-full object-contain">
            </div>
            <p class="mt-1 text-center text-[11px] font-bold text-[#05b169]">
                Tanda tangan tersimpan siap dipakai.
            </p>
        </div>
    @else
        {{-- Belum punya tanda tangan tersimpan: arahkan guru ke tombol pengunggahnya. --}}
        <div class="flex items-start gap-2 rounded-xl border border-[#f5b301] bg-[#fff8e6] px-3 py-2">
            <span class="mt-0.5 flex h-4 w-4 flex-none items-center justify-center rounded-full bg-[#d98200] text-[10px] font-bold leading-none text-white">i</span>
            <p class="text-[11px] font-semibold text-[#8a6100]">
                Belum ada tanda tangan tersimpan. Unggah sekali lewat tombol
                <span class="font-bold">Tanda Tangan Saya</span> di bagian atas halaman ini,
                supaya berikutnya Anda tidak perlu menggores kanvas lagi.
            </p>
        </div>
    @endif

    {{-- Kanvas tanda tangan (perilaku lama).
         Ukuran kanvas otomatis dihitung ulang oleh ResizeObserver di dalam
         komponen kanvas begitu kotak ini berubah dari tersembunyi ke terlihat. --}}
    <div x-show="sumber === 'canvas'" {!! $cloakKanvas !!}>
        <x-ttd-pad :name="$name" :label="$labelKanvas" :tinggi="$tinggi" :wajib="false" />
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    /* Penjaga "wajib tanda tangan" untuk pemilih sumber tanda tangan.

       Kanvas di dalam komponen ini dipasang dengan wajib=0 supaya penjaga bawaan
       kanvas tidak ikut memblokir. Penjaga di bawah hanya menghalangi pengiriman
       bila guru memilih "canvas" TETAPI kanvasnya masih kosong. Kalau guru memilih
       "tersimpan", form boleh langsung dikirim. */
    if (window.__ttdPilihSiap) return;
    window.__ttdPilihSiap = true;

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        const grup = form.querySelector('[data-ttd-pilih]');
        if (!grup) return;

        const dipilih = grup.querySelector('input[name="sumber_ttd"]:checked');
        const sumber  = dipilih ? dipilih.value : 'canvas';
        if (sumber !== 'canvas') return;          /* pakai tanda tangan tersimpan -> lolos */

        const isi = grup.querySelector('[data-ttd-input]');
        if (isi && isi.value) return;             /* kanvas sudah terisi -> lolos */

        e.preventDefault();
        e.stopPropagation();

        const adaOpsiTersimpan = grup.querySelector('input[name="sumber_ttd"][value="tersimpan"]');
        const pesan = adaOpsiTersimpan
            ? 'Kanvas tanda tangan masih kosong. Gores tanda tangan Anda, atau pilih "Pakai tanda tangan tersimpan".'
            : 'Kanvas tanda tangan masih kosong. Mohon tanda tangani pada kotak yang tersedia sebelum absensi divalidasi.';

        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanda tangan belum ada',
                text: pesan,
                confirmButtonColor: '#0047d6',
            });
        } else {
            alert(pesan);
        }

        const kotak = grup.querySelector('[data-ttd]');
        try {
            (kotak || grup).scrollIntoView({ block: 'center', behavior: 'smooth' });
        } catch (err) {}

        if (kotak && kotak.__ttdUkurUlang) kotak.__ttdUkurUlang();
    }, true);
})();
</script>
@endpush
@endonce

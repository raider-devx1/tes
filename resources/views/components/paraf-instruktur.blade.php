{{--
    Komponen tampilan PARAF DIGITAL INSTRUKTUR (mode baca saja).

    Dipakai di halaman guru (monitoring jurnal & catatan) supaya guru bisa
    melihat langsung hasil paraf digital instruktur pada kolom Status,
    lalu memvalidasi tanpa perlu membuka berkas terpisah.

    Contoh pemakaian:
        <x-paraf-instruktur :ttd="$jurnal->ttd_instruktur"
                            :nama="$jurnal->ttd_instruktur_nama"
                            :waktu="$jurnal->ttd_signed_at"
                            :foto-lama="$jurnal->foto_bukti"
                            :tinggi="42"
                            judul="Paraf Instruktur — Budi"
                            unduh-nama="paraf-jurnal-12" />
--}}
@props([
    'ttd'       => null,   // path relatif di disk 'public', mis. ttd/jurnal/xxxx.png
    'nama'      => null,   // nama instruktur yang memaraf
    'waktu'     => null,   // waktu paraf (Carbon|string|null)
    'fotoLama'  => null,   // data lama: foto lembar berparaf (legacy)
    'tinggi'    => 42,     // tinggi gambar kecil (px)
    'judul'     => 'Paraf Digital Instruktur',
    'label'     => 'Paraf instruktur',   // keterangan kecil di bawah gambar
    'peranLabel' => 'Nama instruktur',   // label baris nama di pop-up perbesar
    'unduhNama' => 'paraf-instruktur',
    'kosong'    => 'Belum ada paraf',
])

@php
    $srcParaf = $ttd ? asset('storage/' . $ttd) : null;
    $srcLama  = $fotoLama ? asset('storage/' . $fotoLama) : null;
    $extLama  = $fotoLama ? pathinfo($fotoLama, PATHINFO_EXTENSION) : null;

    $waktuTeks = null;
    if ($waktu) {
        try {
            $waktuTeks = \Illuminate\Support\Carbon::parse($waktu)->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            $waktuTeks = null;
        }
    }
@endphp

@if($srcParaf)
    <div x-data="{ zoomParaf: false }" class="flex flex-col items-center gap-1">
        {{-- Gambar paraf: klik untuk memperbesar --}}
        <button type="button" @click="zoomParaf = true"
                title="Klik untuk memperbesar paraf instruktur"
                class="block w-full rounded-lg border-2 border-[#05b169]/40 bg-white px-2 py-1 transition hover:border-[#05b169] hover:bg-[#05b169]/5">
            <img src="{{ $srcParaf }}" alt="{{ $label }}"
                 style="height:{{ (int) $tinggi }}px; width:auto; max-width:100%; margin:0 auto; display:block;">
        </button>

        <span class="text-[10px] font-bold uppercase tracking-wide text-[#05b169]">{{ $label }}</span>

        @if($nama)
            <span class="block max-w-[150px] truncate text-[10px] font-semibold text-[#5b616e]" title="{{ $nama }}">{{ $nama }}</span>
        @endif

        {{-- Pop-up perbesar paraf --}}
        <div x-show="zoomParaf" x-cloak
             class="fixed inset-0 z-[60] flex items-center justify-center bg-black/70 p-4"
             @keydown.escape.window="zoomParaf = false">
            <div class="w-full max-w-lg rounded-2xl bg-white text-left shadow-xl" @click.outside="zoomParaf = false">
                <div class="flex items-center justify-between border-b-2 border-[#0047d6]/15 px-5 py-3">
                    <h3 class="text-sm font-bold text-black">{{ $judul }}</h3>
                    <button type="button" @click="zoomParaf = false" class="text-2xl leading-none text-[#5b616e] hover:text-black">&times;</button>
                </div>

                <div class="px-5 py-4">
                    <div class="rounded-xl border-2 border-dashed border-[#0047d6]/20 bg-white p-3">
                        <img src="{{ $srcParaf }}" alt="{{ $label }}"
                             style="width:100%; height:auto; max-height:45vh; object-fit:contain;">
                    </div>
                    <div class="mt-3 space-y-1 text-xs font-medium text-[#5b616e]">
                        <p><span class="font-bold text-black">{{ $peranLabel }}:</span> {{ $nama ?: '-' }}</p>
                        <p><span class="font-bold text-black">Waktu paraf:</span> {{ $waktuTeks ?: '-' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-2 border-t-2 border-[#0047d6]/15 px-5 py-3">
                    <a href="{{ $srcParaf }}" target="_blank" rel="noopener"
                       class="inline-flex items-center rounded-xl bg-[#0047d6] px-4 py-2 text-xs font-bold text-white transition hover:bg-[#0038aa]">
                        Buka Gambar
                    </a>
                    <a href="{{ $srcParaf }}" download="{{ $unduhNama }}.png"
                       class="inline-flex items-center rounded-xl border-2 border-[#0047d6] bg-white px-4 py-2 text-xs font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                        Unduh Paraf
                    </a>
                    <button type="button" @click="zoomParaf = false"
                            class="inline-flex items-center rounded-xl bg-[#5b616e]/10 px-4 py-2 text-xs font-bold text-[#5b616e] transition hover:bg-[#5b616e]/20">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@elseif($srcLama)
    {{-- Data lama: pengajuan versi unggah foto lembar berparaf --}}
    <a href="{{ $srcLama }}" target="_blank" rel="noopener"
       download="{{ $unduhNama }}{{ $extLama ? '.' . $extLama : '' }}"
       class="inline-flex items-center gap-1 rounded-full border-2 border-[#0047d6] bg-white px-2.5 py-1 text-[11px] font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
        Bukti foto (lama)
    </a>
@else
    <span class="block text-[10px] font-semibold italic text-[#5b616e]">{{ $kosong }}</span>
@endif

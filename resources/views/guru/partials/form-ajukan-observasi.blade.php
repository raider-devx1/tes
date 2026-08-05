@php
    /*
    |----------------------------------------------------------------------
    | FORM "AJUKAN / VALIDASI LEMBAR OBSERVASI" (guru pembimbing)
    |----------------------------------------------------------------------
    | Dipakai bersama oleh tampilan desktop & HP pada halaman observasi guru.
    | Variabel yang dibutuhkan:
    |   $obs        -> \App\Models\Observasi
    |   $isWakasek  -> bool (guru berstatus wakasek boleh langsung validasi)
    |
    | Alur baru: hanya BUKTI FOTO OBSERVASI yang diunggah. Paraf instruktur &
    | guru pembimbing dibubuhkan langsung di layar (kanvas, responsif di HP).
    */
    $isWakasek      = $isWakasek ?? false;
    $namaGuru       = auth()->user()->name ?? '-';
    $namaInstruktur = $obs->user->instruktur->name ?? null;
    if ($namaInstruktur === 'Belum Diatur') {
        $namaInstruktur = null;
    }
    $adaParafLama = $obs->ttd_guru || $obs->ttd_instruktur;
@endphp

<p class="mb-4 text-sm text-[#5b616e]">
    Unggah <span class="font-semibold text-black">bukti foto observasi</span>, lalu bubuhkan
    <span class="font-semibold text-black">paraf digital</span> langsung di layar (bisa memakai HP):
    paraf <span class="font-semibold text-black">guru pembimbing</span> di kotak pertama dan paraf
    <span class="font-semibold text-black">instruktur</span> pada kotak di bawahnya.
    Tidak perlu lagi memotret lembar observasi yang sudah diparaf.
    @if($isWakasek)
        Karena Anda berstatus <span class="font-bold text-black">Wakasek</span>, lembar akan langsung <span class="font-bold text-black">tervalidasi</span> dan hasil cetak menampilkan <span class="font-bold text-black">SUDAH DIVALIDASI</span>.
    @else
        Setelah diajukan, lembar berstatus <span class="font-bold text-black">menunggu divalidasi</span> oleh Wakasek yang ditetapkan admin.
    @endif
</p>

@if($adaParafLama)
    {{-- Paraf yang sudah tersimpan sebelumnya (pengajuan ulang akan menggantinya) --}}
    <div class="mb-4 rounded-xl border-2 border-[#05b169]/25 bg-[#05b169]/5 p-3">
        <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-[#049458]">Paraf yang sudah tersimpan</p>
        <div class="flex flex-wrap items-end gap-5">
            @if($obs->ttd_guru)
                <div>
                    <img src="{{ asset('storage/' . $obs->ttd_guru) }}" alt="Paraf guru pembimbing"
                         style="max-height:46px; width:auto; max-width:170px;">
                    <p class="text-[11px] font-semibold text-[#5b616e]">Guru: {{ $obs->ttd_guru_nama ?: $namaGuru }}</p>
                </div>
            @endif
            @if($obs->ttd_instruktur)
                <div>
                    <img src="{{ asset('storage/' . $obs->ttd_instruktur) }}" alt="Paraf instruktur"
                         style="max-height:46px; width:auto; max-width:170px;">
                    <p class="text-[11px] font-semibold text-[#5b616e]">Instruktur: {{ $obs->ttd_instruktur_nama ?: ($namaInstruktur ?: '-') }}</p>
                </div>
            @endif
        </div>
        <p class="mt-2 text-[11px] text-[#5b616e]">Mengirim form ini lagi akan menggantikan paraf di atas.</p>
    </div>
@endif

<form method="POST" action="{{ route('guru.observasi.ajukan', $obs->id) }}"
      enctype="multipart/form-data" class="space-y-4">
    @csrf
    @method('PUT')

    {{-- 1. Bukti foto observasi (tetap diunggah) --}}
    <div>
        <label class="block text-sm font-bold text-black mb-1">
            Bukti Foto Observasi <span class="text-red-500">*</span>
        </label>
        <input type="file" name="foto_dokumentasi" accept="image/*" required
               class="w-full text-sm text-gray-600 rounded-lg border border-gray-300 bg-white file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        <p class="mt-1 text-xs text-gray-500">Wajib. Foto kegiatan/kunjungan observasi. Format JPG/JPEG/PNG, maksimal 3 MB.</p>
    </div>

    {{-- 2. Paraf digital guru pembimbing (atas) --}}
    <div>
        <x-ttd-pad name="ttd_guru" label="Paraf Digital Guru Pembimbing" :tinggi="170" />
        <p class="mt-1 text-[11px] text-[#5b616e]">Diparaf oleh: <span class="font-semibold text-black">{{ $namaGuru }}</span></p>
    </div>

    {{-- 3. Paraf digital instruktur (di bawah paraf guru) --}}
    <div>
        <x-ttd-pad name="ttd_instruktur" label="Paraf Digital Instruktur" :tinggi="170" />
        <p class="mt-1 text-[11px] text-[#5b616e]">Diparaf oleh: <span class="font-semibold text-black">{{ $namaInstruktur ?: 'Instruktur (nama belum diatur admin)' }}</span></p>
    </div>

    <div class="rounded-xl bg-[#0047d6]/5 p-3 text-[11px] font-medium text-[#5b616e]">
        Hasil kedua paraf otomatis tercetak pada kolom <span class="font-bold text-black">PARAF INST.</span> dan
        <span class="font-bold text-black">PARAF PEMB.</span> di PDF lembar observasi.
    </div>

    <div class="flex justify-end gap-2 pt-2">
        <button type="button" @click="showValidasi = false"
                class="inline-flex items-center rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
            Batal
        </button>
        <button type="submit"
                class="inline-flex items-center rounded-xl bg-[#05b169] px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#049457]">
            {{ $isWakasek ? 'Simpan & Validasi' : 'Ajukan Sekarang' }}
        </button>
    </div>
</form>

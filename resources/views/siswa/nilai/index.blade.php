<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Lembar Penilaian PKL Saya</h2>
    </x-slot>

    <style>[x-cloak]{display:none!important;}</style>

    @php
        $daftarSkor = [
            optional($nilai)->skor_soft_skill,
            optional($nilai)->skor_hard_skill,
            optional($nilai)->skor_pengembangan,
            optional($nilai)->skor_kewirausahaan,
            optional($nilai)->skor_laporan,
            optional($nilai)->skor_presentasi,
        ];
        $nilaiLengkap  = $nilai && ! in_array(null, $daftarSkor, true);
        $rataRataAkhir = $nilaiLengkap ? round(array_sum($daftarSkor) / count($daftarSkor), 2) : null;
        $komponen = [
            ['Internalisasi & Penerapan Soft Skill', optional($nilai)->skor_soft_skill],
            ['Penerapan Hard Skill', optional($nilai)->skor_hard_skill],
            ['Peningkatan & Pengembangan Hard Skill', optional($nilai)->skor_pengembangan],
            ['Penyiapan & Kemandirian Kewirausahaan', optional($nilai)->skor_kewirausahaan],
            ['Penulisan Laporan', optional($nilai)->skor_laporan],
            ['Pemaparan Presentasi', optional($nilai)->skor_presentasi],
        ];
    @endphp

    <div class="py-8 md:py-12 bg-white">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 2xl:px-12 space-y-6">

            {{-- ===== TOMBOL KEMBALI (paling atas) ===== --}}
            <div>
                <a href="{{ route('siswa.dashboard') }}"
                   class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            {{-- ===== CARD MENU: CETAK (hanya jika nilai ada) ===== --}}
            @if($nilai)
                <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-4 sm:p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:justify-end gap-3">
                        <a href="{{ route('cetak.nilai.guru') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                            <svg xmlns="http://www.w3.org/2000/xl" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak PDF
                        </a>
                    </div>
                </div>
            @endif

            {{-- ===== CARD UTAMA: DATA PENILAIAN ===== --}}
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 sm:p-6 md:p-8 shadow-sm">
                @if(!$nilai)
                    <div class="text-center py-10 font-bold text-[#5b616e]">Nilai belum lengkap</div>
                @else
                    <h3 class="text-lg font-bold text-black border-b-2 border-[#0047d6]/15 pb-2 mb-3">Penilaian PKL (skala 0–100)</h3>

                    {{-- Selalu 1 kolom vertikal berurutan (1 -> 6), card tetap full kiri-kanan --}}
                    <div class="space-y-3">
                        @foreach($komponen as $index => $item)
                            <div class="flex justify-between border-b border-[#0047d6]/10 pb-2">
                                <span class="font-medium text-black">{{ $index + 1 }}. {{ $item[0] }}</span>
                                <span class="font-bold text-[#0047d6]">{{ $item[1] ?? '-' }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if($nilai->catatan_guru)
                        <div class="mt-4 rounded-xl border-2 border-[#05b169]/40 bg-[#05b169]/5 p-3">
                            <h4 class="font-bold text-black text-sm mb-1">Catatan Guru Pembimbing:</h4>
                            <p class="text-sm font-medium text-black italic">" {{ $nilai->catatan_guru }} "</p>
                        </div>
                    @endif

                    {{-- ===== RATA-RATA / STATUS ===== --}}
                    @if($nilaiLengkap)
                        <div class="flex flex-wrap justify-between items-center gap-2 rounded-2xl border-2 border-[#0047d6] bg-[#0047d6]/5 p-4 sm:p-5 mt-8">
                            <span class="font-bold text-black text-base sm:text-lg">RATA-RATA NILAI AKHIR (0–100):</span>
                            <span class="font-black text-2xl sm:text-3xl text-[#0047d6]">{{ $rataRataAkhir }}</span>
                        </div>
                    @else
                        <div class="flex justify-center items-center rounded-2xl border-2 border-amber-400 bg-amber-50 p-4 sm:p-5 mt-8">
                            <span class="font-bold text-amber-700 text-base sm:text-lg">Nilai belum lengkap</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl md:text-2xl font-bold tracking-tight text-black">Lembar Penilaian PKL Saya</h2>
            <a href="{{ route('siswa.dashboard') }}"
               class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

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
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 sm:p-6 md:p-8 shadow-sm">
                @if(!$nilai)
                    <div class="text-center py-10 font-bold text-[#5b616e]">Nilai belum lengkap</div>
                @else
                    {{-- ===== TOMBOL CETAK (format penilaian guru) ===== --}}
                    <div class="flex justify-end mb-6">
                        <a href="{{ route('cetak.nilai.guru') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                            Cetak PDF
                        </a>
                    </div>

                    <h3 class="text-lg font-bold text-black border-b-2 border-[#0047d6]/15 pb-2 mb-3">Penilaian PKL (skala 0–100)</h3>

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
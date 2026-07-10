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

    <div class="py-8 md:py-12 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border-2 border-[#0047d6]/15 bg-white p-5 sm:p-6 md:p-8 shadow-sm">
                @if(!$nilai)
                    <div class="text-center py-8 font-medium text-[#5b616e]">Belum ada lembar penilaian yang dirilis.</div>
                @else
                    {{-- ===== TOMBOL CETAK ===== --}}
                    <div class="flex justify-end mb-6">
                        <a href="{{ route('cetak.nilai') }}" target="_blank"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0047d6] px-6 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-[#0038aa] focus:outline-none focus:ring-4 focus:ring-[#0047d6]/30">
                            Cetak PDF
                        </a>
                    </div>

                    {{-- ===== BAGIAN A: INDUSTRI ===== --}}
                    <h3 class="text-lg font-bold text-black border-b-2 border-[#0047d6]/15 pb-2 mb-3">A. Penilaian Instruktur Industri (skala 1–5)</h3>
                    @if(is_null($nilai->rata_rata))
                        <p class="text-sm font-medium text-[#5b616e] mb-4">Belum dinilai oleh instruktur.</p>
                    @else
                        <div class="space-y-3">
                            <div class="flex justify-between border-b border-[#0047d6]/10 pb-2">
                                <span class="font-medium text-black">Internalisasi &amp; Penerapan Soft Skill</span>
                                <span class="font-bold text-[#0047d6]">{{ $nilai->nilai_soft_skill }} / 5</span>
                            </div>
                            <div class="flex justify-between border-b border-[#0047d6]/10 pb-2">
                                <span class="font-medium text-black">Penerapan Hard Skill</span>
                                <span class="font-bold text-[#0047d6]">{{ $nilai->nilai_hard_skill_penerapan }} / 5</span>
                            </div>
                            <div class="flex justify-between border-b border-[#0047d6]/10 pb-2">
                                <span class="font-medium text-black">Pengembangan Hard Skill</span>
                                <span class="font-bold text-[#0047d6]">{{ $nilai->nilai_hard_skill_pengembangan }} / 5</span>
                            </div>
                            <div class="flex justify-between border-b border-[#0047d6]/10 pb-2">
                                <span class="font-medium text-black">Kemandirian &amp; Kewirausahaan</span>
                                <span class="font-bold text-[#0047d6]">{{ $nilai->nilai_kemandirian }} / 5</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-bold text-black">Rata-rata Instruktur</span>
                                <span class="font-bold text-[#0047d6]">{{ $nilai->rata_rata }} / 5</span>
                            </div>
                        </div>
                        @if($nilai->catatan_rekomendasi)
                            <div class="mt-3 rounded-xl border-2 border-[#d98200]/40 bg-[#d98200]/5 p-3">
                                <p class="text-sm font-medium text-black italic">" {{ $nilai->catatan_rekomendasi }} "</p>
                            </div>
                        @endif
                    @endif

                    {{-- ===== BAGIAN B: SEKOLAH ===== --}}
                    <h3 class="text-lg font-bold text-black border-b-2 border-[#0047d6]/15 pb-2 mb-3 mt-6">B. Penilaian Guru Pembimbing &amp; Laporan (skala 0–100)</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between border-b border-[#0047d6]/10 pb-2">
                            <span class="font-medium text-black">Nilai Guru Pembimbing</span>
                            <span class="font-bold text-[#05b169]">{{ $nilai->nilai_guru ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-[#0047d6]/10 pb-2">
                            <span class="font-medium text-black">Nilai Laporan Akhir</span>
                            <span class="font-bold text-[#05b169]">{{ $nilai->nilai_laporan ?? '-' }}</span>
                        </div>
                    </div>
                    @if($nilai->catatan_guru)
                        <div class="mt-3 rounded-xl border-2 border-[#05b169]/40 bg-[#05b169]/5 p-3">
                            <h4 class="font-bold text-black text-sm mb-1">Catatan Guru Pembimbing:</h4>
                            <p class="text-sm font-medium text-black italic">" {{ $nilai->catatan_guru }} "</p>
                        </div>
                    @endif

                    {{-- ===== TOTAL NILAI AKHIR ===== --}}
                    <div class="flex flex-wrap justify-between items-center gap-2 rounded-2xl border-2 border-[#0047d6] bg-[#0047d6]/5 p-4 sm:p-5 mt-8">
                        <span class="font-bold text-black text-base sm:text-lg">NILAI AKHIR PKL (0–100):</span>
                        <span class="font-black text-2xl sm:text-3xl text-[#0047d6]">
                            {{ $nilai->nilai_akhir ?? '-' }} 
                        </span>
                    </div>
                    <p class="text-xs font-medium text-[#5b616e] mt-2 text-right">Formula: 50% Instruktur + 20% Guru + 30% Laporan</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
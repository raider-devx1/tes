<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lembar Penilaian PKL Saya</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(!$nilai)
                    <div class="text-center py-6 text-gray-500">Belum ada lembar penilaian yang dirilis.</div>
                @else
                    <div class="flex justify-end mb-4">
                        <a href=" route('cetak.nilai') " target="_blank" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded font-semibold">Cetak PDF</a>
                    </div>

                    -- A. Penilaian Instruktur (1–5) --
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-3">A. Penilaian Instruktur Industri (skala 1–5)</h3>
                    @if(is_null($nilai->rata_rata))
                        <p class="text-sm text-gray-500 mb-4">Belum dinilai oleh instruktur.</p>
                    @else
                        <div class="space-y-3">
                            <div class="flex justify-between border-b pb-2"><span class="text-gray-700">Internalisasi &amp; Penerapan Soft Skill</span><span class="font-bold text-blue-600"> $nilai->soft_skill  / 5</span></div>
                            <div class="flex justify-between border-b pb-2"><span class="text-gray-700">Penerapan Hard Skill</span><span class="font-bold text-blue-600"> $nilai->hard_skill  / 5</span></div>
                            <div class="flex justify-between border-b pb-2"><span class="text-gray-700">Pengembangan Hard Skill</span><span class="font-bold text-blue-600"> $nilai->pengembangan_hard_skill  / 5</span></div>
                            <div class="flex justify-between border-b pb-2"><span class="text-gray-700">Kemandirian &amp; Kewirausahaan</span><span class="font-bold text-blue-600"> $nilai->kewirausahaan  / 5</span></div>
                            <div class="flex justify-between"><span class="text-gray-700 font-medium">Rata-rata Instruktur</span><span class="font-bold text-blue-700"> number_format($nilai->rata_rata, 2)  / 5</span></div>
                        </div>
                        @if($nilai->catatan_rekomendasi)
                            <div class="mt-3 p-3 border border-yellow-200 bg-yellow-50 rounded">
                                <p class="text-sm text-gray-700 italic">" $nilai->catatan_rekomendasi "</p>
                            </div>
                        @endif
                    @endif

                    -- B. Penilaian Guru & Laporan (0–100) --
                    <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-3 mt-6">B. Penilaian Guru Pembimbing &amp; Laporan (skala 0–100)</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between border-b pb-2"><span class="text-gray-700">Nilai Guru Pembimbing</span><span class="font-bold text-green-600"> !is_null($nilai->nilai_guru) ? number_format($nilai->nilai_guru, 2) : '—' </span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-gray-700">Nilai Laporan Akhir</span><span class="font-bold text-green-600"> !is_null($nilai->nilai_laporan) ? number_format($nilai->nilai_laporan, 2) : '—' </span></div>
                    </div>
                    @if($nilai->catatan_guru)
                        <div class="mt-3 p-3 border border-green-200 bg-green-50 rounded">
                            <h4 class="font-bold text-green-800 text-sm mb-1">Catatan Guru Pembimbing:</h4>
                            <p class="text-sm text-gray-700 italic">" $nilai->catatan_guru "</p>
                        </div>
                    @endif

                    -- Nilai Akhir --
                    <div class="flex justify-between items-center bg-blue-50 p-4 rounded-lg mt-8">
                        <span class="text-blue-900 font-bold text-lg">NILAI AKHIR PKL (0–100):</span>
                        <span class="font-black text-2xl text-blue-700"> !is_null($nilai->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : 'Menunggu kelengkapan' </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-right">Formula: 50% Instruktur + 20% Guru + 30% Laporan</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
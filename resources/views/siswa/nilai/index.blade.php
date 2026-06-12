<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Lembar Capaian Nilai PKL Saya') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(!$nilai)
                    <div class="text-center py-6 text-gray-500">Belum ada rilis lembar penilaian kompetensi dari instruktur industri Anda.</div>
                @else
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('cetak.nilai') }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded font-semibold transition">Cetak Format Dokumen PDF</a>
                    </div>

                    <div class="border rounded-lg overflow-hidden bg-gray-50 p-4 mb-6">
                        <p class="text-sm text-gray-600"><strong>Penilai Industri:</strong> {{ $nilai->instruktur->name }}</p>
                        <p class="text-sm text-gray-600"><strong>Waktu Penilaian:</strong> {{ $nilai->updated_at->format('d M Y - H:i') }} WIB</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between border-b pb-2"><span class="text-gray-700 font-medium">Internalisasi dan Penerapan Soft Skill</span><span class="font-bold text-lg text-blue-600">{{ $nilai->soft_skill }} / 5</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-gray-700 font-medium">Penerapan Hard Skill</span><span class="font-bold text-lg text-blue-600">{{ $nilai->hard_skill }} / 5</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-gray-700 font-medium">Peningkatan dan Pengembangan Hard Skill</span><span class="font-bold text-lg text-blue-600">{{ $nilai->pengembangan_hard_skill }} / 5</span></div>
                        <div class="flex justify-between border-b pb-2"><span class="text-gray-700 font-medium">Penyiapan Kemandirian dan Kewirausahaan</span><span class="font-bold text-lg text-blue-600">{{ $nilai->kewirausahaan }} / 5</span></div>
                        
                        <div class="flex justify-between items-center bg-blue-50 p-4 rounded-lg mt-6"><span class="text-blue-900 font-bold text-lg">SKOR AKHIR (RATA-RATA) :</span><span class="font-black text-2xl text-blue-700">{{ $nilai->rata_rata }}</span></div>
                    </div>

                    @if($nilai->catatan_rekomendasi)
                        <div class="mt-6 p-4 border border-yellow-200 bg-yellow-50 rounded-lg">
                            <h4 class="font-bold text-yellow-800 text-sm mb-1">Catatan Tambahan / Rekomendasi Instruktur:</h4>
                            <p class="text-sm text-gray-700 italic">"{{ $nilai->catatan_rekomendasi }}"</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
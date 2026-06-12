<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Input Penilaian: {{ $siswa->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('instruktur.nilai.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $siswa->id }}">

                    @php
                        $komponen = [
                            'soft_skill' => 'Internalisasi dan Penerapan Soft Skill',
                            'hard_skill' => 'Penerapan Hard Skill',
                            'pengembangan_hard_skill' => 'Peningkatan dan Pengembangan Hard Skill',
                            'kewirausahaan' => 'Penyiapan Kemandirian dan Kewirausahaan'
                        ];
                    @endphp

                    @foreach($komponen as $field => $label)
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">{{ $label }}</label>
                            <select name="{{ $field }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                                <option value="">-- Berikan Rentang Nilai (1 - 5) --</option>
                                @for($i=5; $i>=1; $i--)
                                    <option value="{{ $i }}" {{ old($field, $siswa->nilai->$field ?? '') == $i ? 'selected' : '' }}>{{ $i }} - {{ $i == 5 ? 'Sangat Baik' : ($i == 4 ? 'Baik' : ($i == 3 ? 'Cukup' : ($i == 2 ? 'Kurang' : 'Sangat Kurang'))) }}</option>
                                @endfor
                            </select>
                        </div>
                    @endforeach

                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Rekomendasi / Kualifikasi Evaluasi</label>
                        <textarea name="catatan_rekomendasi" rows="3" placeholder="Masukkan komentar saran instruktur..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-200">{{ old('catatan_rekomendasi', $siswa->nilai->catatan_rekomendasi ?? '') }}</textarea>
                    </div>

                    <div class="flex justify-end pt-4">
                        <a href="{{ route('instruktur.nilai.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mr-2">Batal</a>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded font-semibold shadow">Simpan Hasil Penilaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
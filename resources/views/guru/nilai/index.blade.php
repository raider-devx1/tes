<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekap & Penilaian (Guru Pembimbing)</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-3 mb-4 rounded font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                <p class="text-sm text-gray-500 mb-4">
                    Nilai Instruktur (skala 1–5) diisi oleh instruktur industri.
                    <strong>Nilai Guru</strong> &amp; <strong>Nilai Laporan</strong> (skala 0–100) diisi oleh Anda.
                    Nilai Akhir = 50% Instruktur + 20% Guru + 30% Laporan.
                </p>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 border">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 border">Siswa</th>
                                <th class="px-3 py-3 border text-center">Instruktur (/5)</th>
                                <th class="px-3 py-3 border text-center">Nilai Guru (0–100)</th>
                                <th class="px-3 py-3 border text-center">Nilai Laporan (0–100)</th>
                                <th class="px-3 py-3 border">Catatan Guru</th>
                                <th class="px-3 py-3 border text-center bg-blue-50 text-blue-900">Nilai Akhir</th>
                                <th class="px-3 py-3 border text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswa as $item)
                                @php $n = $item->nilai; @endphp
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-3 py-3 border font-bold text-gray-900">
                                        {{ $item->name }}
                                    </td>

                                    <td class="px-3 py-3 border text-center">
                                        {{ $n && !is_null($n->rata_rata) ? number_format($n->rata_rata, 2) : '—' }}
                                    </td>

                                    <td class="px-3 py-3 border text-center">
                                        <input type="number" form="form-guru-{{ $item->id }}" name="nilai_guru"
                                               min="0" max="100" step="0.01"
                                               value="{{ old('nilai_guru', $n->nilai_guru ?? '') }}"
                                               class="w-20 border-gray-300 rounded text-center" required>
                                    </td>

                                    <td class="px-3 py-3 border text-center">
                                        <input type="number" form="form-guru-{{ $item->id }}" name="nilai_laporan"
                                               min="0" max="100" step="0.01"
                                               value="{{ old('nilai_laporan', $n->nilai_laporan ?? '') }}"
                                               class="w-20 border-gray-300 rounded text-center" required>
                                    </td>

                                    <td class="px-3 py-3 border">
                                        <textarea form="form-guru-{{ $item->id }}" name="catatan_guru" rows="1"
                                                  placeholder="Opsional..."
                                                  class="w-full border-gray-300 rounded text-sm">{{ old('catatan_guru', $n->catatan_guru ?? '') }}</textarea>
                                    </td>

                                    <td class="px-3 py-3 border text-center font-black text-blue-700 bg-blue-50/50">
                                        {{ $n && !is_null($n->nilai_akhir) ? number_format($n->nilai_akhir, 2) : 'Menunggu' }}
                                    </td>

                                    <td class="px-3 py-3 border text-center whitespace-nowrap">
                                        <form id="form-guru-{{ $item->id }}" action="{{ route('guru.nilai.store') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $item->id }}">
                                            <button type="submit" class="bg-blue-600 text-white font-semibold py-1.5 px-4 rounded text-xs hover:bg-blue-800">Simpan</button>
                                        </form>
                                        <a href="{{ route('cetak.nilai', $item->id) }}" target="_blank"
                                           class="inline-block mt-2 bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded">PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada siswa bimbingan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
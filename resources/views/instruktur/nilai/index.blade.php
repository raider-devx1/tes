<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Penilaian Akhir PKL</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-3 mb-4 rounded font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('instruktur.nilai.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status Penilaian</label>
                            <select name="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Status --</option>
                                <option value="sudah" @selected(request('status') === 'sudah')>Sudah Dinilai</option>
                                <option value="belum" @selected(request('status') === 'belum')>Belum Dinilai</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">Cari</button>
                            <a href="{{ route('instruktur.nilai.index') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-md transition inline-block text-center">Reset</a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL PENILAIAN ===== --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-sm text-gray-700">
                                <th class="border p-2 text-center w-12" rowspan="2">No</th>
                                <th class="border p-2" rowspan="2">Siswa</th>
                                <th class="border p-2" rowspan="2">NISN</th>
                                <th class="border p-2 text-center" colspan="4">Kriteria Nilai (1 - 5)</th>
                                <th class="border p-2" rowspan="2">Catatan Instruktur</th>
                                <th class="border p-2 text-center" rowspan="2">Aksi</th>
                            </tr>
                            <tr class="bg-gray-55 text-xs text-gray-600">
                                <th class="border p-2 text-center w-20">Soft Skills</th>
                                <th class="border p-2 text-center w-20">Hard Skills</th>
                                <th class="border p-2 text-center w-24">Pengembangan</th>
                                <th class="border p-2 text-center w-28">Kewirausahaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswa as $item)
                                @php
                                    // Data nilai siswa ini (jika sudah ada)
                                    $n = $item->nilai;
                                    $sudahDinilai = $n && $n->rata_rata !== null;
                                @endphp
                                <tr class="hover:bg-gray-50 align-top transition">
                                    <td class="border p-2 text-center text-gray-500">{{ $siswa->firstItem() + $loop->index }}</td>
                                    <td class="border p-2 font-bold text-gray-900">{{ $item->name }}</td>
                                    <td class="border p-2 whitespace-nowrap text-gray-600">{{ $item->nisn ?? '-' }}</td>

                                    <td class="border p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="soft_skill" min="1" max="5" value="{{ old('soft_skill', optional($n)->soft_skill) }}" class="w-16 border-gray-300 rounded text-center text-sm" required>
                                    </td>
                                    <td class="border p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="hard_skill" min="1" max="5" value="{{ old('hard_skill', optional($n)->hard_skill) }}" class="w-16 border-gray-300 rounded text-center text-sm" required>
                                    </td>
                                    <td class="border p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="pengembangan_hard_skill" min="1" max="5" value="{{ old('pengembangan_hard_skill', optional($n)->pengembangan_hard_skill) }}" class="w-16 border-gray-300 rounded text-center text-sm" required>
                                    </td>
                                    <td class="border p-2 text-center">
                                        <input type="number" form="form-nilai-{{ $item->id }}" name="kewirausahaan" min="1" max="5" value="{{ old('kewirausahaan', optional($n)->kewirausahaan) }}" class="w-16 border-gray-300 rounded text-center text-sm" required>
                                    </td>
                                    <td class="border p-2">
                                        <textarea form="form-nilai-{{ $item->id }}" name="catatan_rekomendasi" rows="1" placeholder="Opsional..." class="w-full border-gray-300 rounded text-sm">{{ old('catatan_rekomendasi', optional($n)->catatan_rekomendasi) }}</textarea>
                                    </td>

                                    <td class="border p-2 text-center whitespace-nowrap">
                                        <form id="form-nilai-{{ $item->id }}" action="{{ route('instruktur.nilai.store') }}" method="POST" class="inline block mb-2">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $item->id }}">
                                            @if($sudahDinilai)
                                                <div class="text-green-700 bg-green-50 font-semibold py-1 px-2 rounded text-xs mb-2 text-center border border-green-200">✓ Sudah Disimpan</div>
                                                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white text-xs py-1.5 px-3 rounded shadow-sm transition">Perbarui</button>
                                            @else
                                                <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-1.5 px-4 rounded text-xs hover:bg-blue-800 shadow-sm transition">Simpan</button>
                                            @endif
                                        </form>
                                        <a href="{{ route('cetak.nilai', $item->id) }}" target="_blank" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white text-xs py-1.5 px-3 rounded shadow-sm transition">Cetak PDF</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="border p-6 text-center text-gray-400 italic">Tidak ada data siswa PKL yang Anda bimbing / cocok dengan pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $siswa->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
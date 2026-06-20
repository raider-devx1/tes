<x-app-layout title="Rekap Penilaian">
    <div class="max-w-7xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Rekap Penilaian PKL</h2>
            <p class="text-sm text-gray-500">Ringkasan statistik penilaian seluruh siswa (hanya-baca).</p>
        </div>

        {{-- Statistik Utama Kartu --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Total Siswa</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalSiswa }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Sudah Dinilai</p>
                <p class="text-2xl font-bold text-green-600">{{ $sudahDinilai }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Belum Lengkap</p>
                <p class="text-2xl font-bold text-amber-500">{{ $belumLengkap }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 p-4">
                <p class="text-xs text-gray-500">Rata Nilai Akhir</p>
                <p class="text-2xl font-bold text-[#2563EB]">{{ $statNilaiAkhir['rata'] }}</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            {{-- Progres Bar Komponen Nilai --}}
            <div class="bg-white rounded-xl border border-blue-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Rata-rata per Komponen (skala 1–5)</h3>
                <div class="space-y-3">
                    @foreach($rataKomponen as $label => $val)
                        @php $persen = ($val / 5) * 100; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $label }}</span>
                                <span class="font-medium text-gray-800">{{ $val }}</span>
                            </div>
                            <div class="w-full h-2 bg-blue-50 rounded-full overflow-hidden">
                                <div class="h-full bg-[#2563EB] rounded-full" style="width: {{ $persen }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Distribusi Kelompok Nilai --}}
            <div class="bg-white rounded-xl border border-blue-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Distribusi Nilai Akhir</h3>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    @foreach($distribusi as $label => $jml)
                        <div class="flex items-center justify-between rounded-lg bg-blue-50/60 px-3 py-2">
                            <span class="text-sm text-gray-600">{{ $label }}</span>
                            <span class="font-bold text-gray-800">{{ $jml }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between text-sm border-t border-gray-100 pt-3">
                    <span class="text-gray-500">Tertinggi: <span class="font-semibold text-green-600">{{ $statNilaiAkhir['tertinggi'] }}</span></span>
                    <span class="text-gray-500">Terendah: <span class="font-semibold text-red-500">{{ $statNilaiAkhir['terendah'] }}</span></span>
                </div>
            </div>
        </div>

        {{-- Tabel 10 Besar Peringkat --}}
        <div class="bg-white rounded-xl border border-blue-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-blue-100">
                <h3 class="font-semibold text-gray-800">🏆 Peringkat 10 Besar</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3 text-center">#</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3 text-center">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($peringkat as $i => $p)
                            <tr class="hover:bg-blue-50/40">
                                <td class="px-4 py-3 text-center font-bold text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $p->user->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-bold text-[#2563EB]">{{ number_format($p->nilai_akhir, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Belum ada data penilaian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
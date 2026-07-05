<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitoring: ') . $siswa->name }}
        </h2>
         <a href="{{ route('guru.dashboard') }}"
           class="inline-flex items-center gap-1 rounded-xl border-2 border-[#0047d6]/25 bg-white px-4 py-2 text-sm font-bold text-[#0047d6] transition hover:bg-[#0047d6]/5">
            Kembali ke Dashboard
        </a>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">Jurnal Kegiatan Terakhir</h3>
                    <a href="{{ route('cetak.jurnal', $siswa->id) }}" target="_blank" class="bg-gray-600 hover:bg-gray-800 text-white text-sm py-1 px-3 rounded">Cetak Jurnal PDF</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2">Tanggal</th>
                                <th class="border p-2">Unit Kerja</th>
                                <th class="border p-2">Status & Catatan Instruktur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurnals as $jurnal)
                            <tr>
                                <td class="border p-2 text-sm">{{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->format('d M Y') }}</td>
                                <td class="border p-2 text-sm">{{ $jurnal->unit_kerja }}</td>
                                <td class="border p-2 text-sm">
                                    <span class="font-bold uppercase text-xs">{{ $jurnal->status_persetujuan }}</span>
                                    <p class="text-gray-500 italic mt-1">{{ $jurnal->catatan_instruktur ?? '-' }}</p>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="border p-2 text-center">Belum ada jurnal.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Riwayat Kehadiran (Absensi)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2">Tanggal</th>
                                <th class="border p-2">Status</th>
                                <th class="border p-2">Jam Masuk</th>
                                <th class="border p-2">Jam Pulang</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absensis as $absen)
                            <tr>
                                <td class="border p-2 text-sm">{{ \Carbon\Carbon::parse($absen->tanggal)->format('d M Y') }}</td>
                                <td class="border p-2 text-sm font-bold">{{ $absen->status }}</td>
                                <td class="border p-2 text-sm">{{ $absen->jam_masuk ?? '-' }}</td>
                                <td class="border p-2 text-sm">{{ $absen->jam_pulang ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="border p-2 text-center">Belum ada absensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
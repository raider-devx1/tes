<x-app-layout title="Notifikasi Sistem">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Notifikasi Sistem</h2>
            <p class="text-sm text-gray-500">Peringatan otomatis: jurnal belum disetujui instruktur, siswa belum mengisi jurnal, dan guru belum melakukan observasi.</p>
        </div>
        <span class="inline-flex items-center gap-2 self-start px-3 py-1.5 rounded-full bg-[#2563EB] text-white text-sm font-medium">
            {{ count($notifikasi) }} Notifikasi
        </span>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-blue-100">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Nama</th>
                        <th class="py-3 px-3">NISN</th>
                        <th class="py-3 px-3">NIP</th>
                        <th class="py-3 px-3">Email</th>
                        <th class="py-3 px-3">Notifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifikasi as $i => $n)
                        <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                            <td class="py-3 px-3 text-center text-gray-500">{{ $i + 1 }}</td>
                            <td class="py-3 px-3 font-medium text-gray-800">{{ $n['nama'] }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $n['nisn'] }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $n['nip'] }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $n['email'] }}</td>
                            <td class="py-3 px-3">
                                @php 
                                    $warna = ($n['kategori'] ?? 'warning') === 'danger' 
                                        ? 'bg-red-50 text-red-600' 
                                        : 'bg-amber-50 text-amber-600'; 
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $warna }}">
                                    {{ $n['keterangan'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-400">
                                🎉 Semua kondisi aman. Tidak ada notifikasi saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
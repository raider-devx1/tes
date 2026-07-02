<x-app-layout title="Notifikasi Sistem">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Notifikasi Sistem</h2>
            <p class="text-sm text-gray-500">Peringatan otomatis, diperbarui berkala (realtime).</p>
        </div>
        <span class="inline-flex items-center gap-2 self-start px-3 py-1.5 rounded-full bg-[#2563EB] text-white text-sm font-medium">
            {{ $notifikasi->total() }} Notifikasi
        </span>
    </div>

    {{-- ===== KARTU RINGKASAN REALTIME ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 border-l-4 border-l-amber-400 p-5">
            <p class="text-xs uppercase tracking-wide text-gray-400">Guru Belum Observasi</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $ringkasan['guru_observasi'] }}</h3>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 border-l-4 border-l-red-400 p-5">
            <p class="text-xs uppercase tracking-wide text-gray-400">Siswa Belum Isi Jurnal</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $ringkasan['siswa_jurnal'] }}</h3>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 border-l-4 border-l-[#2563EB] p-5">
            <p class="text-xs uppercase tracking-wide text-gray-400">Jurnal Belum Disetujui Instruktur</p>
            <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $ringkasan['jurnal_pending'] }}</h3>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-5">

        {{-- ===== FILTER PENCARIAN ===== --}}
        <form method="GET" class="mb-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / NISN / NIP..."
                   class="w-full sm:w-80 rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
            <button class="px-4 py-2 rounded-lg bg-blue-50 text-[#2563EB] text-sm font-medium hover:bg-blue-100">Cari</button>
            @if($q)
                <a href="{{ route('admin.notifikasi.index') }}" class="px-4 py-2 rounded-lg text-gray-500 text-sm hover:bg-gray-50">Reset</a>
            @endif
        </form>

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
                    @forelse($notifikasi as $n)
                        <tr class="border-b border-blue-50 hover:bg-blue-50/40">
                            <td class="py-3 px-3 text-center text-gray-500">{{ $notifikasi->firstItem() + $loop->index }}</td>
                            <td class="py-3 px-3 font-medium text-gray-800">{{ $n['nama'] }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $n['nisn'] }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $n['nip'] }}</td>
                            <td class="py-3 px-3 text-gray-600">{{ $n['email'] }}</td>
                            <td class="py-3 px-3">
                                @php 
                                    $warna = ($n['kategori'] ?? 'warning') === 'danger' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600'; 
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-medium {{ $warna }}">
                                    {{ $n['keterangan'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-400">
                                🎉 Tidak ada notifikasi yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ===== PAGINATION ===== --}}
        <div class="mt-4">
            {!! $notifikasi->links() !!}
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto-refresh ringan agar notifikasi mendekati realtime (tiap 60 detik).
        setTimeout(function () { window.location.reload(); }, 60000);
    </script>
    @endpush

</x-app-layout>
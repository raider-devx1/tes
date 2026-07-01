<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Persetujuan Jurnal Siswa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ===== FORM FILTER ===== --}}
                <form method="GET" action="{{ route('instruktur.jurnal.index') }}" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="w-full md:w-56">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select name="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Status --</option>
                                <option value="disetujui" @selected(request('status') === 'disetujui')>Sudah Disetujui</option>
                                <option value="revisi"    @selected(request('status') === 'revisi')>Revisi</option>
                                <option value="pending"   @selected(request('status') === 'pending')>Menunggu</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md transition duration-200">
                                Cari
                            </button>
                            <a href="{{ route('instruktur.jurnal.index') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-md transition duration-200 inline-block text-center">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ===== TABEL JURNAL ===== --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-3 text-center w-12">No</th>
                                <th class="border p-3">Nama Siswa</th>
                                <th class="border p-3">NISN</th>
                                <th class="border p-3">Tanggal & Unit Kerja</th>
                                <th class="border p-3 w-1/3">Deskripsi Pekerjaan</th>
                                <th class="border p-3">Foto</th>
                                <th class="border p-3 text-center">Tindakan Persetujuan</th>
                                <th class="border p-3 text-center">Cetak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurnals as $jurnal)
                            <tr class="hover:bg-gray-50 {{ $jurnal->status_persetujuan === 'disetujui' ? 'bg-green-50/60' : ($jurnal->status_persetujuan === 'revisi' ? 'bg-yellow-50/60' : '') }} transition">
                                <td class="border p-3 text-center text-gray-500">{{ $jurnals->firstItem() + $loop->index }}</td>
                                <td class="border p-3 font-bold text-gray-900">{{ $jurnal->siswa->name ?? '-' }}</td>
                                <td class="border p-3 text-sm whitespace-nowrap text-gray-600">{{ $jurnal->siswa->nisn ?? '-' }}</td>
                                <td class="border p-3 text-sm text-gray-600">
                                    {{ \Carbon\Carbon::parse($jurnal->hari_tanggal)->translatedFormat('d M Y') }} <br>
                                    <span class="text-gray-500 text-xs">{{ $jurnal->unit_kerja }}</span>
                                </td>
                                <td class="border p-3 text-sm text-gray-600">{{ $jurnal->deskripsi_pekerjaan }}</td>
                                <td class="border p-3 text-center">
                                    @if($jurnal->dokumentasi)
                                        <a href="{{ asset('storage/' . $jurnal->dokumentasi) }}" target="_blank" class="text-blue-500 underline text-sm hover:text-blue-700">Lihat</a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="border p-3">
                                    <form action="{{ route('instruktur.jurnal.update', $jurnal->id) }}" method="POST" class="flex flex-col gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="status_persetujuan" class="border-gray-300 rounded text-sm w-full">
                                            <option value="pending"   @selected($jurnal->status_persetujuan === 'pending')>Menunggu</option>
                                            <option value="disetujui" @selected($jurnal->status_persetujuan === 'disetujui')>Setujui</option>
                                            <option value="revisi"    @selected($jurnal->status_persetujuan === 'revisi')>Revisi</option>
                                        </select>
                                        <textarea name="catatan_instruktur" rows="2" placeholder="Catatan/Feedback..." class="border-gray-300 rounded text-sm w-full">{{ $jurnal->catatan_instruktur }}</textarea>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-1 px-2 rounded text-sm transition duration-200">Simpan</button>
                                    </form>
                                </td>
                                <td class="border p-3 text-center">
                                    <a href="{{ route('cetak.jurnal', $jurnal->siswa_id) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white text-xs py-1 px-2 rounded transition duration-200 inline-block">PDF</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="border p-4 text-center text-gray-400 italic">Belum ada jurnal dari siswa bimbingan Anda.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {!! $jurnals->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
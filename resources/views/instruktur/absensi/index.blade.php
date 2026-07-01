<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Input Absensi Siswa
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
                <form action="{{ route('instruktur.absensi.index') }}" method="GET" class="mb-6">
                    <div class="flex flex-col md:flex-row gap-3 md:items-end">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Absen</label>
                            <input type="date" name="tanggal" value="{{ $tanggal }}"
                                   class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cari (Nama / NISN)</label>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status Kehadiran</label>
                            <select name="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Status --</option>
                                <option value="Hadir" @selected(request('status') === 'Hadir')>Hadir</option>
                                <option value="Izin"  @selected(request('status') === 'Izin')>Izin</option>
                                <option value="Sakit" @selected(request('status') === 'Sakit')>Sakit</option>
                                <option value="Alpha" @selected(request('status') === 'Alpha')>Alpha</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-gray-600 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-md transition duration-200">Tampilkan</button>
                            <a href="{{ route('instruktur.absensi.index') }}"
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-md inline-block text-center transition duration-200">Reset</a>
                        </div>
                    </div>
                </form>

                <hr class="mb-6">

                {{-- ===== FORM SIMPAN ABSENSI ===== --}}
                <form action="{{ route('instruktur.absensi.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100 text-xs uppercase text-gray-700">
                                    <th class="border p-3 text-center w-12">No</th>
                                    <th class="border p-3">Nama Siswa</th>
                                    <th class="border p-3">NISN</th>
                                    <th class="border p-3">Status Kehadiran</th>
                                    <th class="border p-3 text-center w-40">Jam Masuk</th>
                                    <th class="border p-3 text-center w-40">Jam Pulang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($siswas as $siswa)
                                    @php
                                        // Cari data absen siswa ini di tanggal yang dipilih (jika sudah ada)
                                        $absen = $absensis->get($siswa->id);
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="border p-3 text-center text-gray-500">{{ $siswas->firstItem() + $loop->index }}</td>
                                        <td class="border p-3 font-bold text-gray-900">{{ $siswa->name }}</td>
                                        <td class="border p-3 text-sm text-gray-600 whitespace-nowrap">{{ $siswa->nisn ?? '-' }}</td>
                                        <td class="border p-3">
                                            <select name="absensi[{{ $siswa->id }}][status]" class="border-gray-300 rounded text-sm w-full">
                                                <option value="Hadir" @selected(optional($absen)->status === 'Hadir')>Hadir</option>
                                                <option value="Izin"  @selected(optional($absen)->status === 'Izin')>Izin</option>
                                                <option value="Sakit" @selected(optional($absen)->status === 'Sakit')>Sakit</option>
                                                <option value="Alpha" @selected(optional($absen)->status === 'Alpha')>Alpha (Tanpa Keterangan)</option>
                                            </select>
                                        </td>
                                        <td class="border p-3 text-center">
                                            <input type="time" name="absensi[{{ $siswa->id }}][jam_masuk]" value="{{ optional($absen)->jam_masuk }}" class="border-gray-300 rounded text-sm w-32 text-center">
                                        </td>
                                        <td class="border p-3 text-center">
                                            <input type="time" name="absensi[{{ $siswa->id }}][jam_pulang]" value="{{ optional($absen)->jam_pulang }}" class="border-gray-300 rounded text-sm w-32 text-center">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="border p-4 text-center text-gray-400 italic">Belum ada siswa bimbingan yang di-mapping ke Anda / tidak cocok dengan pencarian.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($siswas->count() > 0)
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-6 rounded text-sm shadow-lg transition duration-200">
                                Simpan Absensi
                            </button>
                        </div>
                    @endif
                </form>

                {{-- ===== PAGINATION ===== --}}
                <div class="mt-4">
                    {!! $siswas->links() !!}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
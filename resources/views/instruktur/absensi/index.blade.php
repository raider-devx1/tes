<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Hadir Siswa PKL') }}
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

                <form action="{{ route('instruktur.absensi.index') }}" method="GET" class="mb-6 flex items-center gap-4">
                    <label class="font-bold text-gray-700">Pilih Tanggal Absen:</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="border-gray-300 rounded shadow-sm">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded">Tampilkan Data</button>
                </form>

                <hr class="mb-6">

                <form action="{{ route('instruktur.absensi.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse border border-gray-200">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border p-3">Nama Siswa</th>
                                    <th class="border p-3">Status Kehadiran</th>
                                    <th class="border p-3 text-center">Jam Masuk</th>
                                    <th class="border p-3 text-center">Jam Pulang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($siswas as $siswa)
                                    @php 
                                        // Cari data absen siswa ini di tanggal yang dipilih (jika sudah ada)
                                        $absen = $absensis->get($siswa->id); 
                                    @endphp
                                <tr>
                                    <td class="border p-3 font-bold">{{ $siswa->name }}</td>
                                    <td class="border p-3">
                                        <select name="absensi[{{ $siswa->id }}][status]" class="border-gray-300 rounded w-full">
                                            <option value="Hadir" {{ ($absen->status ?? '') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="Izin" {{ ($absen->status ?? '') == 'Izin' ? 'selected' : '' }}>Izin</option>
                                            <option value="Sakit" {{ ($absen->status ?? '') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                            <option value="Alpha" {{ ($absen->status ?? '') == 'Alpha' ? 'selected' : '' }}>Alpha (Tanpa Keterangan)</option>
                                        </select>
                                    </td>
                                    <td class="border p-3 text-center">
                                        <input type="time" name="absensi[{{ $siswa->id }}][jam_masuk]" value="{{ $absen->jam_masuk ?? '' }}" class="border-gray-300 rounded w-32">
                                    </td>
                                    <td class="border p-3 text-center">
                                        <input type="time" name="absensi[{{ $siswa->id }}][jam_pulang]" value="{{ $absen->jam_pulang ?? '' }}" class="border-gray-300 rounded w-32">
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="border p-4 text-center text-gray-500">Belum ada siswa bimbingan yang di-mapping ke Anda.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($siswas->count() > 0)
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-6 rounded text-lg shadow-lg">
                                Simpan Absensi
                            </button>
                        </div>
                    @endif
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
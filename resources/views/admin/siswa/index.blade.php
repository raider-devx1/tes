<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pemetaan Siswa PKL') }}
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

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border-b p-3">Nama Siswa</th>
                                <th class="border-b p-3">Kelas & Jurusan</th>
                                <th class="border-b p-3">Penempatan (Industri, Instruktur, Guru)</th>
                                <th class="border-b p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($siswas as $siswa)
                            <tr class="border-b hover:bg-gray-50">
                                <form action="{{ route('admin.siswa.mapping', $siswa->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <td class="p-3 font-semibold">{{ $siswa->name }}</td>
                                    
                                    <td class="p-3">
                                        <input type="text" name="kelas" value="{{ $siswa->kelas }}" placeholder="Kelas (ex: XI)" class="border rounded p-1 w-full text-sm mb-1">
                                        <input type="text" name="jurusan" value="{{ $siswa->jurusan }}" placeholder="Jurusan (ex: TKJ)" class="border rounded p-1 w-full text-sm">
                                    </td>
                                    
                                    <td class="p-3">
                                        <select name="perusahaan_id" class="border rounded p-1 w-full text-sm mb-1">
                                            <option value="">-- Pilih Tempat Industri --</option>
                                            @foreach($perusahaans as $p)
                                                <option value="{{ $p->id }}" {{ $siswa->perusahaan_id == $p->id ? 'selected' : '' }}>{{ $p->nama_perusahaan }}</option>
                                            @endforeach
                                        </select>

                                        <select name="instruktur_id" class="border rounded p-1 w-full text-sm mb-1">
                                            <option value="">-- Pilih Instruktur Industri --</option>
                                            @foreach($instrukturs as $inst)
                                                <option value="{{ $inst->id }}" {{ $siswa->instruktur_id == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                                            @endforeach
                                        </select>

                                        <select name="guru_id" class="border rounded p-1 w-full text-sm">
                                            <option value="">-- Pilih Guru Pembimbing --</option>
                                            @foreach($gurus as $guru)
                                                <option value="{{ $guru->id }}" {{ $siswa->guru_id == $guru->id ? 'selected' : '' }}>{{ $guru->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    
                                    <td class="p-3 text-center">
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-1 px-4 rounded text-sm w-full mb-2">
                                            Simpan Mapping
                                        </button>
                                        <a href="{{ route('cetak.jurnal', $siswa->id) }}" target="_blank" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-4 rounded text-sm w-full inline-block">
                                            Test Cetak PDF
                                        </a>
                                    </td>
                                </form>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
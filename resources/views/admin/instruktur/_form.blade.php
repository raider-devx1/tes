@php $data = $perusahaan ?? null; @endphp

@if ($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-6">
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">🏢 Data Industri / Tempat PKL</h3>
        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan</label>
                    <input type="text" name="nama_perusahaan" value="{{ old('nama_perusahaan', $data?->nama_perusahaan) }}" required
                           class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pembimbing / Instruktur Industri</label>
                    <input type="text" name="pembimbing_industri" value="{{ old('pembimbing_industri', $data?->pembimbing_industri) }}" placeholder="cth: Budi Santoso"
                           class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepon Industri</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $data?->telepon) }}"
                           class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <input type="text" name="alamat" value="{{ old('alamat', $data?->alamat) }}" required
                           class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
                </div>
            </div>
        </div>
    </div>
</div>

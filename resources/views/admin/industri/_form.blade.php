@php 
    $i = $industri ?? null; 
@endphp

@if ($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-600 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan</label>
        <input type="text" name="nama_perusahaan" value="{{ old('nama_perusahaan', $i->nama_perusahaan ?? '') }}" required
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bidang Usaha</label>
            <input type="text" name="bidang_usaha" value="{{ old('bidang_usaha', $i->bidang_usaha ?? '') }}" placeholder="cth: Telekomunikasi"
                   class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kuota Siswa</label>
            <input type="number" name="kuota" value="{{ old('kuota', $i->kuota ?? 0) }}" min="0" required
                   class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        </div>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
        <textarea name="alamat" rows="2" required
                  class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">{{ old('alamat', $i->alamat ?? '') }}</textarea>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
            <input type="text" name="telepon" value="{{ old('telepon', $i->telepon ?? '') }}"
                   class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $i->email ?? '') }}"
                   class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        </div>
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pembimbing Industri</label>
        <input type="text" name="pembimbing_industri" value="{{ old('pembimbing_industri', $i->pembimbing_industri ?? '') }}"
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
    </div>
</div>
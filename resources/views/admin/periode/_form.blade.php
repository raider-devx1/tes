@php $p = $periode ?? null; @endphp

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
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Periode</label>
        <input type="text" name="nama" value="{{ old('nama', $p->nama ?? '') }}" required
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran</label>
        <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', $p->tahun_ajaran ?? '') }}" placeholder="2025/2026" required
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $p && $p->tanggal_mulai ? $p->tanggal_mulai->format('Y-m-d') : '') }}" required
                   class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $p && $p->tanggal_selesai ? $p->tanggal_selesai->format('Y-m-d') : '') }}" required
                   class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
        <textarea name="keterangan" rows="3"
                  class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">{{ old('keterangan', $p->keterangan ?? '') }}</textarea>
    </div>
    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $p->is_active ?? false) ? 'checked' : '' }}
               class="rounded border-blue-200 text-[#2563EB] focus:ring-[#2563EB]">
        Jadikan periode aktif (periode lain otomatis nonaktif)
    </label>
    
</div>
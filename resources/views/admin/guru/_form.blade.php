@php $isEdit = $guru->exists; @endphp

<div class="space-y-4">
    <!-- Nama -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="{{ old('name', $guru->name) }}" required
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- NIP (identitas login guru) -->
    <div>
        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">
            NIP <span class="text-gray-400 font-normal">(dipakai untuk login)</span>
        </label>
        <input type="text" id="nip" name="nip" value="{{ old('nip', $guru->nip) }}" required
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        @error('nip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- No. HP -->
    <div>
        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
        <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $guru->no_hp) }}"
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        @error('no_hp') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Password -->
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Password
            @if($isEdit)<span class="text-gray-400 font-normal">(kosongkan bila tidak diganti)</span>@endif
        </label>
        <input type="password" id="password" name="password" {{ $isEdit ? '' : 'required' }} autocomplete="new-password"
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <!-- Konfirmasi Password -->
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
    </div>
</div>
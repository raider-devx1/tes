<div class="grid grid-cols-1 gap-5">

    {{-- Nama Lengkap --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
        <input type="text" id="name" name="name"
               value="{{ old('name', $admin->name ?? '') }}" required
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        @error('name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- NIP (dipakai untuk login admin, sama seperti guru) --}}
    <div>
        <label for="nip" class="block text-sm font-medium text-gray-700 mb-1">
            NIP <span class="text-gray-400">(dipakai untuk login)</span>
        </label>
        <input type="text" id="nip" name="nip"
               value="{{ old('nip', $admin->nip ?? '') }}" required
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        @error('nip')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Password --}}
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
            Password
            @if(isset($admin) && $admin->exists)
                <span class="text-gray-400">(kosongkan bila tidak ingin mengubah)</span>
            @endif
        </label>
        <input type="password" id="password" name="password"
               autocomplete="new-password"
               {{ (isset($admin) && $admin->exists) ? '' : 'required' }}
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        @error('password')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Konfirmasi Password --}}
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               autocomplete="new-password"
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
    </div>

</div>
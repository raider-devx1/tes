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

    {{-- Email (dipakai untuk login admin) --}}
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            Email <span class="text-gray-400">(dipakai untuk login)</span>
        </label>
        <input type="email" id="email" name="email"
               value="{{ old('email', $admin->email ?? '') }}" required
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        @error('email')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- No HP --}}
    <div>
        <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1">
            No. HP <span class="text-gray-400">(opsional)</span>
        </label>
        <input type="text" id="no_hp" name="no_hp"
               value="{{ old('no_hp', $admin->no_hp ?? '') }}"
               class="w-full rounded-lg border-blue-100 focus:border-[#2563EB] focus:ring-[#2563EB] text-sm">
        @error('no_hp')
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
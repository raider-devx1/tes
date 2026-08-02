<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ===== PERINGATAN / PEMBERITAHUAN HASIL PERUBAHAN ===== --}}
            @if (session('profil_pesan'))
                @php $sukses = (bool) session('profil_pesan_sukses', true); @endphp
                <div x-data="{ tampil: true }" x-show="tampil" x-transition x-cloak
                     x-init="setTimeout(() => tampil = false, 8000)"
                     class="flex items-start gap-3 rounded-xl border px-4 py-3 shadow-sm
                            {{ $sukses ? 'border-green-200 bg-green-50 text-green-800' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                    @if ($sukses)
                        <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    @else
                        <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    @endif

                    <p class="text-sm font-semibold leading-6">{{ session('profil_pesan') }}</p>

                    <button type="button" @click="tampil = false"
                            class="ml-auto shrink-0 rounded-lg p-1 opacity-60 hover:opacity-100 focus:outline-none"
                            aria-label="Tutup pemberitahuan">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif

            <!-- ===== FORM UPDATE INFORMASI PROFIL ===== -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- ===== FORM UBAH PASSWORD ===== -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Form "Hapus Akun" sengaja dihapus untuk semua peran -->

        </div>
    </div>
</x-app-layout>
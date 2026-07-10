@php $role = auth()->user()->role; @endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
             {{ __('Profile Information') }} 
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            @if(in_array($role, ['instruktur_industri', 'admin'], true))
                Perbarui informasi nama, email, dan foto akun Anda.
            @else
                Perbarui nama dan foto akun Anda.
            @endif
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6"
          enctype="multipart/form-data"
          x-data="{ fotoPreview: '{{ $user->foto ? asset('storage/' . $user->foto) : '' }}' }">
        @csrf
        @method('patch')

        <div>
            <x-input-label :value="__('Foto Profil')" />
            <div class="mt-2 flex items-center gap-4">
                <template x-if="fotoPreview">
                    <img :src="fotoPreview" alt="Foto profil"
                         class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-100">
                </template>
                <template x-if="!fotoPreview">
                    <span class="h-20 w-20 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold">
                         {{ strtoupper(substr($user->name, 0, 1)) }} 
                    </span>
                </template>

                <div>
                    <input type="file" name="foto" accept="image/*"
                           @change="const f = $event.target.files[0]; if (f) fotoPreview = URL.createObjectURL(f);"
                           class="block w-full text-sm text-gray-600
                                  file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                  file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100 cursor-pointer">
                    <p class="mt-1 text-xs text-gray-500">Format JPG, JPEG, atau PNG. Maksimal 2MB.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('foto')" />
                </div>
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        @if(in_array($role, ['instruktur_industri', 'admin'], true))
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800">
                             {{ __('Your email address is unverified.') }} 

                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                 {{ __('Click here to re-send the verification email.') }} 
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                 {{ __('A new verification link has been sent to your email address.') }} 
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button> {{ __('Save') }} </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                > {{ __('Saved.') }} </p>
            @endif
        </div>
    </form>
</section>
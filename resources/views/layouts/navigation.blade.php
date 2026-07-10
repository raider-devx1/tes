<nav x-data="{ open: false }" x-init="window.addEventListener('resize', () => { if (window.innerWidth >= 640) open = false })">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        <span class="font-bold text-slate-800 text-base tracking-tight">
                            LMS <span class="text-blue-600 font-extrabold">PKL</span>
                        </span>
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        Dashboard
                    </x-nav-link>

                    <x-nav-link :href="route('informasi.index')" :active="request()->routeIs('informasi.index')" class="gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        Informasi PKL
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2.5 p-1.5 pr-3 rounded-xl text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition ease-in-out duration-150">
                            @if(auth()->user()->foto)
                                <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="Foto profil"
                                     class="w-7 h-7 rounded-lg object-cover shadow-sm">
                            @else
                                <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-sm shadow-blue-200">
                                     {{ strtoupper(substr(auth()->user()->name, 0, 1)) }} 
                                </span>
                            @endif
                            <div> {{ auth()->user()->name }} </div>

                            <div class="ms-0.5 text-slate-400">
                                <svg class="fill-current h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-1 py-1">
                            <x-dropdown-link :href="route('profile.edit')" class="rounded-lg text-slate-600 hover:bg-slate-50 hover:text-slate-900 py-2">
                                 {{ __('Profile') }} 
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        class="rounded-lg text-blue-600 font-medium hover:bg-blue-50/50 py-2"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                     {{ __('Log Out') }} 
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-600 hover:bg-slate-50 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="sm:hidden border-t border-slate-100 bg-white">
        <div class="pt-2 pb-3 space-y-1 px-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('informasi.index')" :active="request()->routeIs('informasi.index')">
                Informasi PKL
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-3 border-t border-slate-100 px-3">
            <div class="px-3 flex items-center gap-3 mb-3">
                @if(auth()->user()->foto)
                    <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="Foto profil"
                         class="w-8 h-8 rounded-lg object-cover shadow-sm">
                @else
                    <span class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shadow-blue-200">
                         {{ strtoupper(substr(auth()->user()->name, 0, 1)) }} 
                    </span>
                @endif
                <div>
                    <div class="font-semibold text-sm text-slate-800"> {{ auth()->user()->name }} </div>
                    <div class="font-medium text-xs text-slate-400"> {{ auth()->user()->email }} </div>
                </div>
            </div>

            <div class="space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-slate-600 hover:bg-slate-50">
                     {{ __('Profile') }} 
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            class="text-blue-600 hover:bg-blue-50/50"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                         {{ __('Log Out') }} 
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
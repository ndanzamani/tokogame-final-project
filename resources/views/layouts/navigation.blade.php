<nav x-data="{ open: false }" class="bg-[#171a21] border-b border-black text-white font-sans shadow-lg">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-24"> {{-- Tinggi navbar diperbesar ala Steam --}}
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('store.index') }}">
                        {{-- Ganti dengan Logo Text atau Gambar Anda --}}
                        <h1 class="text-2xl font-black uppercase tracking-[0.2em] text-white">
                            STEAM<span class="text-[#66c0f4]">CLONE</span>
                        </h1>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    
                    {{-- STORE LINK --}}
                    <x-nav-link :href="route('store.index')" :active="request()->routeIs('store.index')" class="text-gray-300 hover:text-[#66c0f4] text-sm font-bold uppercase tracking-wider">
                        {{ __('Store') }}
                    </x-nav-link>

                    {{-- LIBRARY LINK --}}
                    <x-nav-link :href="route('library.index')" :active="request()->routeIs('library.index')" class="text-gray-300 hover:text-[#66c0f4] text-sm font-bold uppercase tracking-wider">
                        {{ __('Library') }}
                    </x-nav-link>

                    {{-- DASHBOARD LINK --}}
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-300 hover:text-[#66c0f4] text-sm font-bold uppercase tracking-wider">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- ADMIN PANEL LINK (HANYA MUNCUL JIKA ADMIN) --}}
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="text-red-400 hover:text-red-300 text-sm font-black uppercase tracking-wider border-b-2 border-transparent hover:border-red-500">
                            {{ __('⚠️ Admin Panel') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-[#66c0f4] bg-[#2a475e] hover:bg-[#3d5d7a] focus:outline-none transition ease-in-out duration-150 shadow-md">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Styling Dropdown Content --}}
                        <div class="bg-[#171a21] border border-gray-700 text-gray-300">
                            <x-dropdown-link :href="route('profile.edit')" class="hover:bg-[#2a475e] hover:text-white">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();" class="hover:bg-[#2a475e] hover:text-white">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:bg-gray-700 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-[#171a21] border-t border-gray-700">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('store.index')" :active="request()->routeIs('store.index')" class="text-gray-300 hover:bg-[#2a475e] hover:text-white">
                {{ __('Store') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('library.index')" :active="request()->routeIs('library.index')" class="text-gray-300 hover:bg-[#2a475e] hover:text-white">
                {{ __('Library') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-300 hover:bg-[#2a475e] hover:text-white">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            @if(auth()->check() && auth()->user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="text-red-400 hover:bg-red-900 hover:text-white">
                    {{ __('⚠️ Admin Panel') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-700">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-gray-300 hover:bg-[#2a475e] hover:text-white">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();" class="text-gray-300 hover:bg-[#2a475e] hover:text-white">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
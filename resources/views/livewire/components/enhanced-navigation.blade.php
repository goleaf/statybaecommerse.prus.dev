<nav class="bg-white shadow-lg dark:bg-gray-900" x-data="{ mobileOpen: @entangle('mobileMenuOpen'), searchOpen: @entangle('searchOpen') }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            {{-- Logo and Main Navigation --}}
            <div class="flex">
                <div class="flex flex-shrink-0 items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <img class="h-8 w-auto" src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}">
                        <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">{{ config('app.name') }}</span>
                    </a>
                </div>
                
                {{-- Desktop Navigation --}}
                <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                    <a href="{{ route('home') }}" 
                       class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200">
                        {{ __('nav_home') }}
                    </a>
                    
                    {{-- Categories Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200">
                            {{ __('nav_categories') }}
                            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute left-0 z-50 mt-3 w-screen max-w-md transform px-2 sm:px-0">
                            <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                                <div class="relative grid gap-6 bg-white px-5 py-6 dark:bg-gray-800 sm:gap-8 sm:p-8">
                                    @foreach($this->mainCategories as $category)
                                        <a href="{{ route('categories.show', $category) }}" 
                                           class="-m-3 flex items-start rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                            @if($category->getFirstMediaUrl('images'))
                                                <img src="{{ $category->getFirstMediaUrl('images') }}" 
                                                     alt="{{ $category->name }}" 
                                                     class="h-10 w-10 flex-shrink-0 rounded-lg object-cover">
                                            @endif
                                            <div class="ml-4">
                                                <p class="text-base font-medium text-gray-900 dark:text-white">{{ $category->name }}</p>
                                                @if($category->description)
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $category->description }}</p>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="bg-gray-50 px-5 py-5 dark:bg-gray-700 sm:px-8 sm:py-8">
                                    <div>
                                        <h3 class="text-base font-medium text-gray-500 dark:text-gray-400">{{ __('Popular Categories') }}</h3>
                                        <ul role="list" class="mt-4 space-y-4">
                                            @foreach($this->mainCategories->take(3) as $category)
                                                <li class="truncate text-base">
                                                    <a href="{{ route('categories.show', $category) }}" 
                                                       class="font-medium text-gray-900 hover:text-gray-700 dark:text-white dark:hover:text-gray-300 transition-colors duration-200">
                                                        {{ $category->name }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="mt-5 text-sm">
                                        <a href="{{ route('categories.index') }}" 
                                           class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200">
                                            {{ __('View all categories') }}
                                            <span aria-hidden="true"> →</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('brands.index') }}" 
                       class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200">
                        {{ __('nav_brands') }}
                    </a>
                    
                    <a href="{{ route('collections.index') }}" 
                       class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium transition-colors duration-200">
                        {{ __('nav_collections') }}
                    </a>
                </div>
            </div>

            {{-- Right Side Actions with Filament Components --}}
            <div class="flex items-center space-x-4">
                {{-- Search Toggle with Filament Icon Button --}}
                <x-filament::icon-button 
                    @click="searchOpen = !searchOpen"
                    icon="heroicon-o-magnifying-glass"
                    color="gray"
                    tooltip="{{ __('search_toggle') }}"
                    class="transition-all duration-200"
                />

                {{-- Language Switcher --}}
                <livewire:shared.language-switcher />

                {{-- Currency Selector --}}
                <livewire:shared.currency-selector />

                {{-- Cart --}}
                <livewire:shared.shopping-cart />

                {{-- User Menu --}}
                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center rounded-full bg-gray-100 p-2 text-gray-600 hover:bg-gray-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all duration-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800">
                            <a href="{{ route('account.profile') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200">
                                {{ __('account_profile') }}
                            </a>
                            <a href="{{ route('account.orders') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200">
                                {{ __('account_orders') }}
                            </a>
                            <a href="{{ route('account.addresses') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200">
                                {{ __('account_addresses') }}
                            </a>
                            <livewire:actions.logout />
                        </div>
                    </div>
                @else
                    <a 
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-sm hover:shadow-md transition-all duration-200"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ __('auth_login') }}
                    </a>
                @endauth

                {{-- Mobile Menu Toggle --}}
                <button @click="mobileOpen = !mobileOpen" 
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white sm:hidden transition-all duration-200">
                    <svg class="h-6 w-6" :class="{'hidden': mobileOpen, 'block': !mobileOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg class="h-6 w-6" :class="{'block': mobileOpen, 'hidden': !mobileOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Enhanced Search Overlay with Filament Components --}}
    <div x-show="searchOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-x-0 top-full z-50 bg-white shadow-lg dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('search_products') }}</h2>
                    </div>
                </div>
                
                <form wire:submit="search" class="flex gap-4">
                    <input 
                        wire:model="searchQuery"
                        x-ref="searchInput"
                        type="search" 
                        placeholder="{{ __('search_placeholder') }}"
                        class="flex-1 rounded-lg border-gray-300 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                        @focus-search.window="$refs.searchInput.focus()"
                    />
                    <button 
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ __('Search') }}
                    </button>
                    <button 
                        type="button"
                        @click="searchOpen = false"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('btn_cancel') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="sm:hidden">
        <div class="space-y-1 border-t border-gray-200 pb-3 pt-2 dark:border-gray-700">
            <a href="{{ route('home') }}" 
               class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all duration-200">
                {{ __('nav_home') }}
            </a>
            <a href="{{ route('categories.index') }}" 
               class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all duration-200">
                {{ __('nav_categories') }}
            </a>
            <a href="{{ route('brands.index') }}" 
               class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all duration-200">
                {{ __('nav_brands') }}
            </a>
            <a href="{{ route('collections.index') }}" 
               class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white transition-all duration-200">
                {{ __('nav_collections') }}
            </a>
            
            {{-- Mobile Search --}}
            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                <form wire:submit="search" class="px-4">
                    <div class="flex gap-2">
                        <input 
                            wire:model="searchQuery"
                            type="search" 
                            placeholder="{{ __('search_placeholder') }}"
                            class="flex-1 rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <button 
                            type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors duration-200"
                        >
                            {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</nav>

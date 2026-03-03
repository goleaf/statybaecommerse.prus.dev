@php
    // Ensure locale is set before using translations
    $request = request();
    $supportedConfig = config('app.supported_locales', 'lt,en');
    $navSupportedLocales = [];
    if (is_array($supportedConfig)) {
        $navSupportedLocales = array_filter($supportedConfig, fn($locale): bool => is_string($locale) && $locale !== '');
    } elseif (is_string($supportedConfig)) {
        $navSupportedLocales = array_filter(
            array_map(fn(string $locale): string => trim($locale), explode(',', $supportedConfig)),
            fn(string $locale): bool => $locale !== ''
        );
    }
    $navSupportedLocales = array_values(array_map(fn(string $locale): string => trim($locale), $navSupportedLocales));

    $routeLocale = $request->route('locale');
    $queryLocale = $request->query('locale');
    $defaultLocale = config('app.locale', 'lt');

    $candidateLocales = array_values(array_filter([
        $routeLocale,
        $queryLocale,
        session('locale'),
        session('app.locale'),
        $request->cookie('app_locale'),
        auth()->check() ? (auth()->user()->preferred_locale ?? null) : null,
    ], fn($candidate): bool => is_string($candidate) && $candidate !== ''));

    $locale = $defaultLocale;
    foreach ($candidateLocales as $candidate) {
        if (in_array($candidate, $navSupportedLocales, true)) {
            $locale = $candidate;
            break;
        }
    }

    if (!in_array($locale, $navSupportedLocales, true)) {
        $fallbackLocale = config('app.fallback_locale', $defaultLocale);
        $locale = in_array($fallbackLocale, $navSupportedLocales, true) ? $fallbackLocale : ($navSupportedLocales[0] ?? $defaultLocale);
    }

    app()->setLocale($locale);
@endphp
<nav class="w-full relative z-40 overflow-visible">
    @php
        $homeUrl = Route::has('localized.home')
            ? route('localized.home', ['locale' => $locale])
            : (Route::has('home')
                ? route('home')
                : url('/'));
        $newsTopbarUrl = Route::has('localized.news.index')
            ? route('localized.news.index', ['locale' => $locale])
            : (Route::has('frontend.news.index')
                ? route('frontend.news.index')
                : url('/news'));
        $quickLinks = collect([
            [
                'key' => 'categories',
                'label' => __('messages.nav_categories'),
                'url' => Route::has('localized.categories.index')
                    ? route('localized.categories.index', ['locale' => $locale])
                    : url('/' . $locale . '/categories'),
            ],
            [
                'key' => 'collections',
                'label' => __('messages.nav_collections'),
                'url' => Route::has('localized.collections.index')
                    ? route('localized.collections.index', ['locale' => $locale])
                    : url('/' . $locale . '/collections'),
            ],
            [
                'key' => 'brands',
                'label' => __('messages.nav_brands'),
                'url' => Route::has('localized.brands.index')
                    ? route('localized.brands.index', ['locale' => $locale])
                    : url('/' . $locale . '/brands'),
            ],
            [
                'key' => 'downloads',
                'label' => __('messages.nav_downloads'),
                'url' => Route::has('localized.brochures.index')
                    ? route('localized.brochures.index', ['locale' => $locale])
                    : url('/' . $locale . '/brochures'),
            ],
            // Vendor catalogs menu item disabled by request.
            // Support center menu item disabled by request.
            // [
            //     'key' => 'support',
            //     'label' => __('messages.nav_support_center'),
            //     'url' => 'mailto:eegidia@gmail.com',
            // ],
        ])->filter(fn($link) => !empty($link['url']));
    @endphp

    {{-- Top utility bar with contact info and social links --}}
    <section class="hidden text-dark sm:block border-b border-ash bg-sage">
        <div class="container mx-auto flex justify-between gap-10 h-9 px-5">
            <div class="flex items-center gap-5 sm:gap-8">
                <a href="tel:{{ __('frontend.header.topbar.phone_href') }}"
                    class="flex items-center group gap-2 hover:text-stone transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 6.75c0 2.138 1.912 3.75 3.75 3.75s3.75-1.612 3.75-3.75S8.838 3 6 3 2.25 4.612 2.25 6.75zM2.25 17.25c0 2.138 1.912 3.75 3.75 3.75S9.75 19.388 9.75 17.25 7.838 13.5 6 13.5s-3.75 1.612-3.75 3.75zM14.25 6.75c0 2.138 1.912 3.75 3.75 3.75s3.75-1.612 3.75-3.75S20.088 3 18.25 3s-3.75 1.612-3.75 3.75zM14.25 17.25c0 2.138 1.912 3.75 3.75 3.75s3.75-1.612 3.75-3.75-1.912-3.75-3.75-3.75-3.75 1.612-3.75 3.75z" />
                    </svg>
                    <span class="hidden sm:block">{{ __('frontend.header.topbar.phone') }}</span>
                </a>
                <a href="mailto:{{ __('frontend.header.topbar.email_href') }}"
                    class="flex items-center group gap-2 hover:text-stone transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="hidden sm:block">{{ __('frontend.header.topbar.email') }}</span>
                </a>
            </div>

            <div class="hidden md:flex items-center gap-5">
                <x-language-switcher class="!text-dark hover:!text-stone hover:!bg-transparent !p-0" />
                <a href="{{ $newsTopbarUrl }}" class="flex items-center group gap-2 hover:text-stone transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4zM8 9h8M8 13h5" />
                    </svg>
                    <span class="hidden sm:block">{{ __('frontend.header.topbar.news') }}</span>
                </a>
                <a href="#" class="flex items-center group gap-2 hover:text-stone transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 10h8M8 14h5M12 20l-3.5-2H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2h-2" />
                    </svg>
                    <span class="hidden sm:block">{{ __('frontend.header.topbar.consultations') }}</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Main header section --}}
    <div class="bg-sage transition-all duration-300 border-b border-ash">
        {{-- Primary header line - Logo and main navigation --}}
        <div class="h-20 flex items-center overflow-visible">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    {{-- Logo section - Made bigger --}}
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ $homeUrl }}" class="group" aria-label="{{ __('messages.nav_home') }}">
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
                                class="h-16 w-auto object-contain">
                        </a>
                    </div>

                    {{-- Navigation menu - hidden on mobile, visible on xl+ --}}
                    <nav class="hidden xl:flex space-x-10 text-sm">
                        @foreach ($quickLinks as $link)
                            <div class="header-link group relative cursor-pointer">
                                <a href="{{ $link['url'] }}" class="relative">
                                    <div class="relative">
                                        <span
                                            class="hidden sm:block font-semibold text-base text-dark hover:text-stone transition-colors">{{ $link['label'] }}</span>
                                        <span
                                            class="absolute inset-x-0 bottom-0 h-0.5 bg-dark transform scale-x-0 origin-left transition-transform group-hover:scale-x-100 duration-300"></span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </nav>

                    {{-- Right side actions --}}
                    <div class="flex items-center gap-5">
                        {{-- Shopping cart button --}}
                        <livewire:components.shopping-cart-button />

                        {{-- Guest actions (Login / Register) --}}
                        @guest
                            <div class="hidden sm:flex items-center gap-2">
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="header-action-button text-gray-700 hover:text-emerald-600 bg-transparent shadow-none border-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                        </svg>
                                        <span>{{ __('auth.ui.login.title') }}</span>
                                    </a>
                                @endif

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="header-action-button bg-emerald-600 text-white hover:bg-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.749 0-5.353-.62-7.499-1.632z" />
                                        </svg>
                                        <span>{{ __('messages.auth_register') }}</span>
                                    </a>
                                @endif
                            </div>
                        @endguest

                        {{-- Authenticated User Dropdown --}}
                        @auth
                            <div class="relative hidden sm:block" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
                                <div>
                                    <button type="button" @click="open = !open" class="header-action-button bg-emerald-50 text-emerald-700 hover:bg-emerald-100" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.749 0-5.353-.62-7.499-1.632z" />
                                        </svg>
                                        <span class="header-action-button__user-name max-w-[100px] truncate">{{ auth()->user()->name }}</span>
                                        <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </div>

                                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1" style="display: none;">
                                    @if(Route::has('account.index'))
                                        <a href="{{ route('account.index') }}" class="block px-4 py-2 text-sm @if(request()->routeIs('account.index')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-100 @endif" role="menuitem" tabindex="-1" id="user-menu-item-0">{{ __('frontend.account.navigation.dashboard') }}</a>
                                    @endif
                                    @if(Route::has('account.orders'))
                                        <a href="{{ route('account.orders') }}" class="block px-4 py-2 text-sm @if(request()->routeIs('account.orders*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-100 @endif" role="menuitem" tabindex="-1" id="user-menu-item-1">{{ __('frontend.account.navigation.orders') }}</a>
                                    @endif
                                    @if(Route::has('account.profile'))
                                        <a href="{{ route('account.profile') }}" class="block px-4 py-2 text-sm @if(request()->routeIs('account.profile*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-100 @endif" role="menuitem" tabindex="-1" id="user-menu-item-2">{{ __('frontend.account.navigation.profile') }}</a>
                                    @endif
                                    @if(Route::has('account.addresses'))
                                        <a href="{{ route('account.addresses') }}" class="block px-4 py-2 text-sm @if(request()->routeIs('account.addresses*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-100 @endif" role="menuitem" tabindex="-1" id="user-menu-item-3">{{ __('frontend.account.navigation.addresses') }}</a>
                                    @endif
                                    {{-- Notifications dropdown item disabled by request.
                                    <a href="https://egistatyba.test/account/notifications" class="block px-4 py-2 text-sm  text-gray-700 hover:bg-gray-100 " role="menuitem" tabindex="-1" id="user-menu-item-5">Pranešimai</a>
                                    --}}
                                    @if(Route::has('referrals.index'))
                                        {{-- Referrals dropdown item disabled by request.
                                        <a href="{{ route('referrals.index') }}" class="block px-4 py-2 text-sm @if(request()->routeIs('referrals.*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-100 @endif" role="menuitem" tabindex="-1" id="user-menu-item-6">{{ __('frontend.account.navigation.referrals') }}</a>
                                        --}}
                                    @endif
                                    <form method="POST" action="{{ route('logout') }}" x-data>
                                        @csrf
                                        <button type="submit" class="block w-full cursor-pointer text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem" tabindex="-1" id="user-menu-item-7">
                                            {{ __('frontend.account.navigation.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth

                        {{-- Mobile menu button - visible on xl- --}}
                        <div class="xl:hidden">
                            <button type="button" class="text-dark hover:text-stone focus:outline-none"
                                wire:click="toggleMobileMenu" aria-label="{{ __('messages.nav_toggle') }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary header line - Search bar --}}
        <div class="bg-sage border-t border-ash overflow-visible">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 relative overflow-visible">
                <div class="flex justify-center overflow-visible">
                    {{-- Enhanced search bar --}}
                    <div class="w-full max-w-2xl relative overflow-visible">
                        <x-search-module class="w-full" :max-results="10" :min-query-length="2" />
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Mobile menu overlay --}}
    @if ($mobileMenuOpen)
        <div
            class="fixed inset-0 z-[1200] lg:hidden"
            x-data="{ open: @entangle('mobileMenuOpen') }"
            x-show="open"
            x-cloak
            x-on:keydown.escape.window="open = false"
        >
            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-black/45 backdrop-blur-sm"
                x-on:click="open = false"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            ></div>

            {{-- Mobile menu panel --}}
            <div
                class="fixed inset-y-0 right-0 h-full w-[88vw] max-w-[360px] bg-sage shadow-2xl"
                x-show="open"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
            >
                {{-- Header --}}
                <div class="flex justify-between items-center py-3 px-4 bg-dark text-sage">
                    <h3 class="font-bold font-montserrat">{{ __('messages.frontend_header') }}</h3>
                    <button type="button"
                        class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-sage text-sage bg-transparent hover:bg-sage hover:text-dark transition-colors"
                        wire:click="toggleMobileMenu" aria-label="{{ __('messages.shared') }}">
                        <span class="sr-only">{{ __('messages.shared') }}</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Menu content --}}
                <div class="p-4 h-[calc(100dvh-60px)] flex flex-col justify-between gap-4 bg-sage">
                    <div class="space-y-3">
                        @foreach ($quickLinks as $link)
                            <a href="{{ $link['url'] }}"
                                class="w-full flex items-center gap-5 border-b border-gray-200 text-dark py-2 hover:text-stone transition-colors"
                                wire:click="toggleMobileMenu">
                                <p class="capitalize font-normal font-montserrat">{{ $link['label'] }}</p>
                            </a>
                        @endforeach

                        {{-- Mobile User/Auth Links --}}
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            @guest
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="w-full flex items-center gap-5 text-dark py-2 hover:text-stone transition-colors" wire:click="toggleMobileMenu">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                        <p class="capitalize font-normal font-montserrat">{{ __('auth.ui.login.title') }}</p>
                                    </a>
                                @endif
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="w-full flex items-center gap-5 text-emerald-600 py-2 hover:text-emerald-700 transition-colors" wire:click="toggleMobileMenu">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                        <p class="capitalize font-normal font-montserrat">{{ __('messages.auth_register') }}</p>
                                    </a>
                                @endif
                            @endguest

                            @auth
                                @if(Route::has('account.index'))
                                    <a href="{{ route('account.index') }}" class="w-full flex items-center gap-5 py-2 transition-colors @if(request()->routeIs('account.index')) text-stone font-semibold @else text-dark hover:text-stone @endif" wire:click="toggleMobileMenu">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        <p class="capitalize font-normal font-montserrat">{{ __('frontend.account.navigation.dashboard') }}</p>
                                    </a>
                                @endif
                                @if(Route::has('account.orders'))
                                    <a href="{{ route('account.orders') }}" class="w-full flex items-center gap-5 py-2 transition-colors @if(request()->routeIs('account.orders*')) text-stone font-semibold @else text-dark hover:text-stone @endif" wire:click="toggleMobileMenu">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/></svg>
                                        <p class="capitalize font-normal font-montserrat">{{ __('frontend.account.navigation.orders') }}</p>
                                    </a>
                                @endif
                                @if(Route::has('account.profile'))
                                    <a href="{{ route('account.profile') }}" class="w-full flex items-center gap-5 py-2 transition-colors @if(request()->routeIs('account.profile*')) text-stone font-semibold @else text-dark hover:text-stone @endif" wire:click="toggleMobileMenu">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <p class="capitalize font-normal font-montserrat">{{ __('frontend.account.navigation.profile') }}</p>
                                    </a>
                                @endif
                                @if(Route::has('account.addresses'))
                                    <a href="{{ route('account.addresses') }}" class="w-full flex items-center gap-5 py-2 transition-colors @if(request()->routeIs('account.addresses*')) text-stone font-semibold @else text-dark hover:text-stone @endif" wire:click="toggleMobileMenu">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <p class="capitalize font-normal font-montserrat">{{ __('frontend.account.navigation.addresses') }}</p>
                                    </a>
                                @endif
                                @if(Route::has('referrals.index'))
                                    <a href="{{ route('referrals.index') }}" class="w-full flex items-center gap-5 py-2 transition-colors @if(request()->routeIs('referrals.*')) text-stone font-semibold @else text-dark hover:text-stone @endif" wire:click="toggleMobileMenu">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                                        <p class="capitalize font-normal font-montserrat">{{ __('frontend.account.navigation.referrals') }}</p>
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full cursor-pointer flex items-center gap-5 text-red-600 py-2 hover:text-red-700 transition-colors" wire:click="toggleMobileMenu">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        <p class="capitalize font-normal font-montserrat">{{ __('frontend.account.navigation.logout') }}</p>
                                    </button>
                                </form>
                            @endauth
                        </div>

                        <div class="pt-4 mt-2 border-t border-gray-200">
                            <x-language-switcher class="w-full justify-between !text-dark hover:!bg-dark/5" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</nav>

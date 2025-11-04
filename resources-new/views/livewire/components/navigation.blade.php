<nav class="w-full relative z-50 overflow-visible">
    @php
        $locale = app()->getLocale();
        $homeUrl = Route::has('localized.home')
            ? route('localized.home', ['locale' => $locale])
            : (Route::has('home')
                ? route('home')
                : url('/'));
        $quickLinks = collect([
            [
                'key' => 'categories',
                'label' => __('nav_categories'),
                'url' => Route::has('localized.categories.index')
                    ? route('localized.categories.index', ['locale' => $locale])
                    : url('/' . $locale . '/categories'),
            ],
            [
                'key' => 'collections',
                'label' => __('nav_collections'),
                'url' => Route::has('localized.collections.index')
                    ? route('localized.collections.index', ['locale' => $locale])
                    : url('/' . $locale . '/collections'),
            ],
            [
                'key' => 'brands',
                'label' => __('nav_brands'),
                'url' => Route::has('localized.brands.index')
                    ? route('localized.brands.index', ['locale' => $locale])
                    : url('/' . $locale . '/brands'),
            ],
            [
                'key' => 'downloads',
                'label' => __('nav_downloads'),
                'url' => '#',
            ],
            [
                'key' => 'vendor_catalogs',
                'label' => __('nav_vendor_catalogs'),
                'url' => '#',
            ],
            [
                'key' => 'support',
                'label' => __('nav_support_center'),
                'url' => 'mailto:support@statybae.com',
            ],
        ])->filter(fn($link) => !empty($link['url']));

        $categoryFeature = config('app-features.features.category') ?? null;
        $featureEnabled =
            $categoryFeature instanceof \App\Support\FeatureState
                ? $categoryFeature === \App\Support\FeatureState::Enabled
                : (is_string($categoryFeature)
                    ? strtolower($categoryFeature) === strtolower(\App\Support\FeatureState::Enabled->value)
                    : (bool) $categoryFeature);
    @endphp

    {{-- Top utility bar with contact info and social links --}}
    <section class="text-dark border-b border-ash bg-sage">
        <div class="container mx-auto flex justify-between gap-10 h-9 px-5">
            <div class="flex items-center gap-5 sm:gap-8">
                <a href="tel:+37060000000" class="flex items-center group gap-2 hover:text-stone transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 2.138 1.912 3.75 3.75 3.75s3.75-1.612 3.75-3.75S8.838 3 6 3 2.25 4.612 2.25 6.75zM2.25 17.25c0 2.138 1.912 3.75 3.75 3.75S9.75 19.388 9.75 17.25 7.838 13.5 6 13.5s-3.75 1.612-3.75 3.75zM14.25 6.75c0 2.138 1.912 3.75 3.75 3.75s3.75-1.612 3.75-3.75S20.088 3 18.25 3s-3.75 1.612-3.75 3.75zM14.25 17.25c0 2.138 1.912 3.75 3.75 3.75s3.75-1.612 3.75-3.75-1.912-3.75-3.75-3.75-3.75 1.612-3.75 3.75z" />
                    </svg>
                    <span class="hidden sm:block">+370 600 00 000</span>
                </a>
                <a href="mailto:support@statybae.com" class="flex items-center group gap-2 hover:text-stone transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="hidden sm:block">support@statybae.com</span>
                </a>
                @if (Route::has('localized.locations.index'))
                    <a href="{{ route('localized.locations.index', ['locale' => $locale]) }}" class="flex items-center group gap-2 hover:text-stone transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 9c0 7.5-7.5 12-7.5 12S4.5 16.5 4.5 9a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span class="hidden sm:block">{{ __('Store locator') }}</span>
                    </a>
                @endif
            </div>

            <div class="hidden md:flex items-center gap-5">
                <a href="#" class="flex items-center group gap-2 hover:text-stone transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4zM8 9h8M8 13h5" />
                    </svg>
                    <span class="hidden sm:block">Naujienos</span>
                </a>
                <a href="#" class="flex items-center group gap-2 hover:text-stone transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5M12 20l-3.5-2H6a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2h-2" />
                    </svg>
                    <span class="hidden sm:block">Konsultacijos</span>
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
                        <a href="{{ $homeUrl }}" class="group" aria-label="{{ __('nav_home') }}">
                            <img src="/images/logo/logo.png" 
                                 alt="{{ config('app.name') }}" 
                                 class="h-16 w-auto object-contain">
                        </a>
                    </div>

                    {{-- Navigation menu - hidden on mobile, visible on xl+ --}}
                    <nav class="hidden xl:flex space-x-10 text-sm">
                        @foreach ($quickLinks as $link)
                            <div class="header-link group relative cursor-pointer">
                                <a href="{{ $link['url'] }}" class="relative">
                                    <div class="relative">
                                        <span class="hidden sm:block font-semibold text-base text-dark hover:text-stone transition-colors">{{ $link['label'] }}</span>
                                        <span class="absolute inset-x-0 bottom-0 h-0.5 bg-dark transform scale-x-0 origin-left transition-transform group-hover:scale-x-100 duration-300"></span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </nav>

                    {{-- Right side actions --}}
                    <div class="flex items-center gap-5">
                        {{-- Shopping cart button --}}
                        <livewire:components.shopping-cart-button />

                        {{-- Register button (guest only) --}}
                        @guest
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="header-action-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.749 0-5.353-.62-7.499-1.632z" />
                                    </svg>
                                    <span>{{ __('auth_register') }}</span>
                                </a>
                            @endif
                        @endguest

                        {{-- Mobile menu button - visible on xl- --}}
                        <div class="xl:hidden">
                            <button type="button"
                                    class="text-dark hover:text-stone focus:outline-none"
                                    wire:click="toggleMobileMenu"
                                    aria-label="{{ __('nav_toggle') }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-4 relative z-50 overflow-visible">
                <div class="flex justify-center overflow-visible">
                    {{-- Enhanced search bar --}}
                    <div class="w-full max-w-2xl relative z-60 overflow-visible">
                        <x-search-module
                                       class="w-full"
                                       :max-results="10"
                                       :min-query-length="2" />
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Mobile menu overlay --}}
    @if ($mobileMenuOpen)
        <div class="fixed inset-0 z-50 lg:hidden" x-data="{ open: @entangle('mobileMenuOpen') }" x-show="open" x-transition>
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/30" x-on:click="open = false"></div>
            
            {{-- Mobile menu panel --}}
            <div class="fixed top-0 right-0 h-full w-full max-w-[350px] bg-sage transform transition-transform duration-300" x-show="open">
                {{-- Header --}}
                <div class="flex justify-between items-center py-3 px-4 bg-dark text-sage">
                    <h3 class="font-bold font-montserrat">{{ __('Navigation') }}</h3>
                    <button type="button" 
                            class="size-8 inline-flex justify-center items-center gap-x-2 rounded-full border border-sage text-sage bg-transparent hover:bg-sage hover:text-dark transition-colors" 
                            wire:click="toggleMobileMenu"
                            aria-label="{{ __('Close') }}">
                        <span class="sr-only">{{ __('Close') }}</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                               class="w-full flex items-center gap-5 border-b border-dark text-dark py-2 hover:text-stone transition-colors"
                               wire:click="toggleMobileMenu">
                                <p class="capitalize font-normal font-montserrat">{{ $link['label'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Categories section (if enabled) --}}
    @if ($featureEnabled && isset($categories) && count($categories) && Route::has('localized.categories.show'))
        <div class="bg-white border-b border-ash">
            <div class="container mx-auto px-4 py-4">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-stone font-montserrat">{{ __('Highlighted categories') }}</span>
                    @if ($quickLinks->firstWhere('key', 'categories'))
                        <a href="{{ $quickLinks->firstWhere('key', 'categories')['url'] }}"
                           class="text-xs font-semibold text-dark hover:text-stone transition-colors">
                            {{ __('View all') }}
                        </a>
                    @endif
                </div>
                <div class="flex items-stretch gap-3 overflow-x-auto pb-2">
                    @foreach ($categories as $category)
                        @php
                            $slug = method_exists($category, 'trans')
                                ? $category->trans('slug') ?? $category->slug
                                : $category->slug;
                            $name = method_exists($category, 'trans')
                                ? $category->trans('name') ?? $category->name
                                : $category->name;
                        @endphp
                        <a href="{{ route('localized.categories.show', ['locale' => $locale, 'category' => $slug]) }}"
                           class="group relative flex min-w-[160px] flex-col justify-between rounded-xl border border-ash bg-sage p-4 shadow-sm transition hover:-translate-y-1 hover:border-dark hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-dark group-hover:text-stone transition-colors font-montserrat">{{ $name }}</span>
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-dark text-sm font-bold text-sage font-montserrat">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
                            </div>
                            <span class="mt-6 inline-flex items-center gap-2 text-xs font-medium text-stone group-hover:text-dark">
                                {{ __('Browse') }}
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</nav>
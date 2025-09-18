<div class="border-b border-slate-200 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/70 shadow-sm">
    @php
        $locale = app()->getLocale();
        $homeUrl = Route::has('localized.home')
            ? route('localized.home', ['locale' => $locale])
            : (Route::has('home') ? route('home') : url('/'));
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
                'label' => __('translations.collections') ?? __('Collections'),
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
        ])->filter(fn ($link) => !empty($link['url']));

        $categoryFeature = config('app-features.features.category') ?? null;
        $featureEnabled = $categoryFeature instanceof \App\Support\FeatureState
            ? $categoryFeature === \App\Support\FeatureState::Enabled
            : (is_string($categoryFeature)
                ? strtolower($categoryFeature) === strtolower(\App\Support\FeatureState::Enabled->value)
                : (bool) $categoryFeature);
    @endphp

    <div class="hidden lg:block bg-slate-900 text-slate-100">
        <x-container class="px-4">
            <div class="flex items-center justify-between gap-6 py-2 text-[13px]">
                <div class="flex items-center gap-6">
                    <span class="inline-flex items-center gap-2 font-semibold tracking-wide uppercase text-slate-200">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('Fast next-day delivery across Lithuania') }}
                    </span>
                    <span class="inline-flex items-center gap-2 text-slate-300">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 1.657-1.343 3-3 3s-3-1.343-3-3 1.343-3 3-3 3 1.343 3 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 01.4 1.09V18a2 2 0 01-2 2H6.2a2 2 0 01-2-2v-1.91A1.65 1.65 0 014.6 15c1.359-1.088 3.315-2 5.4-2s4.041.912 5.4 2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 8V5a1 1 0 00-1-1h-2" />
                        </svg>
                        {{ __('Secure payments & 30-day returns') }}
                    </span>
                </div>
                <div class="flex items-center gap-5 text-slate-300">
                    @if (Route::has('localized.locations.index'))
                        <a href="{{ route('localized.locations.index', ['locale' => $locale]) }}" class="inline-flex items-center gap-2 hover:text-white transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 9c0 7.5-7.5 12-7.5 12S4.5 16.5 4.5 9a7.5 7.5 0 1115 0z" />
                            </svg>
                            {{ __('Store locator') }}
                        </a>
                    @endif
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 2.138 1.912 3.75 3.75 3.75s3.75-1.612 3.75-3.75S8.838 3 6 3 2.25 4.612 2.25 6.75zM2.25 17.25c0 2.138 1.912 3.75 3.75 3.75S9.75 19.388 9.75 17.25 7.838 13.5 6 13.5s-3.75 1.612-3.75 3.75zM14.25 6.75c0 2.138 1.912 3.75 3.75 3.75s3.75-1.612 3.75-3.75S20.088 3 18.25 3s-3.75 1.612-3.75 3.75zM14.25 17.25c0 2.138 1.912 3.75 3.75 3.75s3.75-1.612 3.75-3.75-1.912-3.75-3.75-3.75-3.75 1.612-3.75 3.75z" />
                        </svg>
                        +370 600 00 000
                    </span>
                    <a href="mailto:support@statybae.com" class="inline-flex items-center gap-2 hover:text-white transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        support@statybae.com
                    </a>
                </div>
            </div>
        </x-container>
    </div>

    <x-container class="px-4">
        <div class="flex flex-col gap-4 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4 flex-1">
                <button type="button"
                        class="lg:hidden inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white p-2.5 text-slate-700 shadow-sm hover:border-blue-300 hover:text-blue-600 transition"
                        wire:click="toggleMobileMenu"
                        wire:confirm="{{ __('translations.confirm_toggle_mobile_menu') }}"
                        aria-label="{{ __('nav_toggle') }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <a href="{{ $homeUrl }}" class="group flex items-center gap-3" aria-label="{{ __('nav_home') }}">
                    <div class="relative">
                        <img src="{{ asset('images/hero.png') }}" alt="{{ config('app.name') }}"
                             class="h-11 w-11 rounded-2xl border border-slate-200 bg-white object-cover shadow-sm transition-all duration-300 group-hover:scale-105 group-hover:shadow-lg"
                             width="44" height="44" />
                        <span class="pointer-events-none absolute inset-0 rounded-2xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base font-semibold text-slate-900 group-hover:text-blue-600 transition-colors">{{ config('app.name') }}</span>
                        <span class="hidden text-xs font-medium tracking-wide text-slate-400 lg:inline">{{ __('Building supplies marketplace') }}</span>
                    </div>
                </a>
            </div>

            <div class="hidden w-full max-w-3xl flex-1 lg:block">
                <x-search-module
                    class="w-full"
                    :max-results="10"
                    :min-query-length="2"
                />
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden items-center gap-2 lg:flex">
                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['url'] }}"
                           class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-blue-300 hover:text-blue-600">
                            @switch($link['key'])
                                @case('categories')
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                    @break
                                @case('collections')
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16M8 3v4m0 10v4m8-18v4m0 10v4" />
                                    </svg>
                                    @break
                                @case('brands')
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h4l2 9h8l2-9h4" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 17a2 2 0 11-4 0" />
                                    </svg>
                                    @break
                                @default
                            @endswitch
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>

                <x-language-switcher />

                @auth
                    @php
                        $accountUrl = Route::has('account.index')
                            ? route('account.index')
                            : (Route::has('account.orders') ? route('account.orders') : url('/account'));
                    @endphp
                    <a href="{{ $accountUrl }}"
                       class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:border-blue-300 hover:text-blue-600 transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 21a8 8 0 0112 0" />
                        </svg>
                        {{ __('nav_account') }}
                    </a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:border-blue-300 hover:text-blue-600 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m0 0l4 4m-4-4l4-4m6-4h6a2 2 0 012 2v12a2 2 0 01-2 2h-6" />
                            </svg>
                            {{ __('auth_login') }}
                        </a>
                    @endif
                @endauth

                <livewire:components.shopping-cart-button />
            </div>
        </div>

        <div class="lg:hidden">
            <livewire:components.mobile-autocomplete
                :max-results="5"
                :search-types="['products', 'categories', 'brands']"
                :enable-suggestions="true"
                :enable-recent-searches="true"
                :enable-popular-searches="false"
            />
        </div>

        <div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 lg:flex-row lg:items-center lg:justify-between">
            <nav class="flex items-center gap-4 overflow-x-auto text-sm font-medium text-slate-700">
                <x-navigation.menu-items :items="$this->headerMenu" />
            </nav>

            @if ($quickLinks->isNotEmpty())
                <div class="flex items-center gap-2 overflow-x-auto">
                    @foreach ($quickLinks as $link)
                        <a href="{{ $link['url'] }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition">
                            {{ $link['label'] }}
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($featureEnabled && isset($categories) && count($categories) && Route::has('localized.categories.show'))
            <div class="mt-3 border-t border-slate-100 pt-3">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">{{ __('Highlighted categories') }}</span>
                    @if ($quickLinks->firstWhere('key', 'categories'))
                        <a href="{{ $quickLinks->firstWhere('key', 'categories')['url'] }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
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
                           class="group relative flex min-w-[160px] flex-col justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 transition-colors">{{ $name }}</span>
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-600">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
                            </div>
                            <span class="mt-6 inline-flex items-center gap-2 text-xs font-medium text-slate-400 group-hover:text-blue-500">
                                {{ __('Browse') }}
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($mobileMenuOpen)
            <div class="mt-4 border-t border-slate-200 pt-4 lg:hidden">
                <div class="mb-4">
                    <x-search-module
                        class="w-full"
                        :max-results="6"
                        :min-query-length="2"
                    />
                </div>
                <nav class="grid gap-2 text-sm font-medium text-slate-700">
                    @if ($quickLinks->firstWhere('key', 'categories'))
                        <a href="{{ $quickLinks->firstWhere('key', 'categories')['url'] }}" class="rounded-lg bg-slate-100 px-3 py-2 hover:bg-blue-50 hover:text-blue-600 transition">{{ __('nav_categories') }}</a>
                    @endif
                    @if ($quickLinks->firstWhere('key', 'brands'))
                        <a href="{{ $quickLinks->firstWhere('key', 'brands')['url'] }}" class="rounded-lg bg-slate-100 px-3 py-2 hover:bg-blue-50 hover:text-blue-600 transition">{{ __('nav_brands') }}</a>
                    @endif
                    @if ($quickLinks->firstWhere('key', 'collections'))
                        <a href="{{ $quickLinks->firstWhere('key', 'collections')['url'] }}" class="rounded-lg bg-slate-100 px-3 py-2 hover:bg-blue-50 hover:text-blue-600 transition">{{ __('translations.collections') ?? __('Collections') }}</a>
                    @endif
                    @if (Route::has('localized.locations.index'))
                        <a href="{{ route('localized.locations.index', ['locale' => $locale]) }}" class="rounded-lg bg-slate-100 px-3 py-2 hover:bg-blue-50 hover:text-blue-600 transition">{{ __('nav_locations') }}</a>
                    @endif
                    @if (Route::has('localized.search'))
                        <a href="{{ route('localized.search', ['locale' => $locale]) }}" class="rounded-lg bg-slate-100 px-3 py-2 hover:bg-blue-50 hover:text-blue-600 transition">{{ __('nav_search') }}</a>
                    @endif
                    @if (Route::has('localized.cart.index'))
                        <a href="{{ route('localized.cart.index', ['locale' => $locale]) }}" class="rounded-lg bg-slate-100 px-3 py-2 hover:bg-blue-50 hover:text-blue-600 transition">{{ __('nav_cart') }}</a>
                    @endif
                </nav>
            </div>
        @endif
    </x-container>
</div>

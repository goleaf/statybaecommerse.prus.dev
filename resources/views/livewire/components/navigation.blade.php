<div>
    <x-container class="px-4">
        {{-- Top bar: Logo, Search, Actions --}}
        <div class="flex items-center justify-between gap-4 py-4">
            {{-- Left: Brand / Logo --}}
            <div class="flex items-center gap-4">
                <button class="lg:hidden inline-flex items-center justify-center rounded-lg p-2.5 text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                        type="button"
                        wire:click="toggleMobileMenu"
                        aria-label="{{ __('nav_toggle') }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                @php
                    $homeUrl = Route::has('localized.home')
                        ? route('localized.home', ['locale' => app()->getLocale()])
                        : (Route::has('home')
                            ? route('home')
                            : url('/'));
                @endphp
                <a href="{{ $homeUrl }}" class="flex items-center gap-3 group" aria-label="{{ __('nav_home') }}">
                    <div class="relative">
                        <img src="{{ asset('images/hero.png') }}" alt="{{ config('app.name') }}"
                             class="h-10 w-10 rounded-xl shadow-sm group-hover:shadow-md transition-all duration-300 group-hover:scale-105"
                             width="40" height="40" />
                        <div
                             class="absolute inset-0 rounded-xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                    </div>
                    <span
                          class="hidden sm:inline text-lg font-heading font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200">{{ config('app.name') }}</span>
                </a>
            </div>

            {{-- Center: Enhanced Search with modern design --}}
            <div class="flex-1 max-w-2xl hidden md:block">
                <form wire:submit.prevent="search" role="search" aria-label="{{ __('nav_search') }}">
                    <div class="relative group">
                        <input
                               type="search"
                               wire:model.live.debounce.300ms="searchQuery"
                               placeholder="{{ __('search_placeholder') }}"
                               class="block w-full rounded-2xl border border-gray-200 bg-white/90 backdrop-blur-xl pl-12 pr-12 py-4 text-sm placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all duration-300 shadow-sm focus:shadow-lg hover:shadow-md"
                               aria-label="{{ __('search_placeholder') }}"
                               autocomplete="off"
                               spellcheck="false" />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors duration-300"
                             aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <button type="submit"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-blue-600 transition-colors duration-300 hover:scale-110"
                                aria-label="{{ __('nav_search') }}">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd"
                                      d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z"
                                      clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        {{-- Search suggestions overlay --}}
                        @if ($searchQuery && strlen($searchQuery) > 2)
                            <div
                                 class="absolute top-full left-0 right-0 mt-2 bg-white/95 backdrop-blur-xl border border-gray-200 rounded-2xl shadow-lg z-50 max-h-96 overflow-y-auto">
                                <div class="p-4">
                                    <div class="text-sm text-gray-500 mb-2">{{ __('Search suggestions') }}</div>
                                    <div class="space-y-2">
                                        <div
                                             class="p-2 hover:bg-gray-50 rounded-lg cursor-pointer transition-colors duration-200">
                                            <span class="text-gray-700">{{ $searchQuery }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-3">
                <x-language-switcher />
                @auth
                    @if (Route::has('account.index'))
                        <x-link :href="route('account.index')"
                                class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200 px-3 py-2 rounded-lg hover:bg-gray-50">{{ __('nav_account') }}</x-link>
                    @else
                        <x-link href="/account"
                                class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200 px-3 py-2 rounded-lg hover:bg-gray-50">{{ __('nav_account') }}</x-link>
                    @endif
                @else
                    @if (Route::has('login'))
                        <x-link :href="route('login')"
                                class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200 px-3 py-2 rounded-lg hover:bg-gray-50">{{ __('auth_login') }}</x-link>
                    @endif
                @endauth
                <livewire:components.shopping-cart-button />
            </div>
        </div>

        {{-- Secondary: links and categories --}}
        <div class="hidden lg:flex items-center justify-between py-2">
            <nav class="flex items-center gap-6">
                <x-navigation.menu-items :items="$this->headerMenu" />
            </nav>

            @php
                $categoryFeature = config('app-features.features.category') ?? null;
                $featureEnabled =
                    $categoryFeature instanceof \App\Support\FeatureState
                        ? $categoryFeature === \App\Support\FeatureState::Enabled
                        : (is_string($categoryFeature)
                            ? strtolower($categoryFeature) === strtolower(\App\Support\FeatureState::Enabled->value)
                            : (bool) $categoryFeature);
            @endphp

            @if ($featureEnabled && isset($categories) && count($categories) && Route::has('localized.categories.show'))
                <nav class="flex items-center gap-x-6">
                    @foreach ($categories as $category)
                        @php
                            $slug = method_exists($category, 'trans')
                                ? $category->trans('slug') ?? $category->slug
                                : $category->slug;
                            $name = method_exists($category, 'trans')
                                ? $category->trans('name') ?? $category->name
                                : $category->name;
                        @endphp
                        <x-nav.item :href="route('localized.categories.show', [
                            'locale' => app()->getLocale(),
                            'category' => $slug,
                        ])">{{ $name }}</x-nav.item>
                    @endforeach
                </nav>
            @endif
        </div>

        {{-- Mobile menu panel --}}
        @if ($mobileMenuOpen)
            <div class="lg:hidden border-t border-gray-200 py-3">
                <div class="mb-3">
                    <form wire:submit.prevent="search">
                        <input type="search" wire:model.live.debounce.300ms="searchQuery"
                               placeholder="{{ __('search_placeholder') }}"
                               class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" />
                    </form>
                </div>
                <nav class="grid gap-2">
                    @if (Route::has('brands.index'))
                        <x-link :href="route('brands.index', ['locale' => app()->getLocale()])"
                                class="text-sm font-medium text-gray-700">{{ __('nav_brands') }}</x-link>
                    @endif
                    @if (Route::has('locations.index'))
                        <x-link :href="route('locations.index', ['locale' => app()->getLocale()])"
                                class="text-sm font-medium text-gray-700">{{ __('nav_locations') }}</x-link>
                    @endif
                    @if (Route::has('search'))
                        <x-link :href="route('search', ['locale' => app()->getLocale()])"
                                class="text-sm font-medium text-gray-700">{{ __('nav_search') }}</x-link>
                    @endif
                    @if (Route::has('cart.index'))
                        <x-link :href="route('cart.index', ['locale' => app()->getLocale()])"
                                class="text-sm font-medium text-gray-700">{{ __('nav_cart') }}</x-link>
                    @endif
                </nav>
            </div>
        @endif
    </x-container>
</div>

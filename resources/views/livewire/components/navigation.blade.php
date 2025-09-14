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

            {{-- Center: Live Search Module --}}
            <div class="flex-1 max-w-2xl hidden md:block">
                <x-search-module 
                    class="w-full"
                    :max-results="8"
                    :min-query-length="2"
                />
            </div>
            
            {{-- Mobile Search --}}
            <div class="flex-1 md:hidden">
                <livewire:components.mobile-autocomplete 
                    :max-results="5"
                    :search-types="['products', 'categories', 'brands']"
                    :enable-suggestions="true"
                    :enable-recent-searches="true"
                    :enable-popular-searches="false"
                />
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
                    <x-search-module 
                        class="w-full"
                        :max-results="5"
                        :min-query-length="2"
                    />
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

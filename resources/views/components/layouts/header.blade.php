<header class="sticky top-0 z-20 border-b border-gray-200 bg-white bg-opacity-80 backdrop-blur-xl backdrop-filter">
    <x-banner />
    <x-container class="flex items-center justify-between px-4 py-2">
        <nav role="navigation" class="flex items-center gap-10">
            <x-link :href="route('home')" class="relative text-sm">
                <x-brand class="h-8 w-auto" aria-hidden="true" />
            </x-link>
            @if (class_exists(\Livewire\Volt\Volt::class) && view()->exists('livewire.components.navigation'))
                <livewire:components.navigation />
            @else
                @php($features = config('shopper.features'))
                <div class="flex items-center gap-6">
                    @if (
                        (string) ($features['brand'] ?? 'enabled') === \App\Support\FeatureState::Enabled->value &&
                            Route::has('brand.index'))
                        <x-link :href="route('brand.index', ['locale' => app()->getLocale()])"
                                class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('Brands') }}</x-link>
                    @endif
                    @if (
                        (string) ($features['category'] ?? 'enabled') === \App\Support\FeatureState::Enabled->value &&
                            Route::has('category.index'))
                        <x-link :href="route('category.index', ['locale' => app()->getLocale()])"
                                class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('Categories') }}</x-link>
                    @endif
                    @if (
                        (string) ($features['collection'] ?? 'enabled') === \App\Support\FeatureState::Enabled->value &&
                            Route::has('collection.index'))
                        <x-link :href="route('collection.index', ['locale' => app()->getLocale()])"
                                class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('Collections') }}</x-link>
                    @endif
                    <x-link :href="route('locations.index', ['locale' => app()->getLocale()])"
                            class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('Locations') }}</x-link>
                    <x-link :href="route('search.index', ['locale' => app()->getLocale()])"
                            class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('Search') }}</x-link>
                    <x-link :href="route('cart.index', ['locale' => app()->getLocale()])"
                            class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('Cart') }}</x-link>
                    @if (auth()->check() && auth()->user()->can('view system'))
                        <x-link :href="route('admin.discounts.presets')"
                                class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('Admin') }}</x-link>
                    @endif
                    @if (auth()->check() && auth()->user()->can('view orders'))
                        <x-link :href="route('exports.index')"
                                class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('Exports') }}</x-link>
                    @endif
                </div>
            @endif
        </nav>
        <div class="ml-auto flex items-center gap-4">
            @if (class_exists(\Livewire\Volt\Volt::class) && view()->exists('livewire.components.account-menu'))
                <div class="hidden lg:flex lg:flex-1 lg:items-center lg:justify-end lg:space-x-6">
                    @auth
                        <livewire:components.account-menu />
                    @else
                        <x-link :href="route('login', ['locale' => app()->getLocale()])" class="text-sm font-medium text-gray-700 hover:text-gray-800">
                            {{ __('Login') }}
                        </x-link>
                        <span class="h-6 w-px bg-gray-200" aria-hidden="true"></span>
                        <x-link :href="route('register', ['locale' => app()->getLocale()])" class="text-sm font-medium text-gray-700 hover:text-gray-800">
                            {{ __('Register') }}
                        </x-link>
                    @endauth
                </div>
            @endif

            @if (auth()->check() && auth()->user()->can('view system'))
                <x-link :href="route('admin.discounts.presets')"
                        class="hidden md:inline-flex text-sm font-medium text-gray-700 hover:text-gray-800">
                    {{ __('Admin') }}
                </x-link>
            @endif
            @if (auth()->check() && auth()->user()->can('view orders'))
                <x-link :href="route('exports.index')"
                        class="hidden md:inline-flex text-sm font-medium text-gray-700 hover:text-gray-800">
                    {{ __('Exports') }}
                </x-link>
            @endif

            @php($currencyClass = \App\Livewire\Components\CurrencySelector::class)
            @if (class_exists($currencyClass))
                @livewire($currencyClass)
            @endif

            <!-- Language Switcher -->
            @includeIf('components.language-switcher')

            <form method="GET" action="{{ route('search.index', ['locale' => app()->getLocale()]) }}"
                  class="hidden md:block">
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search') }}"
                           class="w-64 rounded-md border-gray-300 pl-9" />
                    <span class="absolute left-2 top-1.5 text-gray-400">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                             aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                </div>
            </form>

            <x-link :href="route('cart.index', ['locale' => app()->getLocale()])"
                    class="inline-flex items-center gap-1 text-sm font-medium text-gray-700 hover:text-gray-800">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7l.867 12.142A2 2 0 008.86 21h6.28a2 2 0 001.993-1.858L18 7M9 7V5a3 3 0 116 0v2M4 7h16" />
                </svg>
                <span>{{ __('Cart') }}</span>
            </x-link>
        </div>
    </x-container>
    <x-container class="px-4 py-2">
        @includeIf('components.store-badge')
    </x-container>
</header>

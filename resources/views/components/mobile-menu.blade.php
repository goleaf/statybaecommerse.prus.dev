@props(['categories' => null, 'brands' => null])

@php
    $categories =
        $categories ??
        \App\Models\Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => function ($query) {
                    $query->where('is_active', true)->limit(3);
                },
            ])
            ->limit(6)
            ->get();
    $brands = $brands ?? \App\Models\Brand::where('is_active', true)->limit(8)->get();
@endphp

<div x-data="{ open: false }" class="lg:hidden">
    {{-- Mobile Menu Toggle --}}
    <button @click="open = !open"
            class="inline-flex items-center justify-center rounded-lg p-2.5 text-gray-700 hover:bg-gray-100 transition-colors duration-200"
            type="button"
            aria-label="{{ __('Toggle mobile menu') }}">
        <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg x-show="open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Mobile Menu Overlay --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
         style="display: none;"
         @click="open = false">
    </div>

    {{-- Mobile Menu Panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform -translate-x-full"
         x-transition:enter-end="transform translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform translate-x-0"
         x-transition:leave-end="transform -translate-x-full"
         class="fixed inset-y-0 left-0 z-50 w-80 max-w-sm bg-white shadow-xl overflow-y-auto"
         style="display: none;">

        {{-- Menu Header --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Menu') }}</h2>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Menu Content --}}
        <div class="p-4 space-y-6">
            {{-- Search --}}
            <div>
                <x-search-bar :show-suggestions="false" />
            </div>

            {{-- Main Navigation --}}
            <nav class="space-y-2">
                <a href="{{ route('localized.home', ['locale' => app()->getLocale()]) ?? url('/') }}"
                   class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    {{ __('Home') }}
                </a>

                <a href="{{ route('localized.products.index', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    {{ __('Products') }}
                </a>

                <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 11h10">
                        </path>
                    </svg>
                    {{ __('Categories') }}
                </a>

                <a href="{{ route('localized.brands.index', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                        </path>
                    </svg>
                    {{ __('Brands') }}
                </a>
            </nav>

            {{-- Categories Section --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Categories') }}</h3>
                <div class="space-y-1">
                    @foreach ($categories as $category)
                        <div x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                                <span class="text-sm">{{ $category->name }}</span>
                                @if ($category->children->count() > 0)
                                    <svg class="w-4 h-4 transition-transform duration-200"
                                         :class="{ 'rotate-180': open }" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                @endif
                            </button>

                            @if ($category->children->count() > 0)
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform scale-95"
                                     x-transition:enter-end="opacity-100 transform scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 transform scale-100"
                                     x-transition:leave-end="opacity-0 transform scale-95"
                                     class="ml-4 space-y-1"
                                     style="display: none;">
                                    @foreach ($category->children as $subcategory)
                                        <a href="{{ route('categories.show', ['locale' => app()->getLocale(), 'category' => $subcategory->slug]) ?? '/categories/' . $subcategory->slug }}"
                                           class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                                            {{ $subcategory->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Brands Section --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Popular Brands') }}</h3>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($brands as $brand)
                        <a href="{{ route('brands.show', $brand->slug) }}"
                           class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                            @if ($brand->getFirstMediaUrl('logo'))
                                <img src="{{ $brand->getFirstMediaUrl('logo', 'thumb') }}"
                                     alt="{{ $brand->name }}"
                                     class="w-5 h-5 object-contain">
                            @endif
                            <span class="truncate">{{ $brand->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- User Account Section --}}
            <div class="border-t border-gray-200 pt-4">
                @auth
                    <a href="{{ route('account.index', ['locale' => app()->getLocale()]) ?? '/account' }}"
                       class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ __('My Account') }}
                    </a>

                    <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) ?? '/logout' }}"
                          class="mt-2">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200 w-full text-left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            {{ __('Logout') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login', ['locale' => app()->getLocale()]) ?? '/login' }}"
                       class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                            </path>
                        </svg>
                        {{ __('Login') }}
                    </a>

                    <a href="{{ route('register', ['locale' => app()->getLocale()]) ?? '/register' }}"
                       class="flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                            </path>
                        </svg>
                        {{ __('Register') }}
                    </a>
                @endauth
            </div>

            {{-- Language Switcher --}}
            <div class="border-t border-gray-200 pt-4">
                <x-language-switcher />
            </div>
        </div>
    </div>
</div>

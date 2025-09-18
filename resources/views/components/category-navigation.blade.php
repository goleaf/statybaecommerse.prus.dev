@props(['categories' => null, 'showImages' => true, 'maxItems' => 8])

@php
    $categories =
        $categories ??
        \App\Models\Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => function ($query) {
                    $query->where('is_active', true)->limit(5);
                },
            ])
            ->limit($maxItems)
            ->get();
@endphp

<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex space-x-8 overflow-x-auto py-4" aria-label="{{ __('Categories') }}">
            @foreach ($categories as $category)
                <div class="relative group flex-shrink-0" x-data="{ open: false }">
                    {{-- Main Category Link --}}
                    <a href="{{ route('categories.show', ['locale' => app()->getLocale(), 'category' => $category->slug]) ?? '/categories/' . $category->slug }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-lg transition-colors duration-200"
                       @mouseenter="open = true"
                       @mouseleave="open = false">

                        @if ($showImages && $category->getFirstMediaUrl('images'))
                            <img src="{{ $category->getFirstMediaUrl('images', 'thumb') }}"
                                 alt="{{ $category->name }}"
                                 class="w-6 h-6 object-cover rounded">
                        @else
                            <div
                                 class="w-6 h-6 bg-gradient-to-br from-blue-100 to-purple-100 rounded flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 11h10">
                                    </path>
                                </svg>
                            </div>
                        @endif

                        <span>{{ $category->name }}</span>

                        @if ($category->children->count() > 0)
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors duration-200"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    </a>

                    {{-- Subcategories Dropdown --}}
                    @if ($category->children->count() > 0)
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute top-full left-0 mt-2 w-64 bg-white rounded-xl shadow-large border border-gray-200 py-2 z-50"
                             style="display: none;">

                            <div class="px-4 py-2 border-b border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $category->name }}</h3>
                                <p class="text-xs text-gray-600">{{ $category->children->count() }}
                                    {{ __('subcategories') }}</p>
                            </div>

                            <div class="py-2">
                                @foreach ($category->children as $subcategory)
                                    <a href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $subcategory->slug]) }}"
                                       class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors duration-200">

                                        @if ($showImages && $subcategory->getFirstMediaUrl('images'))
                                            <img src="{{ $subcategory->getFirstMediaUrl('images', 'thumb') }}"
                                                 alt="{{ $subcategory->name }}"
                                                 class="w-5 h-5 object-cover rounded">
                                        @else
                                            <div
                                                 class="w-5 h-5 bg-gradient-to-br from-gray-100 to-gray-200 rounded flex items-center justify-center">
                                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                                    </path>
                                                </svg>
                                            </div>
                                        @endif

                                        <span>{{ $subcategory->name }}</span>
                                    </a>
                                @endforeach

                                @if ($category->children->count() > 5)
                                    <div class="border-t border-gray-100 mt-2 pt-2">
                                        <a href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $category->slug]) }}"
                                           class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 transition-colors duration-200">
                                            <span>{{ __('View All') }} {{ $category->name }}</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- View All Categories Link --}}
            <div class="flex-shrink-0">
                <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    <span>{{ __('All Categories') }}</span>
                </a>
            </div>
        </nav>
    </div>
</div>

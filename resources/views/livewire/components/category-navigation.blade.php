<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-8">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('translations.categories') }}</h2>

                <nav class="hidden md:flex space-x-6" x-data="{ openDropdown: null }">
                    @foreach ($this->categoryTree as $category)
                        <div class="relative" @mouseenter="openDropdown = {{ $category['id'] }}"
                             @mouseleave="openDropdown = null">
                            <button
                                    type="button"
                                    class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">
                                <span>{{ $category['name'] }}</span>
                                @if ($category['products_count'] > 0)
                                    <span
                                          class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ $category['products_count'] }}
                                    </span>
                                @endif
                                @if ($category['has_children'])
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7" />
                                    </svg>
                                @endif
                            </button>

                            @if ($category['has_children'])
                                <div
                                     x-show="openDropdown === {{ $category['id'] }}"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform scale-95"
                                     x-transition:enter-end="opacity-100 transform scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 transform scale-100"
                                     x-transition:leave-end="opacity-0 transform scale-95"
                                     class="absolute top-full left-0 mt-1 w-64 bg-white rounded-md shadow-lg border border-gray-200 z-50">
                                    <div class="py-2">
                                        <a
                                           href="{{ route('category.show', ['category' => $category['slug']]) }}"
                                           class="block px-4 py-2 text-sm font-medium text-gray-900 hover:bg-gray-50 border-b border-gray-100">
                                            {{ __('translations.view_all') }} {{ $category['name'] }}
                                        </a>

                                        @foreach ($category['children'] as $child)
                                            <a
                                               href="{{ route('category.show', ['category' => $child['slug']]) }}"
                                               class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600">
                                                <span>{{ $child['name'] }}</span>
                                                @if ($child['products_count'] > 0)
                                                    <span
                                                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                        {{ $child['products_count'] }}
                                                    </span>
                                                @else
                                                    <span
                                                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                                        0
                                                    </span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </nav>
            </div>

            <div class="flex items-center space-x-4">
                <a
                   href="{{ route('categories.index') }}"
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    {{ __('translations.view_all_categories') }}
                </a>
            </div>
        </div>
    </div>
</div>

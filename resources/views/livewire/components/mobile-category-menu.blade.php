<div class="lg:hidden bg-white border-b border-gray-200">
    <div class="px-4 py-3">
        <div x-data="{
            openCategory: null,
            toggleCategory(categoryId) {
                this.openCategory = this.openCategory === categoryId ? null : categoryId;
            },
            isOpen(categoryId) {
                return this.openCategory === categoryId;
            }
        }" class="space-y-2">

            @foreach ($this->categoryTree as $category)
                <div class="border border-gray-200 rounded-lg">
                    <button
                            type="button"
                            @click="toggleCategory({{ $category['id'] }})"
                            class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-medium text-gray-900">{{ $category['name'] }}</span>
                            @if ($category['products_count'] > 0)
                                <span
                                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $category['products_count'] }}
                                </span>
                            @endif
                        </div>
                        @if ($category['has_children'])
                            <svg
                                 class="w-5 h-5 text-gray-500 transition-transform duration-200"
                                 :class="{ 'rotate-180': isOpen({{ $category['id'] }}) }"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7" />
                            </svg>
                        @endif
                    </button>

                    @if ($category['has_children'])
                        <div
                             x-show="isOpen({{ $category['id'] }})"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="border-t border-gray-200 bg-gray-50">
                            @foreach ($category['children'] as $child)
                                <a
                                   href="{{ route('localized.categories.show', ['category' => $child['slug']]) }}"
                                   class="flex items-center justify-between px-6 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
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
                    @endif
                </div>
            @endforeach

        </div>
    </div>
</div>

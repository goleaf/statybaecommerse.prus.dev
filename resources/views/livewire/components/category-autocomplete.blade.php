<div class="relative" x-data="{ 
    showResults: @entangle('showResults'),
    query: @entangle('query'),
    isSearching: @entangle('isSearching'),
    selectedIndex: -1,
    results: @entangle('results')
}" x-init="
    $watch('query', value => {
        if (value.length < {{ $minQueryLength }}) {
            showResults = false;
        }
    });
    
    // Close results when clicking outside
    $el.addEventListener('clickoutside', () => {
        showResults = false;
        selectedIndex = -1;
    });
    
    // Keyboard navigation
    $el.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, results.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0 && results[selectedIndex]) {
                $wire.selectResult(results[selectedIndex]);
            }
        } else if (e.key === 'Escape') {
            showResults = false;
            selectedIndex = -1;
        }
    });
">
    {{-- Hidden input for form submission --}}
    @if(!$allowMultiple)
        <input type="hidden" name="{{ $name }}" value="{{ $selectedCategoryId }}" />
    @else
        @foreach($selectedCategories as $category)
            <input type="hidden" name="{{ $name }}[]" value="{{ $category['id'] }}" />
        @endforeach
    @endif
    
    {{-- Selected Categories Display (Multiple Mode) --}}
    @if($allowMultiple && count($selectedCategories) > 0)
        <div class="mb-2">
            <div class="flex flex-wrap gap-2">
                @foreach($selectedCategories as $category)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $category['name'] }}
                        <button
                            wire:click="removeCategory({{ $category['id'] }})"
                            wire:confirm="{{ __('translations.confirm_remove_category_selection') }}"
                            type="button"
                            class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-500 focus:outline-none focus:bg-blue-200 focus:text-blue-500 dark:hover:bg-blue-800 dark:hover:text-blue-300"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </span>
                @endforeach
            </div>
        </div>
    @endif
    
    {{-- Search Input --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        
        <input
            wire:model.live.debounce.300ms="query"
            type="text"
            placeholder="{{ $placeholder }}"
            class="block w-full pl-10 pr-20 py-2 border border-gray-300 rounded-md bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
            autocomplete="off"
            x-ref="searchInput"
        />
        
        {{-- Loading Spinner --}}
        <div wire:loading class="absolute inset-y-0 right-0 flex items-center pr-10">
            <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        {{-- Clear Button --}}
        <div wire:loading.remove class="absolute inset-y-0 right-0 flex items-center pr-3">
            <button
                wire:click="clearSelection"
                wire:confirm="{{ __('translations.confirm_clear_category_selection') }}"
                x-show="query.length > 0"
                type="button"
                class="text-gray-400 hover:text-gray-600 focus:outline-none"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Search Results Dropdown --}}
    <div 
        x-show="showResults && (query.length >= {{ $minQueryLength }})"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto dark:bg-gray-800 dark:border-gray-700"
    >
        @if($isSearching)
            {{-- Loading State --}}
            <div class="flex items-center justify-center py-4">
                <div class="flex items-center space-x-2 text-gray-500">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">{{ __('admin.category.searching') }}</span>
                </div>
            </div>
        @elseif(count($results) > 0)
            {{-- Search Results --}}
            <div class="py-1">
                @foreach($results as $index => $result)
                    <button
                        wire:click="selectResult({{ json_encode($result) }})"
                        class="w-full px-3 py-2 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none dark:hover:bg-gray-700 dark:focus:bg-gray-700"
                        :class="{ 'bg-gray-50 dark:bg-gray-700': selectedIndex === {{ $index }} }"
                    >
                        <div class="flex items-center space-x-3">
                            {{-- Category Image --}}
                            <div class="flex-shrink-0">
                                @if($result['image'])
                                    <img 
                                        src="{{ $result['image'] }}" 
                                        alt="{{ $result['title'] }}"
                                        class="w-8 h-8 object-cover rounded"
                                    />
                                @else
                                    <div class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center dark:bg-gray-600">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Category Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                        {{ $result['title'] }}
                                    </h3>
                                    <div class="flex items-center space-x-2">
                                        @if($result['products_count'] > 0)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $result['products_count'] }} {{ __('admin.category.products') }}
                                            </span>
                                        @endif
                                        @if($result['children_count'] > 0)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $result['children_count'] }} {{ __('admin.category.subcategories') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($result['subtitle'])
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">
                                        {{ $result['subtitle'] }}
                                    </p>
                                @endif
                                
                                @if($result['description'])
                                    <p class="text-xs text-gray-400 truncate dark:text-gray-500">
                                        {{ Str::limit($result['description'], 60) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        @elseif(strlen($query) >= $minQueryLength)
            {{-- No Results --}}
            <div class="px-3 py-4 text-center">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{ __('admin.category.no_categories_found') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('admin.category.try_different_keywords') }}
                </p>
            </div>
        @endif
    </div>
</div>

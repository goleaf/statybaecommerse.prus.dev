<div class="infinite-scroll-search" x-data="infiniteScrollSearch()">
    <!-- Search Header -->
    <div class="search-header mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Search Input -->
            <div class="flex-1">
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="query"
                        placeholder="{{ __('frontend.search_placeholder') }}"
                        class="w-full px-4 py-3 pl-12 pr-4 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Search Controls -->
            <div class="flex items-center gap-2">
                <!-- Filters Toggle -->
                <button 
                    wire:click="toggleFilters"
                    wire:confirm="{{ __('translations.confirm_toggle_filters') }}"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-blue-500"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    {{ __('frontend.filters') }}
                    @if($filterCount > 0)
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-blue-600 rounded-full">
                            {{ $filterCount }}
                        </span>
                    @endif
                </button>

                <!-- Sort Dropdown -->
                <select 
                    wire:model.live="sortBy"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <!-- Sort Order -->
                <button 
                    wire:click="$set('sortOrder', $sortOrder === 'asc' ? 'desc' : 'asc')"
                    class="p-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-blue-500"
                >
                    @if($sortOrder === 'asc')
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                        </svg>
                    @else
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"></path>
                        </svg>
                    @endif
                </button>
            </div>
        </div>

        <!-- Active Filters -->
        @if($filterCount > 0)
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($filters as $filterType => $filterValue)
                    <span class="inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-blue-800 bg-blue-100 rounded-full">
                        {{ $filterType }}: {{ is_array($filterValue) ? implode(', ', $filterValue) : $filterValue }}
                        <button 
                            wire:click="removeFilter('{{ $filterType }}')"
                            wire:confirm="{{ __('translations.confirm_remove_filter') }}"
                            class="text-blue-600 hover:text-blue-800"
                        >
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </span>
                @endforeach
                <button 
                    wire:click="clearFilters"
                    wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
                    class="text-sm text-gray-600 hover:text-gray-800 underline"
                >
                    {{ __('frontend.clear_all_filters') }}
                </button>
            </div>
        @endif
    </div>

    <!-- Filters Panel -->
    @if($showFilters)
        <div class="filters-panel mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Type Filters -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('frontend.search_types') }}
                    </label>
                    <div class="space-y-2">
                        @foreach($typeOptions as $value => $label)
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model.live="selectedTypes"
                                    value="{{ $value }}"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Available Filters -->
                @if(!empty($availableFilters))
                    @foreach($availableFilters as $filterType => $filterOptions)
                        @if(!empty($filterOptions))
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('frontend.' . $filterType) }}
                                </label>
                                <div class="space-y-1 max-h-32 overflow-y-auto">
                                    @foreach($filterOptions as $option => $count)
                                        <label class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    wire:click="applyFilter('{{ $filterType }}', '{{ $option }}')"
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                >
                                                <span class="ml-2 text-sm text-gray-700">{{ $option }}</span>
                                            </div>
                                            <span class="text-xs text-gray-500">({{ $count }})</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    @endif

    <!-- Search Results -->
    <div class="search-results">
        @if($isSearching)
            @if($hasResults)
                <!-- Results Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach($results as $result)
                        <div class="result-item bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                            <!-- Result Image -->
                            @if(!empty($result['image']))
                                <div class="aspect-w-16 aspect-h-9">
                                    <img 
                                        src="{{ $result['image'] }}" 
                                        alt="{{ $result['title'] }}"
                                        class="w-full h-48 object-cover rounded-t-lg"
                                    >
                                </div>
                            @endif

                            <!-- Result Content -->
                            <div class="p-4">
                                <!-- Type Badge -->
                                <span class="inline-block px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full mb-2">
                                    {{ __('frontend.' . $result['type']) }}
                                </span>

                                <!-- Title -->
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    {!! $result['title'] !!}
                                </h3>

                                <!-- Subtitle -->
                                @if(!empty($result['subtitle']))
                                    <p class="text-sm text-gray-600 mb-2">
                                        {!! $result['subtitle'] !!}
                                    </p>
                                @endif

                                <!-- Description -->
                                @if(!empty($result['description']))
                                    <p class="text-sm text-gray-500 mb-3">
                                        {!! $result['description'] !!}
                                    </p>
                                @endif

                                <!-- Price -->
                                @if(!empty($result['formatted_price']))
                                    <p class="text-lg font-bold text-green-600 mb-3">
                                        {{ $result['formatted_price'] }}
                                    </p>
                                @endif

                                <!-- Action Button -->
                                <a 
                                    href="{{ $result['url'] }}"
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                >
                                    {{ __('frontend.view_details') }}
                                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Load More Button -->
                @if($hasMore)
                    <div class="text-center">
                        <button 
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-6 py-3 text-base font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="loadMore">
                                {{ __('frontend.load_more') }}
                            </span>
                            <span wire:loading wire:target="loadMore">
                                {{ __('frontend.loading') }}...
                            </span>
                        </button>
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        {{ __('frontend.no_more_results') }}
                    </div>
                @endif

            @else
                <!-- No Results -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('frontend.no_results_found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('frontend.try_different_search') }}</p>
                </div>
            @endif
        @else
            <!-- Search Prompt -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('frontend.start_searching') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('frontend.enter_search_term') }}</p>
            </div>
        @endif
    </div>

    <!-- Loading Overlay -->
    @if($isLoading && $page === 1)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700">{{ __('frontend.searching') }}...</span>
            </div>
        </div>
    @endif
</div>

<script>
function infiniteScrollSearch() {
    return {
        init() {
            // Intersection Observer for infinite scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        @this.call('loadMore');
                    }
                });
            }, {
                rootMargin: '100px'
            });

            // Observe the load more button
            this.$nextTick(() => {
                const loadMoreButton = this.$el.querySelector('[wire\\:click="loadMore"]');
                if (loadMoreButton) {
                    observer.observe(loadMoreButton);
                }
            });
        }
    }
}
</script>

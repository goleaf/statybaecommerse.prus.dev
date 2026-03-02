@props([
    'query' => null,
    'results' => null,
    'title' => null,
    'subtitle' => null,
    'showFilters' => true,
    'showSorting' => true,
    'showCategories' => true,
    'showSuggestions' => true,
    'maxResults' => 50,
])

@php
    $query = $query ?? request('q');
    $title = $title ?? __('frontend.search_results.title');
    $subtitle = $subtitle ?? __('frontend.search_results.subtitle', ['query' => $query]);
    $results = $results ?? collect([]);

    // Get search suggestions
    $suggestions = collect([
        __('frontend.search_results.suggestions.popular_title') => ['laptop', 'smartphone', 'headphones', 'camera', 'tablet'],
        __('frontend.search_results.suggestions.categories_title') => ['Electronics', 'Clothing', 'Home & Garden', 'Sports', 'Books'],
        __('frontend.search_results.suggestions.brands_title') => ['Apple', 'Samsung', 'Sony', 'Nike', 'Adidas'],
    ]);

    // Get recent searches from session
    $recentSearches = session('recent_searches', []);
@endphp

<div class="search-results" x-data="searchResults()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h1>
            <p class="text-lg text-gray-600">{{ $subtitle }}</p>
        </div>

        {{-- Search Bar --}}
        <div class="max-w-2xl mx-auto mb-8">
            <form @submit.prevent="performSearch()" class="relative">
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="{{ __('frontend.search_results.search_placeholder') }}"
                           class="w-full px-6 py-4 pl-12 pr-16 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder:text-gray-500">

                    {{-- Search Icon --}}
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    {{-- Search Button --}}
                    <button type="submit"
                            class="absolute inset-y-0 right-0 flex items-center pr-4">
                        <span class="btn-gradient px-6 py-2 rounded-xl font-medium text-sm">
                            {{ __('frontend.search_results.search_action') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>

        @if ($results->count() > 0)
            {{-- Results Summary --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="text-sm text-gray-600">
                    {{ __('frontend.search_results.found') }} <span class="font-medium">{{ $results->count() }}</span> {{ __('frontend.search_results.results_for') }}
                    <span class="font-medium">"{{ $query }}"</span>
                </div>

                @if ($showSorting)
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">{{ __('frontend.search_results.sort_by') }}:</span>
                        <select x-model="sortBy" @change="applySorting()"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="relevance">{{ __('frontend.search_results.sort.relevance') }}</option>
                            <option value="price_asc">{{ __('frontend.search_results.sort.price_low_high') }}</option>
                            <option value="price_desc">{{ __('frontend.search_results.sort.price_high_low') }}</option>
                            <option value="name_asc">{{ __('frontend.search_results.sort.name_a_z') }}</option>
                            <option value="name_desc">{{ __('frontend.search_results.sort.name_z_a') }}</option>
                            <option value="newest">{{ __('frontend.search_results.sort.newest_first') }}</option>
                        </select>
                    </div>
                @endif
            </div>

            {{-- Filters --}}
            @if ($showFilters)
                <div class="bg-white border border-gray-200 rounded-xl p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('frontend.search_results.refine_title') }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        {{-- Price Range --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('frontend.search_results.filters.price_range') }}</h4>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <input type="number" x-model="filters.priceMin" placeholder="{{ __('frontend.search_results.filters.min') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" x-model="filters.priceMax" placeholder="{{ __('frontend.search_results.filters.max') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        {{-- Categories --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('frontend.search_results.filters.categories') }}</h4>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                @foreach (\App\Models\Category::where('is_active', true)->get() as $category)
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                        <input type="checkbox" x-model="filters.categories" value="{{ $category->id }}"
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Brands --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('frontend.search_results.filters.brands') }}</h4>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                @foreach (\App\Models\Brand::where('is_active', true)->get() as $brand)
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                        <input type="checkbox" x-model="filters.brands" value="{{ $brand->id }}"
                                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm text-gray-700">{{ $brand->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    <div class="flex gap-2 mt-4">
                        <button @click="applyFilters()"
                                class="btn-gradient px-6 py-2 rounded-lg font-medium text-sm">
                            {{ __('frontend.search_results.filters.apply') }}
                        </button>
                        <button @click="clearFilters()"
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-colors duration-200">
                            {{ __('frontend.search_results.filters.clear') }}
                        </button>
                    </div>
                </div>
            @endif

            {{-- Results Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                @foreach ($results as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            {{-- Load More --}}
            @if ($results->count() >= $maxResults)
                <div class="text-center">
                    <button @click="loadMore()"
                            class="btn-gradient px-8 py-3 rounded-xl font-semibold">
                        {{ __('frontend.search_results.load_more') }}
                    </button>
                </div>
            @endif
        @else
            {{-- No Results --}}
            <div class="text-center py-16">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('frontend.search_results.empty.title') }}</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    {{ __('frontend.search_results.empty.description') }}
                </p>

                {{-- Search Suggestions --}}
                @if ($showSuggestions)
                    <div class="max-w-2xl mx-auto">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">{{ __('frontend.search_results.empty.try_searching_for') }}:</h4>
                        <div class="flex flex-wrap gap-2 justify-center">
                            @foreach ($suggestions[__('frontend.search_results.suggestions.popular_title')] as $suggestion)
                                <button @click="searchFor('{{ $suggestion }}')"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Search Suggestions --}}
        @if ($showSuggestions && $results->count() > 0)
            <div class="mt-12 bg-gray-50 rounded-2xl p-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">{{ __('frontend.search_results.suggestions.title') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Popular Searches --}}
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">{{ __('frontend.search_results.suggestions.popular_title') }}</h4>
                        <div class="space-y-2">
                            @foreach ($suggestions[__('frontend.search_results.suggestions.popular_title')] as $suggestion)
                                <button @click="searchFor('{{ $suggestion }}')"
                                        class="block w-full text-left px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-blue-600 rounded-lg transition-colors duration-200">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Categories --}}
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">{{ __('frontend.search_results.suggestions.browse_categories') }}</h4>
                        <div class="space-y-2">
                            @foreach ($suggestions[__('frontend.search_results.suggestions.categories_title')] as $category)
                                <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
                                   class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-blue-600 rounded-lg transition-colors duration-200">
                                    {{ $category }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Recent Searches --}}
                    @if (count($recentSearches) > 0)
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">{{ __('frontend.search_results.suggestions.recent_searches') }}</h4>
                            <div class="space-y-2">
                                @foreach ($recentSearches as $recent)
                                    <button @click="searchFor('{{ $recent }}')"
                                            class="block w-full text-left px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-blue-600 rounded-lg transition-colors duration-200">
                                        {{ $recent }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function searchResults() {
        return {
            searchQuery: '{{ $query }}',
            sortBy: 'relevance',
            filters: {
                priceMin: '',
                priceMax: '',
                categories: [],
                brands: []
            },

            performSearch() {
                if (!this.searchQuery.trim()) return;

                // Add to recent searches
                this.addToRecentSearches(this.searchQuery);

                // Perform search
                const url = new URL(window.location);
                url.searchParams.set('q', this.searchQuery);
                window.location.href = url.toString();
            },

            searchFor(query) {
                this.searchQuery = query;
                this.performSearch();
            },

            applySorting() {
                const url = new URL(window.location);
                url.searchParams.set('sort', this.sortBy);
                window.location.href = url.toString();
            },

            applyFilters() {
                const url = new URL(window.location);

                if (this.filters.priceMin) url.searchParams.set('price_min', this.filters.priceMin);
                if (this.filters.priceMax) url.searchParams.set('price_max', this.filters.priceMax);
                if (this.filters.categories.length > 0) url.searchParams.set('categories', this.filters.categories.join(
                    ','));
                if (this.filters.brands.length > 0) url.searchParams.set('brands', this.filters.brands.join(','));

                window.location.href = url.toString();
            },

            clearFilters() {
                this.filters = {
                    priceMin: '',
                    priceMax: '',
                    categories: [],
                    brands: []
                };

                const url = new URL(window.location);
                url.searchParams.delete('price_min');
                url.searchParams.delete('price_max');
                url.searchParams.delete('categories');
                url.searchParams.delete('brands');

                window.location.href = url.toString();
            },

            loadMore() {
                // Load more results logic
                const url = new URL(window.location);
                const currentPage = parseInt(url.searchParams.get('page') || '1');
                url.searchParams.set('page', currentPage + 1);

                // AJAX load more results
                fetch(url.toString())
                    .then(response => response.text())
                    .then(html => {
                        // Append new results to the grid
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newResults = doc.querySelectorAll('.product-card');

                        newResults.forEach(result => {
                            document.querySelector('.grid').appendChild(result);
                        });
                    });
            },

            addToRecentSearches(query) {
                // Add to recent searches in session
                fetch('/search/recent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        query: query
                    })
                });
            }
        }
    }
</script>

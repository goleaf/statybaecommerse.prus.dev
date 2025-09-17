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
    $title = $title ?? __('Search Results');
    $subtitle = $subtitle ?? __('Search results for') . ' "' . $query . '"';
    $results = $results ?? collect([]);

    // Get search suggestions
    $suggestions = collect([
        'Popular searches' => ['laptop', 'smartphone', 'headphones', 'camera', 'tablet'],
        'Categories' => ['Electronics', 'Clothing', 'Home & Garden', 'Sports', 'Books'],
        'Brands' => ['Apple', 'Samsung', 'Sony', 'Nike', 'Adidas'],
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
                    <input type="text" x-model="searchQuery" placeholder="{{ __('Search for products...') }}"
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
                            {{ __('Search') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>

        @if ($results->count() > 0)
            {{-- Results Summary --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="text-sm text-gray-600">
                    {{ __('Found') }} <span class="font-medium">{{ $results->count() }}</span> {{ __('results for') }}
                    <span class="font-medium">"{{ $query }}"</span>
                </div>

                @if ($showSorting)
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">{{ __('Sort by') }}:</span>
                        <select x-model="sortBy" @change="applySorting()"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="relevance">{{ __('Relevance') }}</option>
                            <option value="price_asc">{{ __('Price: Low to High') }}</option>
                            <option value="price_desc">{{ __('Price: High to Low') }}</option>
                            <option value="name_asc">{{ __('Name: A to Z') }}</option>
                            <option value="name_desc">{{ __('Name: Z to A') }}</option>
                            <option value="rating_desc">{{ __('Highest Rated') }}</option>
                            <option value="newest">{{ __('Newest First') }}</option>
                        </select>
                    </div>
                @endif
            </div>

            {{-- Filters --}}
            @if ($showFilters)
                <div class="bg-white border border-gray-200 rounded-xl p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Refine Results') }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        {{-- Price Range --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Price Range') }}</h4>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <input type="number" x-model="filters.priceMin" placeholder="{{ __('Min') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <span class="text-gray-500">-</span>
                                    <input type="number" x-model="filters.priceMax" placeholder="{{ __('Max') }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        {{-- Categories --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Categories') }}</h4>
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
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Brands') }}</h4>
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

                        {{-- Rating --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Rating') }}</h4>
                            <div class="space-y-2">
                                @for ($i = 5; $i >= 1; $i--)
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                        <input type="radio" x-model="filters.rating" value="{{ $i }}"
                                               class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                        <div class="flex items-center gap-1">
                                            @for ($j = 1; $j <= 5; $j++)
                                                <svg class="w-3 h-3 {{ $j <= $i ? 'text-yellow-400' : 'text-gray-300' }}"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                            <span class="text-sm text-gray-700">{{ $i }}+</span>
                                        </div>
                                    </label>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <button @click="applyFilters()"
                                class="btn-gradient px-6 py-2 rounded-lg font-medium text-sm">
                            {{ __('Apply Filters') }}
                        </button>
                        <button @click="clearFilters()"
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-colors duration-200">
                            {{ __('Clear All') }}
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
                        {{ __('Load More Results') }}
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
                <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('No results found') }}</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    {{ __('Sorry, we couldn\'t find any products matching your search. Try different keywords or browse our categories.') }}
                </p>

                {{-- Search Suggestions --}}
                @if ($showSuggestions)
                    <div class="max-w-2xl mx-auto">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Try searching for') }}:</h4>
                        <div class="flex flex-wrap gap-2 justify-center">
                            @foreach ($suggestions['Popular searches'] as $suggestion)
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
                <h3 class="text-xl font-semibold text-gray-900 mb-6">{{ __('You might also like') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Popular Searches --}}
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">{{ __('Popular Searches') }}</h4>
                        <div class="space-y-2">
                            @foreach ($suggestions['Popular searches'] as $suggestion)
                                <button @click="searchFor('{{ $suggestion }}')"
                                        class="block w-full text-left px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-blue-600 rounded-lg transition-colors duration-200">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Categories --}}
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3">{{ __('Browse Categories') }}</h4>
                        <div class="space-y-2">
                            @foreach ($suggestions['Categories'] as $category)
                                <a href="{{ localized_route('categories.index') }}"
                                   class="block px-3 py-2 text-sm text-gray-600 hover:bg-white hover:text-blue-600 rounded-lg transition-colors duration-200">
                                    {{ $category }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Recent Searches --}}
                    @if (count($recentSearches) > 0)
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">{{ __('Recent Searches') }}</h4>
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
                brands: [],
                rating: ''
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
                if (this.filters.rating) url.searchParams.set('rating', this.filters.rating);

                window.location.href = url.toString();
            },

            clearFilters() {
                this.filters = {
                    priceMin: '',
                    priceMax: '',
                    categories: [],
                    brands: [],
                    rating: ''
                };

                const url = new URL(window.location);
                url.searchParams.delete('price_min');
                url.searchParams.delete('price_max');
                url.searchParams.delete('categories');
                url.searchParams.delete('brands');
                url.searchParams.delete('rating');

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

@props(['placeholder' => null, 'showSuggestions' => true])

@php
    $placeholder = $placeholder ?? __('search_placeholder');
@endphp

<div class="relative" x-data="searchBar()">
    <form @submit.prevent="search" role="search" aria-label="{{ __('Search products') }}">
        <div class="relative group">
            <input
                   type="search"
                   x-model="query"
                   @input.debounce.300ms="getSuggestions"
                   @focus="showSuggestions = true"
                   @blur="setTimeout(() => showSuggestions = false, 200)"
                   placeholder="{{ $placeholder }}"
                   class="block w-full rounded-xl border border-gray-200 bg-white/80 backdrop-blur-sm pl-12 pr-12 py-3 text-sm placeholder:text-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all duration-200 shadow-soft focus:shadow-medium"
                   aria-label="{{ $placeholder }}"
                   autocomplete="off"
                   spellcheck="false">

            {{-- Search Icon --}}
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 group-focus-within:text-blue-500 transition-colors duration-200"
                 aria-hidden="true">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            {{-- Search Button --}}
            <button type="submit"
                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-blue-600 transition-colors duration-200"
                    aria-label="{{ __('Search') }}">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z"
                          clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </form>

    {{-- Search Suggestions --}}
    @if ($showSuggestions)
        <div x-show="showSuggestions && (suggestions.length > 0 || recentSearches.length > 0)"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-large border border-gray-200 py-2 z-50"
             x-cloak>

            {{-- Search Suggestions --}}
            <template x-if="suggestions.length > 0">
                <div>
                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ __('Suggestions') }}
                    </div>
                    <template x-for="suggestion in suggestions" :key="suggestion.id">
                        <a :href="`/search?q=${suggestion.name}`"
                           class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors duration-200">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span class="text-sm text-gray-700" x-text="suggestion.name"></span>
                        </a>
                    </template>
                </div>
            </template>

            {{-- Recent Searches --}}
            <template x-if="suggestions.length === 0 && recentSearches.length > 0">
                <div>
                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        {{ __('Recent Searches') }}
                    </div>
                    <template x-for="search in recentSearches" :key="search">
                        <button @click="query = search; search()"
                                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors duration-200 text-left">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm text-gray-700" x-text="search"></span>
                        </button>
                    </template>
                </div>
            </template>

            {{-- No Results --}}
            <template x-if="suggestions.length === 0 && recentSearches.length === 0 && query.length > 2">
                <div class="px-4 py-3 text-sm text-gray-500 text-center">
                    {{ __('No suggestions found') }}
                </div>
            </template>
        </div>
    @endif
</div>

<script>
    function searchBar() {
        return {
            query: '',
            suggestions: [],
            recentSearches: JSON.parse(localStorage.getItem('recentSearches') || '[]'),
            showSuggestions: false,
            loading: false,

            async getSuggestions() {
                if (this.query.length < 2) {
                    this.suggestions = [];
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(this.query)}`);
                    const data = await response.json();
                    this.suggestions = data.suggestions || [];
                } catch (error) {
                    console.error('Error fetching suggestions:', error);
                    this.suggestions = [];
                } finally {
                    this.loading = false;
                }
            },

            search() {
                if (!this.query.trim()) return;

                // Add to recent searches
                this.addToRecentSearches(this.query);

                // Navigate to search results
                window.location.href = `/search?q=${encodeURIComponent(this.query)}`;
            },

            addToRecentSearches(searchTerm) {
                const searches = this.recentSearches.filter(s => s !== searchTerm);
                searches.unshift(searchTerm);
                this.recentSearches = searches.slice(0, 5); // Keep only 5 recent searches
                localStorage.setItem('recentSearches', JSON.stringify(this.recentSearches));
            }
        }
    }
</script>


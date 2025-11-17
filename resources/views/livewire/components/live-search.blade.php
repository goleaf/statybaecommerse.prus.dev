@php
    foreach ($results as $index => $resultItem) {
        if (is_iterable($resultItem)) {
            foreach ($resultItem as $field => $value) {
                if ($value instanceof \Closure) {
                    logger()->error('Live search result contains closure.', ['index' => $index, 'field' => $field]);
                }
            }
        }
    }

    foreach ($suggestions as $index => $suggestionItem) {
        if (is_iterable($suggestionItem)) {
            foreach ($suggestionItem as $field => $value) {
                if ($value instanceof \Closure) {
                    logger()->error('Live search suggestion contains closure.', ['index' => $index, 'field' => $field]);
                }
            }
        }
    }
@endphp

<div
    class="relative"
    style="position: relative; z-index: 9998;"
    x-data="createDesktopSearchComponent({
        entangle: {
            showResults: @entangle('showResults'),
            showSuggestions: @entangle('showSuggestions'),
            query: @entangle('query'),
            isSearching: @entangle('isSearching'),
            results: @entangle('results'),
            suggestions: @entangle('suggestions'),
        },
        minQueryLength: {{ $minQueryLength }},
    })"
    x-on:keydown="handleKeydown($event)"
    x-on:click.outside="typeof closeDropdowns === 'function' && closeDropdowns()"
>
    {{-- Search Input --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-5 h-5 text-sage/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        
        <input
            wire:model.live.debounce.300ms="query"
            type="text"
            placeholder="{{ __('frontend.search.placeholder') }}"
            class="block w-full pl-10 pr-4 py-3 border border-sage/30 rounded-lg bg-dark text-sage placeholder:text-sage/50 focus:outline-none focus:ring-2 focus:ring-sage/50 focus:border-sage/60 transition-all duration-200"
            autocomplete="off"
            x-ref="searchInput"
        />
        
        {{-- Loading Spinner --}}
        <div wire:loading class="absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="animate-spin h-5 w-5 text-sage/70" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        {{-- Clear Button --}}
        <div wire:loading.remove class="absolute inset-y-0 right-0 flex items-center pr-3">
            <button
                wire:click="clearQuery"
                wire:confirm="{{ __('translations.confirm_clear_search_query') }}"
                x-show="query.length > 0"
                type="button"
                class="text-sage/70 hover:text-white hover:bg-sage/10 focus:outline-none transition-all duration-200 rounded p-1"
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
        class="absolute w-full mt-2 bg-dark border border-sage/30 rounded-lg shadow-2xl max-h-96 overflow-y-auto"
        style="z-index: 9999; position: absolute;"
    >
        @if($isSearching)
            {{-- Loading State --}}
            <div class="flex items-center justify-center py-8">
                <div class="flex items-center space-x-2 text-sage/70">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ __('frontend.search.searching') }}</span>
                </div>
            </div>
        @elseif(count($results) > 0)
            {{-- Search Results --}}
            <div class="py-2">
                @foreach($results as $result)
                    <button
                        wire:click="selectResult({{ json_encode($result) }})"
                        class="w-full px-4 py-3 text-left hover:bg-sage/20 hover:text-white focus:bg-sage/20 focus:text-white focus:outline-none transition-all duration-200 ease-in-out group"
                    >
                        <div class="flex items-center space-x-3">
                            {{-- Result Image --}}
                            <div class="flex-shrink-0">
                                {{-- Blend modern and legacy image keys for live search suggestions. --}}
                                @php
                                    $image = $result['main_image'] ?? $result['thumbnail'] ?? ($result['image'] ?? null);
                                @endphp
                                @if($image)
                                    <img
                                        src="{{ $image }}"
                                        alt="{{ $result['title'] }}"
                                        class="w-12 h-12 object-cover rounded-lg"
                                    />
                                @else
                                    <div class="w-12 h-12 bg-sage/10 rounded-lg flex items-center justify-center">
                                        @if($result['type'] === 'product')
                                            <svg class="w-6 h-6 text-sage/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        @elseif($result['type'] === 'category')
                                            <svg class="w-6 h-6 text-sage/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        @elseif($result['type'] === 'brand')
                                            <svg class="w-6 h-6 text-sage/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Result Content --}}
                            <div class="flex-1 min-w-0">
                                @php
                                    $titleHtml = $result['highlighted_title'] ?? e($result['title'] ?? '');
                                    $subtitleHtml = $result['highlighted_subtitle'] ?? e($result['subtitle'] ?? '');
                                    $descriptionText = $result['description'] ?? null;
                                    $descriptionPreview = $descriptionText !== null ? Str::limit($descriptionText, 60) : null;
                                @endphp

                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-sage group-hover:text-white truncate transition-colors duration-200">
                                        {!! $titleHtml !!}
                                    </h3>
                                    @if(isset($result['formatted_price']))
                                        <span class="text-sm font-semibold text-sage group-hover:text-white/90 transition-colors duration-200">
                                            {{ $result['formatted_price'] }}
                                        </span>
                                    @endif
                                </div>

                                @if(! empty($result['subtitle']))
                                    <p class="text-sm text-sage/70 group-hover:text-sage/90 truncate transition-colors duration-200">
                                        {!! $subtitleHtml !!}
                                    </p>
                                @endif

                                @if($descriptionPreview)
                                    <p class="text-xs text-sage/50 group-hover:text-sage/80 truncate transition-colors duration-200">
                                        {{ $descriptionPreview }}
                                    </p>
                                @endif
                                
                                {{-- Type Badge --}}
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($result['type'] === 'product') bg-sage/20 text-sage
                                        @elseif($result['type'] === 'category') bg-sage/20 text-sage
                                        @elseif($result['type'] === 'brand') bg-sage/20 text-sage
                                        @endif
                                    ">
                                        {{ __('frontend.search.type_' . $result['type']) }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Arrow Icon --}}
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-sage/70 group-hover:text-white group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
            
            {{-- View All Results Link --}}
            <div class="border-t border-sage/30">
                    <a 
                    href="{{ route('localized.search', ['locale' => app()->getLocale(), 'q' => $query]) }}"
                    class="block w-full px-4 py-3 text-center text-sm font-medium text-sage hover:bg-sage/20 hover:text-white focus:bg-sage/20 focus:text-white focus:outline-none transition-all duration-200 ease-in-out"
                >
                    {{ __('frontend.search.view_all_results') }}
                </a>
            </div>
        @elseif(strlen($query) >= $minQueryLength)
            {{-- No Results --}}
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-sage/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-sage">
                    {{ __('frontend.search.no_results') }}
                </h3>
                <p class="mt-1 text-sm text-sage/70">
                    {{ __('frontend.search.try_different_keywords') }}
                </p>
            </div>
        @endif
    </div>

    {{-- Suggestions Dropdown --}}
    <div 
        x-show="showSuggestions && (query.length < {{ $minQueryLength }} || query.length === 0)"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute w-full mt-2 bg-dark border border-sage/30 rounded-lg shadow-2xl max-h-96 overflow-y-auto"
        style="z-index: 9999; position: absolute;"
    >
        @if(count($suggestions) > 0)
            {{-- Suggestions Header --}}
            <div class="px-4 py-2 border-b border-sage/30">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-sage">
                        {{ __('frontend.search.suggestions') }}
                    </h3>
                    @if($enableRecentSearches)
                        <button
                            wire:click="clearRecentSearches"
                            wire:confirm="{{ __('translations.confirm_clear_recent_searches') }}"
                            type="button"
                            class="text-xs text-sage/70 hover:text-white hover:underline transition-all duration-200"
                        >
                            {{ __('frontend.search.clear_recent') }}
                        </button>
                    @endif
                </div>
            </div>
            
            {{-- Suggestions List --}}
            <div class="py-2">
                @foreach($suggestions as $index => $suggestion)
                    <button
                        wire:click="selectSuggestion({{ json_encode($suggestion) }})"
                        class="w-full px-4 py-3 text-left hover:bg-sage/20 hover:text-white focus:bg-sage/20 focus:text-white focus:outline-none transition-all duration-200 ease-in-out group"
                        :class="{ 'bg-sage/20 text-white': selectedIndex === {{ $index }} }"
                    >
                        <div class="flex items-center space-x-3">
                            {{-- Suggestion Icon --}}
                            <div class="flex-shrink-0">
                                @if(isset($suggestion['is_recent']))
                                    <svg class="w-5 h-5 text-sage/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @elseif(isset($suggestion['is_popular']))
                                    <svg class="w-5 h-5 text-sage/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-sage/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                @endif
                            </div>
                            
                            {{-- Suggestion Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-sage group-hover:text-white truncate transition-colors duration-200">
                                        {{ $suggestion['title'] ?? $suggestion['search_term'] ?? '' }}
                                    </h3>
                                    @if(isset($suggestion['is_recent']))
                                        <span class="text-xs text-sage/70">
                                            {{ __('frontend.search.recent') }}
                                        </span>
                                    @elseif(isset($suggestion['is_popular']))
                                        <span class="text-xs text-sage/70">
                                            {{ __('frontend.search.popular') }}
                                        </span>
                                    @endif
                                </div>
                                
                                @if(!empty($suggestion['subtitle']))
                                    <p class="text-sm text-sage/70 group-hover:text-sage/90 truncate transition-colors duration-200">
                                        {{ $suggestion['subtitle'] }}
                                    </p>
                                @endif
                            </div>
                            
                            {{-- Arrow Icon --}}
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-sage/70 group-hover:text-white group-hover:translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
            
            {{-- Search Tips --}}
            <div class="border-t border-sage/30 px-4 py-3">
                <div class="text-xs text-sage/70">
                    <div class="flex items-center space-x-4">
                        <span>{{ __('frontend.search.tip_1') }}</span>
                        <span>{{ __('frontend.search.tip_2') }}</span>
                    </div>
                </div>
            </div>
        @else
            {{-- No Suggestions --}}
            <div class="px-4 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-sage/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-sage">
                    {{ __('frontend.search.no_suggestions') }}
                </h3>
                <p class="mt-1 text-sm text-sage/70">
                    {{ __('frontend.search.start_typing') }}
                </p>
            </div>
        @endif
    </div>
</div>

<div class="mobile-autocomplete relative" 
     x-data="{
         showResults: @entangle('showResults'),
         showSuggestions: @entangle('showSuggestions'),
         query: @entangle('query'),
         isSearching: @entangle('isSearching'),
         selectedIndex: -1,
         results: @entangle('results'),
         suggestions: @entangle('suggestions'),
         isFullScreen: @entangle('isFullScreen')
     }" 
     x-init="
         $watch('query', value => {
             if (value.length < {{ $minQueryLength }}) {
                 showResults = false;
                 if (value.length === 0) {
                     showSuggestions = true;
                 }
             }
         });
         
         // Close results when clicking outside
         $el.addEventListener('clickoutside', () => {
             showResults = false;
             showSuggestions = false;
             selectedIndex = -1;
         });
         
         // Keyboard navigation
         $el.addEventListener('keydown', (e) => {
             const totalItems = showResults ? results.length : suggestions.length;

             if (e.key === 'ArrowDown') {
                 e.preventDefault();
                 selectedIndex = Math.min(selectedIndex + 1, totalItems - 1);
             } else if (e.key === 'ArrowUp') {
                 e.preventDefault();
                 selectedIndex = Math.max(selectedIndex - 1, -1);
             } else if (e.key === 'Enter') {
                 e.preventDefault();
                 if (selectedIndex >= 0) {
                     if (showResults) {
                         $wire.selectResult(results[selectedIndex]);
                     } else if (showSuggestions) {
                         $wire.selectSuggestion(suggestions[selectedIndex]);
                     }
                 }
             } else if (e.key === 'Escape') {
                 showResults = false;
                 showSuggestions = false;
                 selectedIndex = -1;
                 isFullScreen = false;
             }
         });
     ">
    
    {{-- Mobile Search Input --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        
        <input
            wire:model.live.debounce.300ms="query"
            type="text"
            placeholder="{{ __('frontend.search.mobile_placeholder') }}"
            class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            autocomplete="off"
            x-ref="searchInput"
            @focus="isFullScreen = true"
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
                wire:click="clearQuery"
                wire:confirm="{{ __('translations.confirm_clear_search_query') }}"
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
    
    {{-- Full Screen Overlay --}}
    <div x-show="isFullScreen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 bg-white">
        
        {{-- Full Screen Header --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                
                <input
                    wire:model.live.debounce.300ms="query"
                    type="text"
                    placeholder="{{ __('frontend.search.mobile_placeholder') }}"
                    class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    autocomplete="off"
                    x-ref="fullScreenInput"
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
                        wire:click="clearQuery"
                        wire:confirm="{{ __('translations.confirm_clear_search_query') }}"
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
            
            <button
                @click="isFullScreen = false"
                type="button"
                class="ml-3 text-gray-500 hover:text-gray-700 focus:outline-none"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        {{-- Full Screen Content --}}
        <div class="flex-1 overflow-y-auto">
            {{-- Results or Suggestions --}}
            <div x-show="showResults && results.length > 0" class="p-4">
                <div class="space-y-3">
                    <template x-for="(result, index) in results" :key="result.id">
                        <button
                            @click="$wire.selectResult(result)"
                            class="w-full text-left p-3 rounded-lg border border-gray-200 hover:bg-gray-50 focus:bg-gray-50 focus:outline-none"
                            :class="{ 'bg-gray-50': selectedIndex === index }"
                        >
                            <div class="flex items-center space-x-3">
                                {{-- Result Image --}}
                                <div class="flex-shrink-0">
                                    <img x-show="result.image" 
                                         :src="result.image" 
                                         :alt="result.title"
                                         class="w-12 h-12 rounded-lg object-cover bg-gray-100" />
                                    <div x-show="!result.image" 
                                         class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                
                                {{-- Result Content --}}
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-medium text-gray-900 truncate" x-text="result.title"></h3>
                                    <p class="text-sm text-gray-500 truncate" x-text="result.subtitle"></p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full" x-text="result.type"></span>
                                        <span x-show="result.formatted_price" class="text-sm font-medium text-green-600" x-text="result.formatted_price"></span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
            
            {{-- Suggestions --}}
            <div x-show="showSuggestions && suggestions.length > 0" class="p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('frontend.search.suggestions') }}</h3>
                <div class="space-y-2">
                    <template x-for="(suggestion, index) in suggestions" :key="index">
                        <button
                            @click="$wire.selectSuggestion(suggestion)"
                            class="w-full text-left p-3 rounded-lg hover:bg-gray-50 focus:bg-gray-50 focus:outline-none"
                            :class="{ 'bg-gray-50': selectedIndex === index }"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-medium text-gray-900 truncate" x-text="suggestion.title"></h3>
                                    <p x-show="suggestion.subtitle" class="text-sm text-gray-500 truncate" x-text="suggestion.subtitle"></p>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
            
            {{-- No Results --}}
            <div x-show="showResults && results.length === 0 && !isSearching" class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('frontend.search.no_results') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('frontend.search.try_different_keywords') }}</p>
            </div>
        </div>
    </div>
    
    {{-- Compact Results Dropdown (when not full screen) --}}
    <div x-show="showResults && results.length > 0 && !isFullScreen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak
         class="absolute z-40 w-full mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto">
        
        <div class="py-2">
            <template x-for="(result, index) in results.slice(0, 3)" :key="result.id">
                <button
                    @click="$wire.selectResult(result)"
                    class="w-full px-4 py-3 text-left hover:bg-gray-50 focus:bg-gray-50 focus:outline-none"
                    :class="{ 'bg-gray-50': selectedIndex === index }"
                >
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <img x-show="result.image" 
                                 :src="result.image" 
                                 :alt="result.title"
                                 class="w-8 h-8 rounded object-cover bg-gray-100" />
                            <div x-show="!result.image" 
                                 class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium text-gray-900 truncate" x-text="result.title"></h3>
                            <p class="text-sm text-gray-500 truncate" x-text="result.subtitle"></p>
                        </div>
                        
                        <div class="flex-shrink-0">
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full" x-text="result.type"></span>
                        </div>
                    </div>
                </button>
            </template>
            
            {{-- View All Button --}}
            <div class="border-t border-gray-200">
                <button
                    @click="isFullScreen = true"
                    class="w-full px-4 py-3 text-center text-sm text-blue-600 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none"
                >
                    {{ __('frontend.search.view_all_results') }}
                </button>
            </div>
        </div>
    </div>
</div>

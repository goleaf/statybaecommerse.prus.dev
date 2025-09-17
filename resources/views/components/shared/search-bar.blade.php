@props([
    'placeholder' => null,
    'showAdvanced' => false,
    'showFilters' => false,
    'categories' => [],
    'brands' => [],
])

<div class="relative" x-data="{ showAdvanced: false }">
    {{-- Basic Search Bar --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        
        <input
            wire:model.live.debounce.300ms="searchQuery"
            type="search"
            placeholder="{{ $placeholder ?? __('shared.search_placeholder') }}"
            class="block w-full rounded-lg border-gray-300 bg-white pl-10 pr-12 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
            @keydown.escape="$el.blur()"
        />
        
        @if($showAdvanced)
            <button 
                @click="showAdvanced = !showAdvanced"
                class="absolute inset-y-0 right-0 flex items-center pr-3"
                :class="{ 'text-blue-600': showAdvanced, 'text-gray-400': !showAdvanced }"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                </svg>
            </button>
        @endif
    </div>

    {{-- Advanced Search Filters --}}
    @if($showAdvanced)
        <div 
            x-show="showAdvanced"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute top-full left-0 right-0 z-50 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 p-6 dark:bg-gray-800 dark:border-gray-700"
        >
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if(!empty($categories))
                    <x-shared.select
                        wire:model.live="selectedCategory"
                        label="{{ __('shared.filter_by_category') }}"
                        placeholder="{{ __('shared.all') }}"
                    >
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-shared.select>
                @endif

                @if(!empty($brands))
                    <x-shared.select
                        wire:model.live="selectedBrand"
                        label="{{ __('shared.filter_by_brand') }}"
                        placeholder="{{ __('shared.all') }}"
                    >
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </x-shared.select>
                @endif

                <x-shared.select
                    wire:model.live="sortBy"
                    label="{{ __('shared.sort_by') }}"
                >
                    <option value="relevance">{{ __('Relevance') }}</option>
                    <option value="created_at">{{ __('shared.sort_newest') }}</option>
                    <option value="name">{{ __('shared.sort_name_az') }}</option>
                    <option value="price">{{ __('shared.sort_price_low') }}</option>
                </x-shared.select>
            </div>

            <div class="mt-4 flex justify-between">
                <x-shared.button 
                    @click="showAdvanced = false"
                    variant="secondary"
                    size="sm"
                >
                    {{ __('shared.close') }}
                </x-shared.button>
                
                <div class="flex gap-2">
                    <x-shared.button 
                        wire:click="clearFilters"
                        wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
                        variant="secondary"
                        size="sm"
                    >
                        {{ __('shared.clear') }}
                    </x-shared.button>
                    
                    <x-shared.button 
                        wire:click="search"
                        variant="primary"
                        size="sm"
                    >
                        {{ __('shared.search') }}
                    </x-shared.button>
                </div>
            </div>
        </div>
    @endif
</div>
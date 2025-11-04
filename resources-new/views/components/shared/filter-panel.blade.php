@props([
    'categories' => [],
    'brands' => [],
    'attributes' => [],
    'showSearch' => true,
    'showPriceFilter' => true,
    'showStockFilter' => true,
    'showSaleFilter' => true,
])

<x-shared.card class="mb-8">
    <x-slot name="header">
        <div class="flex items-center">
            <svg class="h-6 w-6 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('shared.filter_by_category') }}</h2>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        {{-- Search Filter --}}
        @if($showSearch)
            <x-shared.input
                wire:model.live.debounce.300ms="search"
                type="search"
                label="{{ __('shared.search') }}"
                placeholder="{{ __('shared.search_placeholder') }}"
                icon="heroicon-o-magnifying-glass"
            />
        @endif

        {{-- Category Filter --}}
        @if(!empty($categories))
            <x-shared.select
                wire:model.live="selectedCategories"
                label="{{ __('shared.filter_by_category') }}"
                placeholder="{{ __('shared.all') }}"
                multiple
            >
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-shared.select>
        @endif

        {{-- Brand Filter --}}
        @if(!empty($brands))
            <x-shared.select
                wire:model.live="selectedBrands"
                label="{{ __('shared.filter_by_brand') }}"
                placeholder="{{ __('shared.all') }}"
                multiple
            >
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </x-shared.select>
        @endif

        {{-- Sort Options --}}
        <x-shared.select
            wire:model.live="sortBy"
            label="{{ __('shared.sort_by') }}"
        >
            <option value="created_at">{{ __('shared.sort_newest') }}</option>
            <option value="name">{{ __('shared.sort_name_az') }}</option>
            <option value="price">{{ __('shared.sort_price_low') }}</option>
            <option value="popularity">{{ __('shared.sort_popularity') }}</option>
            <option value="rating">{{ __('shared.sort_rating') }}</option>
        </x-shared.select>
    </div>

    {{-- Advanced Filters --}}
    @if($showPriceFilter || $showStockFilter || $showSaleFilter)
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Price Range --}}
            @if($showPriceFilter)
                <div class="space-y-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('shared.price_range') }}</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <x-shared.input
                            wire:model.live="minPrice"
                            type="number"
                            placeholder="{{ __('shared.min_price') }}"
                            step="0.01"
                            min="0"
                        />
                        <x-shared.input
                            wire:model.live="maxPrice"
                            type="number"
                            placeholder="{{ __('shared.max_price') }}"
                            step="0.01"
                            min="0"
                        />
                    </div>
                </div>
            @endif

            {{-- Stock Filter --}}
            @if($showStockFilter)
                <div class="space-y-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('shared.in_stock') }}</h3>
                    <label class="flex items-center">
                        <input 
                            wire:model.live="inStock"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('shared.in_stock') }}</span>
                    </label>
                </div>
            @endif

            {{-- Sale Filter --}}
            @if($showSaleFilter)
                <div class="space-y-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('shared.sale') }}</h3>
                    <label class="flex items-center">
                        <input 
                            wire:model.live="onSale"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('shared.sale') }}</span>
                    </label>
                </div>
            @endif
        </div>
    @endif

    {{-- Filter Actions --}}
    <div class="mt-6 flex gap-4">
        <x-shared.button 
            wire:click="clearFilters"
            wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
            variant="secondary"
            icon="heroicon-o-x-mark"
            size="sm"
        >
            {{ __('shared.clear_filters') }}
        </x-shared.button>
        
        <x-shared.button 
            wire:click="applyFilters"
            variant="primary"
            icon="heroicon-o-funnel"
            size="sm"
        >
            {{ __('shared.apply_filters') }}
        </x-shared.button>
    </div>
</x-shared.card>

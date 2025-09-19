@props([
    'categories' => [],
    'brands' => [],
    'showSearch' => true,
    'showCategory' => true,
    'showBrand' => true,
    'showSort' => true,
    'showPriceRange' => true,
    'showClearFilters' => true,
    'showApplyFilters' => true,
    'collapsible' => true,
    'defaultOpen' => true,
])

<div class="space-y-6">
    {{-- Search Section --}}
    @if($showSearch)
        <x-shared.card padding="p-4">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="font-semibold text-gray-900">{{ __('shared.search') }}</h3>
                </div>
            </x-slot>
            
            <x-shared.input 
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="{{ __('Search products...') }}"
                class="w-full"
            />
        </x-shared.card>
    @endif

    {{-- Category Filter --}}
    @if($showCategory && !empty($categories))
        <x-shared.card padding="p-4">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <h3 class="font-semibold text-gray-900">{{ __('Category') }}</h3>
                </div>
            </x-slot>
            
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($categories as $category)
                    <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input 
                            type="checkbox" 
                            wire:model.live="selectedCategories" 
                            value="{{ $category->id }}"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="text-sm text-gray-700">{{ $category->name }}</span>
                        <span class="text-xs text-gray-500 ml-auto">({{ $category->products_count ?? 0 }})</span>
                    </label>
                @endforeach
            </div>
        </x-shared.card>
    @endif

    {{-- Brand Filter --}}
    @if($showBrand && !empty($brands))
        <x-shared.card padding="p-4">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <h3 class="font-semibold text-gray-900">{{ __('Brand') }}</h3>
                </div>
            </x-slot>
            
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($brands as $brand)
                    <label class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input 
                            type="checkbox" 
                            wire:model.live="selectedBrands" 
                            value="{{ $brand->id }}"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <span class="text-sm text-gray-700">{{ $brand->name }}</span>
                        <span class="text-xs text-gray-500 ml-auto">({{ $brand->products_count ?? 0 }})</span>
                    </label>
                @endforeach
            </div>
        </x-shared.card>
    @endif

    {{-- Price Range Filter --}}
    @if($showPriceRange)
        <x-shared.card padding="p-4">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <h3 class="font-semibold text-gray-900">{{ __('Price Range') }}</h3>
                </div>
            </x-slot>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <x-shared.input 
                        wire:model.live="minPrice"
                        type="number"
                        placeholder="{{ __('Min') }}"
                        min="0"
                        step="0.01"
                        class="text-sm"
                    />
                    <x-shared.input 
                        wire:model.live="maxPrice"
                        type="number"
                        placeholder="{{ __('Max') }}"
                        min="0"
                        step="0.01"
                        class="text-sm"
                    />
                </div>
                
                {{-- Price Range Slider --}}
                <div class="space-y-2">
                    <input 
                        type="range" 
                        wire:model.live="priceRange"
                        min="0" 
                        max="1000" 
                        step="10"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
                    >
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>€0</span>
                        <span>€1000+</span>
                    </div>
                </div>
            </div>
        </x-shared.card>
    @endif

    {{-- Sort Options --}}
    @if($showSort)
        <x-shared.card padding="p-4">
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                    </svg>
                    <h3 class="font-semibold text-gray-900">{{ __('Sort By') }}</h3>
                </div>
            </x-slot>
            
            <x-shared.select 
                wire:model.live="sortBy"
                class="w-full"
            >
                <option value="created_at">{{ __('Newest') }}</option>
                <option value="name">{{ __('Name A-Z') }}</option>
                <option value="name_desc">{{ __('Name Z-A') }}</option>
                <option value="price">{{ __('Price: Low to High') }}</option>
                <option value="price_desc">{{ __('Price: High to Low') }}</option>
                <option value="popularity">{{ __('Most Popular') }}</option>
                <option value="rating">{{ __('Highest Rated') }}</option>
            </x-shared.select>
        </x-shared.card>
    @endif

    {{-- Filter Actions --}}
    @if($showClearFilters || $showApplyFilters)
        <x-shared.card padding="p-4">
            <div class="space-y-3">
                @if($showApplyFilters)
                    <x-shared.button 
                        wire:click="applyFilters"
                        variant="primary"
                        icon="heroicon-o-funnel"
                        class="w-full"
                        size="sm"
                    >
                        {{ __('Apply Filters') }}
                    </x-shared.button>
                @endif
                
                @if($showClearFilters)
                    <x-shared.button 
                        wire:click="clearFilters"
                        wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
                        variant="secondary"
                        icon="heroicon-o-x-mark"
                        class="w-full"
                        size="sm"
                    >
                        {{ __('Clear Filters') }}
                    </x-shared.button>
                @endif
            </div>
        </x-shared.card>
    @endif
</div>



@props([
    'categories' => [],
    'brands' => [],
    'showSearch' => true,
    'showCategory' => true,
    'showBrand' => true,
    'showSort' => true,
    'showPriceRange' => false,
])

<x-shared.card padding="p-6">
    <x-slot name="header">
        <x-shared.section 
            title="{{ __('frontend.filters.products_title') }}"
            description="{{ __('messages.frontend') }}"
            icon="heroicon-o-funnel"
            titleSize="text-xl"
            centered="false"
        />
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @if($showSearch)
            {{-- Search Input --}}
            <x-shared.input 
                wire:model.live.debounce.300ms="search"
                type="search"
                label="{{ __('messages.shared') }}"
                placeholder="{{ __('frontend.filters.search_placeholder') }}"
                icon="heroicon-o-magnifying-glass"
            />
        @endif

        @if($showCategory && !empty($categories))
            {{-- Category Filter --}}
            <x-shared.select 
                wire:model.live="categoryId"
                label="{{ __('messages.frontend') }}"
                placeholder="{{ __('frontend.filters.all_categories') }}"
            >
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-shared.select>
        @endif

        @if($showBrand && !empty($brands))
            {{-- Brand Filter --}}
            <x-shared.select 
                wire:model.live="brandId"
                label="{{ __('messages.frontend') }}"
                placeholder="{{ __('frontend.filters.all_brands') }}"
            >
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </x-shared.select>
        @endif

        @if($showSort)
            {{-- Sort Options --}}
            <x-shared.select 
                wire:model.live="sortBy"
                label="{{ __('frontend.filters.sort_by') }}"
            >
                <option value="created_at">{{ __('messages.frontend') }}</option>
                <option value="name">{{ __('messages.frontend') }}</option>
                <option value="price">{{ __('messages.frontend') }}</option>
                <option value="popularity">{{ __('messages.frontend') }}</option>
                <option value="rating">{{ __('messages.frontend') }}</option>
            </x-shared.select>
        @endif
    </div>

    @if($showPriceRange)
        {{-- Price Range --}}
        <div class="mt-6 grid grid-cols-2 gap-4">
            <x-shared.input 
                wire:model.live="minPrice"
                type="number"
                label="{{ __('frontend.filters.min_price') }}"
                placeholder="0"
                min="0"
                step="0.01"
            />
            <x-shared.input 
                wire:model.live="maxPrice"
                type="number"
                label="{{ __('frontend.filters.max_price') }}"
                placeholder="1000"
                min="0"
                step="0.01"
            />
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
            {{ __('messages.frontend') }}
        </x-shared.button>
        
        <x-shared.button 
            wire:click="applyFilters"
            variant="primary"
            icon="heroicon-o-funnel"
            size="sm"
        >
            {{ __('messages.frontend') }}
    </x-shared.button>
    </div>
</x-shared.card>

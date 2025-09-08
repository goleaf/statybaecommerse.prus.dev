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
            title="{{ __('shared.filter') }} {{ __('Products') }}"
            description="{{ __('Narrow down your search to find exactly what you need') }}"
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
                label="{{ __('shared.search') }}"
                placeholder="{{ __('Search products...') }}"
                icon="heroicon-o-magnifying-glass"
            />
        @endif

        @if($showCategory && !empty($categories))
            {{-- Category Filter --}}
            <x-shared.select 
                wire:model.live="categoryId"
                label="{{ __('Category') }}"
                placeholder="{{ __('All Categories') }}"
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
                label="{{ __('Brand') }}"
                placeholder="{{ __('All Brands') }}"
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
                label="{{ __('shared.sort') }} {{ __('By') }}"
            >
                <option value="created_at">{{ __('Newest') }}</option>
                <option value="name">{{ __('Name') }}</option>
                <option value="price">{{ __('Price') }}</option>
                <option value="popularity">{{ __('Popularity') }}</option>
                <option value="rating">{{ __('Rating') }}</option>
            </x-shared.select>
        @endif
    </div>

    @if($showPriceRange)
        {{-- Price Range --}}
        <div class="mt-6 grid grid-cols-2 gap-4">
            <x-shared.input 
                wire:model.live="minPrice"
                type="number"
                label="{{ __('Min Price') }}"
                placeholder="0"
                min="0"
                step="0.01"
            />
            <x-shared.input 
                wire:model.live="maxPrice"
                type="number"
                label="{{ __('Max Price') }}"
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
            variant="secondary"
            icon="heroicon-o-x-mark"
            size="sm"
        >
            {{ __('shared.clear') }} {{ __('shared.filter') }}s
        </x-shared.button>
        
        <x-shared.button 
            wire:click="applyFilters"
            variant="primary"
            icon="heroicon-o-funnel"
            size="sm"
        >
            {{ __('shared.apply') }} {{ __('shared.filter') }}s
        </x-shared.button>
    </div>
</x-shared.card>

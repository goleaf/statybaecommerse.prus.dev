@php
    $variant = $variant ?? 'desktop';
    $showSearch = $showSearch ?? true;
    $cardPadding = $variant === 'mobile' ? 'p-5' : 'p-6';
    $cardShadow = $variant === 'mobile' ? 'shadow-lg' : 'shadow-sm';
    $summaryPaddingX = $variant === 'mobile' ? 'px-5' : 'px-6';
    $summaryPaddingY = $variant === 'mobile' ? 'py-3' : 'py-4';
    $sectionSpacing = $variant === 'mobile' ? 'space-y-4' : 'space-y-5';
    $brandsContentClass = $variant === 'desktop'
        ? "{$summaryPaddingX} pb-5 {$sectionSpacing}"
        : "{$summaryPaddingX} pb-5 {$sectionSpacing} max-h-56 overflow-y-auto";
    $collectionsContentClass = $variant === 'desktop'
        ? "{$summaryPaddingX} pb-5 {$sectionSpacing}"
        : "{$summaryPaddingX} pb-5 {$sectionSpacing} max-h-56 overflow-y-auto";
    $clearFiltersUrl = \Illuminate\Support\Facades\Route::has('localized.categories.index')
        ? route('localized.categories.index')
        : (\Illuminate\Support\Facades\Route::has('frontend.categories.index')
            ? route('frontend.categories.index')
            : url('/categories'));
@endphp

<div class="border border-dark/25 bg-white/60 {{ $cardShadow }} {{ $cardPadding }} space-y-4">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-xs font-semibold uppercase tracking-[0.28em] text-dark/70">
            {{ __('messages.refine_results') }}
        </h3>
        <a href="{{ $clearFiltersUrl }}"
           class="text-xs font-semibold text-dark underline decoration-dark/30 underline-offset-4 transition-colors hover:decoration-dark">
            {{ __('messages.clear_all') }}
        </a>
    </div>

    @if ($showSearch)
        <div class="flex flex-col gap-3">
            <label for="category-search-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-dark/60">
                {{ __('messages.search_categories') }}
            </label>
            <div class="flex items-center gap-2 overflow-hidden border border-dark/20 bg-sage/40 focus-within:border-dark focus-within:ring-2 focus-within:ring-dark/10">
                <span class="px-3 text-dark/45">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                </span>
                <input id="category-search-{{ $variant }}"
                       type="search"
                       wire:model.live.debounce.400ms="search"
                       placeholder="{{ __('messages.type_to_filter_categories') }}"
                       class="w-full border-0 bg-transparent py-2 pr-3 text-sm text-dark placeholder:text-dark/45 focus:outline-none focus:ring-0" />
            </div>
        </div>
    @else
        <p class="text-sm leading-6 text-dark/70">
            {{ __('categories.index.filters_description') }}
        </p>
    @endif
</div>

<div class="border border-dark/25 bg-white/60 {{ $cardShadow }} {{ $cardPadding }} space-y-3">
    <h3 class="text-xs font-semibold uppercase tracking-[0.28em] text-dark/70">
        {{ __('messages.availability') }}
    </h3>

    <label class="flex items-center justify-between gap-3 border border-dark/20 bg-sage/40 px-3 py-2 text-sm font-medium text-dark transition-colors hover:border-dark hover:bg-sage/60">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="inStock" class="rounded border-dark/30 text-dark focus:ring-dark/30">
            <span>{{ __('messages.in_stock_only') }}</span>
        </div>
        <span class="text-xs text-dark/55">{{ __('messages.real_time') }}</span>
    </label>

    <label class="flex items-center justify-between gap-3 border border-dark/20 bg-sage/40 px-3 py-2 text-sm font-medium text-dark transition-colors hover:border-dark hover:bg-sage/60">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="onSale" class="rounded border-dark/30 text-dark focus:ring-dark/30">
            <span>{{ __('messages.promotions') }}</span>
        </div>
    </label>

    <label class="flex items-center justify-between gap-3 border border-dark/20 bg-sage/40 px-3 py-2 text-sm font-medium text-dark transition-colors hover:border-dark hover:bg-sage/60">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="hasProducts" class="rounded border-dark/30 text-dark focus:ring-dark/30">
            <span>{{ __('messages.with_active_listings') }}</span>
        </div>
        <span class="text-xs text-dark/55">{{ __('messages.verified_content') }}</span>
    </label>
</div>

<details class="group border border-dark/25 bg-white/60 {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-dark transition-colors hover:text-dark/75">
        <span>{{ __('messages.categories_index_filters_brands') }}</span>
        <svg class="h-4 w-4 text-dark/55 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $brandsContentClass }}">
        <div class="flex flex-col gap-2">
            <label for="brand-search-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-dark/60">
                {{ __('messages.brands_index_search_label') }}
            </label>
            <div class="flex items-center gap-2 overflow-hidden border border-dark/20 bg-sage/40 focus-within:border-dark focus-within:ring-2 focus-within:ring-dark/10">
                <span class="px-3 text-dark/45">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                </span>
                <input id="brand-search-{{ $variant }}"
                       type="search"
                       wire:model.live.debounce.300ms="brandSearch"
                       placeholder="{{ __('messages.brands_index_search_placeholder') }}"
                       class="w-full border-0 bg-transparent py-2 pr-3 text-sm text-dark placeholder:text-dark/45 focus:outline-none focus:ring-0" />
            </div>
        </div>

        @forelse ($this->filteredFacetBrands as $brand)
            <label class="flex items-center justify-between gap-2 border border-dark/20 bg-sage/40 px-3 py-2 text-sm font-medium text-dark transition-colors hover:border-dark hover:bg-sage/60">
                <span class="flex items-center gap-3">
                    <input type="checkbox"
                           value="{{ $brand['id'] }}"
                           wire:model.live="selectedBrandIds"
                           class="rounded border-dark/30 text-dark focus:ring-dark/30">
                    <span>{{ $brand['name'] }}</span>
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center border border-dark/15 bg-white/70 px-2 py-0.5 text-xs font-semibold text-dark">
                    {{ $brand['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-dark/55">{{ __('messages.no_brands_to_filter_yet') }}</p>
        @endforelse
    </div>
</details>

<details class="group border border-dark/25 bg-white/60 {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-dark transition-colors hover:text-dark/75">
        <span>{{ __('messages.categories_index_filters_collections') }}</span>
        <svg class="h-4 w-4 text-dark/55 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $collectionsContentClass }}">
        @forelse ($this->facetCollections as $collection)
            <label class="flex items-center justify-between gap-2 border border-dark/20 bg-sage/40 px-3 py-2 text-sm font-medium text-dark transition-colors hover:border-dark hover:bg-sage/60">
                <span class="flex items-center gap-3">
                    <input type="checkbox"
                           value="{{ $collection['id'] }}"
                           wire:model.live="selectedCollectionIds"
                           class="rounded border-dark/30 text-dark focus:ring-dark/30">
                    <span>{{ $collection['name'] }}</span>
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center border border-dark/15 bg-white/70 px-2 py-0.5 text-xs font-semibold text-dark">
                    {{ $collection['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-dark/55">{{ __('messages.no_collections_available_yet') }}</p>
        @endforelse
    </div>
</details>

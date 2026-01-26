@php
    $variant = $variant ?? 'desktop';
    $cardPadding = $variant === 'mobile' ? 'p-5' : 'p-6';
    $cardShadow = $variant === 'mobile' ? 'shadow-lg' : 'shadow-sm';
    $summaryPaddingX = $variant === 'mobile' ? 'px-5' : 'px-6';
    $summaryPaddingY = $variant === 'mobile' ? 'py-3' : 'py-4';
    $sectionSpacing = $variant === 'mobile' ? 'space-y-4' : 'space-y-5';
@endphp

<div class="rounded-2xl border border-sage/30 bg-dark/50 {{ $cardShadow }} {{ $cardPadding }} space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-sage/60">
            {{ __('messages.refine_results') }}
        </h3>
        <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
           class="text-xs font-semibold text-sage hover:text-white">
            {{ __('messages.clear_all') }}
        </a>
    </div>

    <div class="flex flex-col gap-3">
        <label for="category-search-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-sage/60">
            {{ __('messages.search_categories') }}
        </label>
        <div class="flex items-center gap-2 overflow-hidden rounded-xl border border-sage/30 bg-dark/30 focus-within:border-sage focus-within:ring-2 focus-within:ring-sage/20">
            <span class="px-3 text-sage/60">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
            </span>
            <input id="category-search-{{ $variant }}"
                   type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="{{ __('messages.type_to_filter_categories') }}"
                   class="w-full border-0 bg-transparent py-2 pr-3 text-sm text-sage placeholder:text-sage/50 focus:outline-none focus:ring-0" />
        </div>
    </div>
</div>

<div class="rounded-2xl border border-sage/30 bg-dark/50 {{ $cardShadow }} {{ $cardPadding }} space-y-3">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-sage/60">
        {{ __('messages.availability') }}
    </h3>

    <label class="flex items-center justify-between rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm font-medium text-white transition hover:border-sage hover:bg-sage/10">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="inStock" class="rounded border-sage/30 text-sage focus:ring-sage">
            <span class="text-white">{{ __('messages.in_stock_only') }}</span>
        </div>
        <span class="text-xs text-sage/60">{{ __('messages.real_time') }}</span>
    </label>

    <label class="flex items-center justify-between rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm font-medium text-white transition hover:border-sage hover:bg-sage/10">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="onSale" class="rounded border-sage/30 text-sage focus:ring-sage">
            <span class="text-white">{{ __('messages.promotions') }}</span>
        </div>
        <span class="inline-flex items-center gap-1 rounded-full border border-sage/30 bg-sage px-2 py-0.5 text-[11px] font-semibold text-dark">
            {{ __('messages.hot') }}
        </span>
    </label>

    <label class="flex items-center justify-between rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm font-medium text-white transition hover:border-sage hover:bg-sage/10">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="hasProducts" class="rounded border-sage/30 text-sage focus:ring-sage">
            <span class="text-white">{{ __('messages.with_active_listings') }}</span>
        </div>
        <span class="text-xs text-sage/60">{{ __('messages.verified_content') }}</span>
    </label>
</div>

<div class="rounded-2xl border border-sage/30 bg-dark/50 {{ $cardShadow }} {{ $cardPadding }} space-y-4">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-sage/60">
        {{ __('messages.price_range_eur') }}
    </h3>

    <div class="grid grid-cols-2 gap-3">
        <div class="flex flex-col gap-2">
            <label for="price-min-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-sage/60">
                {{ __('messages.min') }}
            </label>
            <input id="price-min-{{ $variant }}"
                   type="number"
                   min="0"
                   step="0.01"
                   wire:model.live.debounce.500ms="priceMin"
                   placeholder="0.00"
                   class="w-full rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm text-sage placeholder:text-sage/50 focus:border-sage focus:outline-none focus:ring-2 focus:ring-sage/20" />
        </div>
        <div class="flex flex-col gap-2">
            <label for="price-max-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-sage/60">
                {{ __('messages.max') }}
            </label>
            <input id="price-max-{{ $variant }}"
                   type="number"
                   min="0"
                   step="0.01"
                   wire:model.live.debounce.500ms="priceMax"
                   placeholder="2500.00"
                   class="w-full rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm text-sage placeholder:text-sage/50 focus:border-sage focus:outline-none focus:ring-2 focus:ring-sage/20" />
        </div>
    </div>
</div>

<details class="group rounded-2xl border border-sage/30 bg-dark/50 {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-white transition hover:text-sage">
        <span>{{ __('messages.categories_index_filters_brands') }}</span>
        <svg class="h-4 w-4 text-sage/60 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $summaryPaddingX }} pb-5 {{ $sectionSpacing }} max-h-56 overflow-y-auto">
        @forelse ($this->facetBrands as $brand)
            <label class="flex items-center justify-between gap-2 rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm font-medium text-white transition hover:border-sage hover:bg-sage/10">
                <span class="flex items-center gap-3 text-white">
                    <input type="checkbox"
                           value="{{ $brand['id'] }}"
                           wire:model.live="selectedBrandIds"
                           class="rounded border-sage/30 text-sage focus:ring-sage">
                    <span class="text-white">{{ $brand['name'] }}</span>
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full border border-sage/30 bg-sage px-2 py-0.5 text-xs font-semibold text-dark">
                    {{ $brand['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-sage/60">{{ __('messages.no_brands_to_filter_yet') }}</p>
        @endforelse
    </div>
</details>

<details class="group rounded-2xl border border-sage/30 bg-dark/50 {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-white transition hover:text-sage">
        <span>{{ __('messages.categories_index_filters_collections') }}</span>
        <svg class="h-4 w-4 text-sage/60 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $summaryPaddingX }} pb-5 {{ $sectionSpacing }} max-h-56 overflow-y-auto">
        @forelse ($this->facetCollections as $collection)
            <label class="flex items-center justify-between gap-2 rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm font-medium text-white transition hover:border-sage hover:bg-sage/10">
                <span class="flex items-center gap-3 text-white">
                    <input type="checkbox"
                           value="{{ $collection['id'] }}"
                           wire:model.live="selectedCollectionIds"
                           class="rounded border-sage/30 text-sage focus:ring-sage">
                    <span class="text-white">{{ $collection['name'] }}</span>
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full border border-sage/30 bg-sage px-2 py-0.5 text-xs font-semibold text-dark">
                    {{ $collection['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-sage/60">{{ __('messages.no_collections_available_yet') }}</p>
        @endforelse
    </div>
</details>

<details class="group rounded-2xl border border-sage/30 bg-dark/50 {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-white transition hover:text-sage">
        <span>{{ __('messages.categories_index_filters_categories') }}</span>
        <svg class="h-4 w-4 text-sage/60 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $summaryPaddingX }} pb-5 {{ $sectionSpacing }} max-h-56 overflow-y-auto">
        @forelse ($this->facetCategories as $category)
            <label class="flex items-center justify-between gap-2 rounded-xl border border-sage/30 bg-dark/30 px-3 py-2 text-sm font-medium text-white transition hover:border-sage hover:bg-sage/10">
                <span class="flex items-center gap-3 text-white">
                    <input type="checkbox"
                           value="{{ $category['id'] }}"
                           wire:model.live="selectedCategoryIds"
                           class="rounded border-sage/30 text-sage focus:ring-sage">
                    <span class="text-white">{{ $category['name'] }}</span>
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full border border-sage/30 bg-sage px-2 py-0.5 text-xs font-semibold text-dark">
                    {{ $category['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-sage/60">{{ __('messages.no_nested_categories_available') }}</p>
        @endforelse
    </div>
</details>

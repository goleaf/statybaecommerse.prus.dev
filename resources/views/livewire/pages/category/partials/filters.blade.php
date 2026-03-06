@php
    $variant = $variant ?? 'desktop';
    $cardPadding = $variant === 'mobile' ? 'p-5' : 'p-6';
    $cardShadow = $variant === 'mobile' ? 'shadow-lg' : 'shadow-sm';
    $summaryPaddingX = $variant === 'mobile' ? 'px-5' : 'px-6';
    $summaryPaddingY = $variant === 'mobile' ? 'py-3' : 'py-4';
    $sectionSpacing = $variant === 'mobile' ? 'space-y-4' : 'space-y-5';
    $clearFiltersUrl = \Illuminate\Support\Facades\Route::has('localized.categories.index')
        ? route('localized.categories.index')
        : (\Illuminate\Support\Facades\Route::has('frontend.categories.index')
            ? route('frontend.categories.index')
            : url('/categories'));
@endphp

<div class="rounded-2xl border border-slate-200 bg-white {{ $cardShadow }} {{ $cardPadding }} space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700">
            {{ __('messages.refine_results') }}
        </h3>
        <a href="{{ $clearFiltersUrl }}"
           class="text-xs font-semibold text-cyan-700 transition hover:text-cyan-800">
            {{ __('messages.clear_all') }}
        </a>
    </div>

    <div class="flex flex-col gap-3">
        <label for="category-search-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-slate-500">
            {{ __('messages.search_categories') }}
        </label>
        <div class="flex items-center gap-2 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 focus-within:border-cyan-400 focus-within:ring-2 focus-within:ring-cyan-200">
            <span class="px-3 text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
            </span>
            <input id="category-search-{{ $variant }}"
                   type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="{{ __('messages.type_to_filter_categories') }}"
                   class="w-full border-0 bg-transparent py-2 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0" />
        </div>
    </div>
</div>

<div class="rounded-2xl border border-slate-200 bg-white {{ $cardShadow }} {{ $cardPadding }} space-y-3">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700">
        {{ __('messages.availability') }}
    </h3>

    <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/70">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="inStock" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            <span>{{ __('messages.in_stock_only') }}</span>
        </div>
        <span class="text-xs text-slate-500">{{ __('messages.real_time') }}</span>
    </label>

    <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/70">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="onSale" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            <span>{{ __('messages.promotions') }}</span>
        </div>
    </label>

    <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/70">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="hasProducts" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            <span>{{ __('messages.with_active_listings') }}</span>
        </div>
        <span class="text-xs text-slate-500">{{ __('messages.verified_content') }}</span>
    </label>
</div>

<details class="group rounded-2xl border border-slate-200 bg-white {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-slate-800 transition hover:text-cyan-700">
        <span>{{ __('messages.categories_index_filters_brands') }}</span>
        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $summaryPaddingX }} pb-5 {{ $sectionSpacing }} max-h-56 overflow-y-auto">
        <div class="flex flex-col gap-2">
            <label for="brand-search-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {{ __('messages.brands_index_search_label') }}
            </label>
            <div class="flex items-center gap-2 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 focus-within:border-cyan-400 focus-within:ring-2 focus-within:ring-cyan-200">
                <span class="px-3 text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                </span>
                <input id="brand-search-{{ $variant }}"
                       type="search"
                       wire:model.live.debounce.300ms="brandSearch"
                       placeholder="{{ __('messages.brands_index_search_placeholder') }}"
                       class="w-full border-0 bg-transparent py-2 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0" />
            </div>
        </div>

        @forelse ($this->filteredFacetBrands as $brand)
            <label class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/70">
                <span class="flex items-center gap-3">
                    <input type="checkbox"
                           value="{{ $brand['id'] }}"
                           wire:model.live="selectedBrandIds"
                           class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                    <span>{{ $brand['name'] }}</span>
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                    {{ $brand['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-slate-500">{{ __('messages.no_brands_to_filter_yet') }}</p>
        @endforelse
    </div>
</details>

<details class="group rounded-2xl border border-slate-200 bg-white {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-slate-800 transition hover:text-cyan-700">
        <span>{{ __('messages.categories_index_filters_collections') }}</span>
        <svg class="h-4 w-4 text-slate-500 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $summaryPaddingX }} pb-5 {{ $sectionSpacing }} max-h-56 overflow-y-auto">
        @forelse ($this->facetCollections as $collection)
            <label class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/70">
                <span class="flex items-center gap-3">
                    <input type="checkbox"
                           value="{{ $collection['id'] }}"
                           wire:model.live="selectedCollectionIds"
                           class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                    <span>{{ $collection['name'] }}</span>
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                    {{ $collection['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-slate-500">{{ __('messages.no_collections_available_yet') }}</p>
        @endforelse
    </div>
</details>

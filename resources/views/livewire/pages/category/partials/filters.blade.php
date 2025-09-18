@php
    $variant = $variant ?? 'desktop';
    $cardPadding = $variant === 'mobile' ? 'p-5' : 'p-6';
    $cardShadow = $variant === 'mobile' ? 'shadow-lg' : 'shadow-sm';
    $summaryPaddingX = $variant === 'mobile' ? 'px-5' : 'px-6';
    $summaryPaddingY = $variant === 'mobile' ? 'py-3' : 'py-4';
    $sectionSpacing = $variant === 'mobile' ? 'space-y-4' : 'space-y-5';
@endphp

<div class="rounded-2xl border border-slate-200 bg-white {{ $cardShadow }} {{ $cardPadding }} space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">
            {{ __('Refine results') }}
        </h3>
        <a href="{{ route('localized.categories.index', ['locale' => app()->getLocale()]) }}"
           class="text-xs font-semibold text-blue-600 hover:text-blue-700">
            {{ __('Clear all') }}
        </a>
    </div>

    <div class="flex flex-col gap-3">
        <label for="category-search-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
            {{ __('Search categories') }}
        </label>
        <div class="flex items-center gap-2 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 focus-within:border-blue-300 focus-within:ring-2 focus-within:ring-blue-100">
            <span class="px-3 text-slate-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
            </span>
            <input id="category-search-{{ $variant }}"
                   type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="{{ __('Type to filter categories...') }}"
                   class="w-full border-0 bg-transparent py-2 pr-3 text-sm text-slate-600 placeholder:text-slate-400 focus:outline-none focus:ring-0" />
        </div>
    </div>
</div>

<div class="rounded-2xl border border-slate-200 bg-white {{ $cardShadow }} {{ $cardPadding }} space-y-3">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">
        {{ __('Availability') }}
    </h3>

    <label class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-blue-300 hover:text-blue-600">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="inStock" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span>{{ __('In stock only') }}</span>
        </div>
        <span class="text-xs text-slate-400">{{ __('Real-time') }}</span>
    </label>

    <label class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-blue-300 hover:text-blue-600">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="onSale" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span>{{ __('Promotions') }}</span>
        </div>
        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-semibold text-rose-600">
            {{ __('Hot') }}
        </span>
    </label>

    <label class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-blue-300 hover:text-blue-600">
        <div class="flex items-center gap-3">
            <input type="checkbox" wire:model.live="hasProducts" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span>{{ __('With active listings') }}</span>
        </div>
        <span class="text-xs text-slate-400">{{ __('Verified content') }}</span>
    </label>
</div>

<div class="rounded-2xl border border-slate-200 bg-white {{ $cardShadow }} {{ $cardPadding }} space-y-4">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">
        {{ __('Price range (EUR)') }}
    </h3>

    <div class="grid grid-cols-2 gap-3">
        <div class="flex flex-col gap-2">
            <label for="price-min-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                {{ __('Min') }}
            </label>
            <input id="price-min-{{ $variant }}"
                   type="number"
                   min="0"
                   step="0.01"
                   wire:model.live.debounce.500ms="priceMin"
                   placeholder="0.00"
                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-100" />
        </div>
        <div class="flex flex-col gap-2">
            <label for="price-max-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                {{ __('Max') }}
            </label>
            <input id="price-max-{{ $variant }}"
                   type="number"
                   min="0"
                   step="0.01"
                   wire:model.live.debounce.500ms="priceMax"
                   placeholder="2500.00"
                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-100" />
        </div>
    </div>
</div>

<details class="group rounded-2xl border border-slate-200 bg-white {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-slate-700">
        <span>{{ __('Brands') }}</span>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $summaryPaddingX }} pb-5 {{ $sectionSpacing }} max-h-56 overflow-y-auto">
        @forelse ($this->facetBrands as $brand)
            <label class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-blue-300 hover:text-blue-600">
                <span class="flex items-center gap-3">
                    <input type="checkbox"
                           value="{{ $brand['id'] }}"
                           wire:model.live="selectedBrandIds"
                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    {{ $brand['name'] }}
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">
                    {{ $brand['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-slate-400">{{ __('No brands to filter yet.') }}</p>
        @endforelse
    </div>
</details>

<details class="group rounded-2xl border border-slate-200 bg-white {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-slate-700">
        <span>{{ __('Collections') }}</span>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $summaryPaddingX }} pb-5 {{ $sectionSpacing }} max-h-56 overflow-y-auto">
        @forelse ($this->facetCollections as $collection)
            <label class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-blue-300 hover:text-blue-600">
                <span class="flex items-center gap-3">
                    <input type="checkbox"
                           value="{{ $collection['id'] }}"
                           wire:model.live="selectedCollectionIds"
                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    {{ $collection['name'] }}
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">
                    {{ $collection['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-slate-400">{{ __('No collections available yet.') }}</p>
        @endforelse
    </div>
</details>

<details class="group rounded-2xl border border-slate-200 bg-white {{ $cardShadow }}" open>
    <summary class="flex cursor-pointer items-center justify-between {{ $summaryPaddingX }} {{ $summaryPaddingY }} text-sm font-semibold text-slate-700">
        <span>{{ __('Categories') }}</span>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
        </svg>
    </summary>
    <div class="{{ $summaryPaddingX }} pb-5 {{ $sectionSpacing }} max-h-56 overflow-y-auto">
        @forelse ($this->facetCategories as $category)
            <label class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-blue-300 hover:text-blue-600">
                <span class="flex items-center gap-3">
                    <input type="checkbox"
                           value="{{ $category['id'] }}"
                           wire:model.live="selectedCategoryIds"
                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    {{ $category['name'] }}
                </span>
                <span class="inline-flex min-w-[2.5rem] items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">
                    {{ $category['count'] }}
                </span>
            </label>
        @empty
            <p class="text-xs text-slate-400">{{ __('No nested categories available.') }}</p>
        @endforelse
    </div>
</details>

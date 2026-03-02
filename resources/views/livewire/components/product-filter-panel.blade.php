@php
    $categoriesList = $availableCategories ?? collect();
    $brandsList = $availableBrands ?? collect();
    $attributesList = ($availableAttributes ?? collect())
        ->filter(function ($attribute) {
            $name = mb_strtolower((string) ($attribute->name ?? ''));
            $normalizedName = preg_replace('/\s+/', ' ', trim($name)) ?? $name;
            $slug = mb_strtolower((string) ($attribute->slug ?? ''));

            if (
                $name === 'ip rating'
                || $slug === 'ip-rating'
                || $slug === 'ip_rating'
                || str_contains($slug, 'ip-rating')
                || str_contains($slug, 'ip_rating')
                || (
                    str_contains($normalizedName, 'ip')
                    && (
                        str_contains($normalizedName, 'rating')
                        || str_contains($normalizedName, 'reiting')
                        || str_contains($normalizedName, 'class')
                        || str_contains($normalizedName, 'klase')
                    )
                )
            ) {
                return false;
            }

            return $attribute->values->isNotEmpty();
        })
        ->values();
@endphp

<div class="space-y-5">
    <div class="flex items-center justify-between">
        <button
            wire:click="clearFilters"
            wire:confirm="{{ __('translations.confirm_clear_search_filters') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            {{ __('translations.clear_all') }}
        </button>
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-800">
            {{ __('translations.search') }}
        </label>
        <div class="relative">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="{{ __('translations.search_products') }}"
                class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-500 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
            >
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    @if ($categoriesList->isNotEmpty())
        <div class="space-y-2">
            <label class="block text-sm font-semibold text-slate-800">
                {{ __('translations.categories') }}
            </label>
            <div class="max-h-44 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3">
                @foreach ($categoriesList as $category)
                    <label class="flex cursor-pointer items-center rounded-lg px-2 py-1.5 text-sm text-slate-800 transition hover:bg-white">
                        <input
                            wire:model.live="categories"
                            type="checkbox"
                            value="{{ $category->id }}"
                            class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                        >
                        <span class="ml-3 font-medium">{{ $category->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    @if ($brandsList->isNotEmpty())
        <div class="space-y-2">
            <label class="block text-sm font-semibold text-slate-800">
                {{ __('translations.brands') }}
            </label>
            <div class="max-h-44 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3">
                @foreach ($brandsList as $brand)
                    <label class="flex cursor-pointer items-center rounded-lg px-2 py-1.5 text-sm text-slate-800 transition hover:bg-white">
                        <input
                            wire:model.live="brands"
                            type="checkbox"
                            value="{{ $brand->id }}"
                            class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                        >
                        <span class="ml-3 font-medium">{{ $brand->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-800">
            {{ __('translations.price_range') }}
        </label>
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                        {{ __('translations.min_price') }}
                    </label>
                    <input
                        wire:model.live.debounce.500ms="minPrice"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                    >
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                        {{ __('translations.max_price') }}
                    </label>
                    <input
                        wire:model.live.debounce.500ms="maxPrice"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
                    >
                </div>
            </div>
        </div>
    </div>

    @if ($attributesList->isNotEmpty())
        <div class="space-y-4">
            @foreach ($attributesList as $attribute)
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-800">
                        {{ $attribute->name }}
                    </label>
                    <div class="max-h-40 space-y-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3">
                        @foreach ($attribute->values as $value)
                            <label class="flex cursor-pointer items-center rounded-lg px-2 py-1.5 text-sm text-slate-800 transition hover:bg-white">
                                <input
                                    wire:model.live="selectedAttributes.{{ $attribute->id }}"
                                    type="checkbox"
                                    value="{{ $value->id }}"
                                    class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
                                >
                                <span class="ml-2 font-medium">{{ $value->value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
        <h4 class="text-sm font-semibold text-slate-800">{{ __('frontend.search.quick_filters') }}</h4>
        <label class="flex cursor-pointer items-center rounded-lg px-2 py-1.5 text-sm text-slate-800 transition hover:bg-white">
            <input
                wire:model.live="inStock"
                type="checkbox"
                class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
            >
            <span class="ml-3 font-medium">{{ __('translations.in_stock_only') }}</span>
        </label>

        <label class="flex cursor-pointer items-center rounded-lg px-2 py-1.5 text-sm text-slate-800 transition hover:bg-white">
            <input
                wire:model.live="onSale"
                type="checkbox"
                class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
            >
            <span class="ml-3 font-medium">{{ __('translations.on_sale_only') }}</span>
        </label>
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-semibold text-slate-800">
            {{ __('translations.sort_by') }}
        </label>
        <div class="grid grid-cols-2 gap-2">
            <select
                wire:model.live="sortBy"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
            >
                <option value="created_at">{{ __('translations.newest') }}</option>
                <option value="name">{{ __('translations.name') }}</option>
                <option value="price">{{ __('translations.price') }}</option>
                <option value="stock_quantity">{{ __('translations.stock') }}</option>
                <option value="updated_at">{{ __('translations.recently_updated') }}</option>
            </select>
            <select
                wire:model.live="sortDirection"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200"
            >
                <option value="asc">{{ __('translations.ascending') }}</option>
                <option value="desc">{{ __('translations.descending') }}</option>
            </select>
        </div>
    </div>
</div>

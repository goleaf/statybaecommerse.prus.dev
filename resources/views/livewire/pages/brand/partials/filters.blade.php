@php
    $variant = $variant ?? 'desktop';
@endphp

<div class="space-y-5">
    <div class="rounded-2xl border border-sage/30 bg-dark/50 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-sage/60">
                {{ __('frontend/brands.filters.search_label') }}
            </h3>
            @if(filled($this->search))
                <button type="button"
                        wire:click="$set('search', '')"
                        class="text-xs font-semibold text-sage hover:text-white">
                    {{ __('Clear') }}
                </button>
            @endif
        </div>

        <div class="flex flex-col gap-3">
            <label for="brand-search-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-sage/60">
                {{ __('frontend/brands.filters.search_label') }}
            </label>
            <div class="flex items-center gap-2 overflow-hidden rounded-xl border border-sage/30 bg-dark/30 focus-within:border-sage focus-within:ring-2 focus-within:ring-sage/20">
                <span class="px-3 text-sage/60">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                </span>
                <input id="brand-search-{{ $variant }}"
                       type="search"
                       wire:model.live.debounce.400ms="search"
                       placeholder="{{ __('frontend/brands.filters.search_placeholder') }}"
                       class="w-full border-0 bg-transparent py-2 pr-3 text-sm text-sage placeholder:text-sage/50 focus:outline-none focus:ring-0" />
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-sage/30 bg-dark/50 p-6 shadow-sm space-y-3">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-sage/60">
            {{ __('frontend/brands.filters.sort_label') }}
        </h3>

        <div class="flex flex-col gap-3">
            <label for="brand-sort-{{ $variant }}" class="text-xs font-semibold uppercase tracking-wide text-sage/60">
                {{ __('frontend/brands.filters.sort_label') }}
            </label>
            <div class="flex items-center gap-2 overflow-hidden rounded-xl border border-sage/30 bg-dark/30 focus-within:border-sage focus-within:ring-2 focus-within:ring-sage/20">
                <span class="px-3 text-sage/60">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
                    </svg>
                </span>
                <select id="brand-sort-{{ $variant }}"
                        wire:model.live="sortBy"
                        class="w-full border-0 bg-transparent py-2 pr-3 text-sm font-medium text-sage focus:outline-none focus:ring-0">
                    <option value="name" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.name') }}</option>
                    <option value="name_desc" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.name_desc') }}</option>
                    <option value="products_count" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.products_count') }}</option>
                    <option value="created_at" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.created_at') }}</option>
                    <option value="featured" class="bg-dark text-sage">{{ __('frontend/brands.filters.options.featured') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-sage/30 bg-dark/50 p-6 shadow-sm space-y-3">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-sage/60">
            {{ __('Quick actions') }}
        </h3>

        <div class="grid gap-2">
                <button
                type="button"
                wire:click="$set('sortBy', 'featured')"
                class="group relative flex items-center justify-between overflow-hidden rounded-lg border border-sage/30 bg-dark/30 px-4 py-2 text-sm font-medium text-sage transition hover:border-sage hover:bg-sage/10 hover:text-dark"
                wire:class="{ 'border-sage bg-sage text-dark': $wire.sortBy === 'featured' }"
            >
                <span>{{ __('frontend/brands.filters.quick.featured') }}</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 17.3 6.18 20l1.11-6.45L2 8.9l6.5-.94L12 2l2.5 5.96 6.5.94-4.7 4.65L17.82 20 12 17.3z" />
                </svg>
            </button>
            <button
                type="button"
                wire:click="$set('sortBy', 'products_count')"
                class="group relative flex items-center justify-between overflow-hidden rounded-lg border border-sage/30 bg-dark/30 px-4 py-2 text-sm font-medium text-sage transition hover:border-sage hover:bg-sage/10 hover:text-dark"
                wire:class="{ 'border-sage bg-sage text-dark': $wire.sortBy === 'products_count' }"
            >
                <span>{{ __('frontend/brands.filters.quick.products') }}</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h7v6H3V7zm11 0h7v6h-7V7zM3 15h7v6H3v-6zm11 0h7v6h-7v-6z" />
                </svg>
            </button>
        </div>
    </div>

    @php
        $activeFilterCount = collect([
            filled($this->search ?? ''),
            ($this->sortBy ?? 'name') !== 'name',
        ])->filter()->count();
    @endphp

    <div class="rounded-xl border border-dashed border-sage/30 bg-dark/30 px-4 py-3 text-sm text-sage/80">
        @if($activeFilterCount > 0)
            <span class="font-semibold text-sage">
                {{ trans_choice('frontend/brands.filters.status.some', $activeFilterCount, ['count' => $activeFilterCount]) }}
            </span>
            <span class="mt-1 block text-xs text-sage/60">
                {{ __('frontend/brands.filters.status.some_hint') }}
            </span>
        @else
            <span class="font-semibold text-sage">
                {{ __('frontend/brands.filters.status.none') }}
            </span>
            <span class="mt-1 block text-xs text-sage/60">
                {{ __('frontend/brands.filters.status.none_hint') }}
            </span>
        @endif
    </div>

    <div class="flex items-center justify-between gap-3">
        <button
            type="button"
            wire:click="clearFilters"
            class="inline-flex items-center gap-2 rounded-lg border border-sage/30 bg-dark/30 px-4 py-2 text-sm font-semibold text-sage transition hover:border-sage hover:bg-sage/10 hover:text-dark"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            {{ __('shared.clear_filters') }}
        </button>
    </div>
</div>


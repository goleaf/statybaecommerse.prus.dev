@section('meta')
    <x-meta
        :title="__('messages.brands_index_meta_title') . ' - ' . config('app.name')"
        :description="__('messages.brands_index_meta_description')"
        canonical="{{ url()->current() }}" />
@endsection

@php
    $activeSortLabel = match ($sortBy) {
        'name_desc'      => __('messages.brands_index_sort_option_name_desc'),
        'products_count' => __('messages.brands_index_sort_option_products'),
        'created_at'     => __('messages.brands_index_sort_option_newest'),
        'featured'       => __('messages.brands_index_sort_option_featured'),
        default          => __('messages.brands_index_sort_option_name'),
    };
    $hasFilters = filled($search) || $sortBy !== 'name';
    $alphabet = $paginator->getCollection()
        ->pluck('name')
        ->filter()
        ->map(static fn ($name): string => mb_strtoupper(mb_substr(trim((string) $name), 0, 1)))
        ->filter()
        ->unique()
        ->values();
@endphp

<div class="min-h-screen bg-sage text-dark brands-page">
    <header class="bg-dark text-sage">
        <x-container class="px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <nav class="mb-8 text-sm text-sage/90" aria-label="{{ __('messages.brands') }}">
                <ol class="flex flex-wrap items-center gap-2">
                    <li>
                        <a href="{{ route('home', []) }}" class="font-medium text-sage underline decoration-sage/40 underline-offset-4 transition-colors hover:text-white hover:decoration-sage">
                            {{ __('nav.home') }}
                        </a>
                    </li>
                    <li class="text-sage/70">/</li>
                    <li class="text-white">{{ __('messages.brands') }}</li>
                </ol>
            </nav>

            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                <div class="max-w-3xl space-y-4">
                    <span class="inline-flex items-center rounded-full border border-sage/40 px-4 py-1 text-[11px] font-semibold uppercase tracking-[0.28em] text-sage">
                        {{ __('messages.brands_index_badge') }}
                    </span>
                    <h1 class="text-3xl font-bold leading-tight text-white sm:text-4xl md:text-5xl">
                        {{ __('messages.brands_index_title') }}
                    </h1>
                    <p class="text-base text-sage/90 sm:text-lg">
                        {{ __('messages.brands_index_description') }}
                    </p>
                </div>

                <dl class="grid gap-4 text-sm sm:grid-cols-3 lg:min-w-[22rem]">
                    <div class="rounded-2xl border border-sage/30 bg-sage/10 p-4 shadow-soft">
                        <dt class="text-sage/70">{{ __('messages.brands') }}</dt>
                        <dd class="mt-2 text-2xl font-semibold text-white">{{ number_format($totalBrands) }}</dd>
                    </div>
                    <div class="rounded-2xl border border-sage/30 bg-sage/10 p-4 shadow-soft">
                        <dt class="text-sage/70">{{ __('messages.brands_index_filters_title') }}</dt>
                        <dd class="mt-2 text-lg font-semibold text-white">
                            @if ($activeFilterCount > 0)
                                {{ trans_choice('messages.brands_index_status', $activeFilterCount, ['count' => $activeFilterCount]) }}
                            @else
                                {{ __('messages.brands_index_status_none') }}
                            @endif
                        </dd>
                    </div>
                    <div class="rounded-2xl border border-sage/30 bg-sage/10 p-4 shadow-soft">
                        <dt class="text-sage/70">{{ __('messages.brands_index_sort_label') }}</dt>
                        <dd class="mt-2 text-lg font-semibold text-white">{{ $activeSortLabel }}</dd>
                    </div>
                </dl>
            </div>
        </x-container>
    </header>

    <x-container class="px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <section class="rounded-3xl border border-ash bg-white/85 p-5 shadow-soft backdrop-blur-sm sm:p-6">
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px_auto] lg:items-end">
                <div>
                    <label for="brand-search" class="text-sm font-semibold text-dark">{{ __('messages.brands_index_search_label') }}</label>
                    <input
                        id="brand-search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="{{ __('messages.brands_index_search_placeholder') }}"
                        class="mt-2 w-full rounded-full border border-ash bg-sage/20 px-4 py-3 text-sm text-dark placeholder:text-dark/50 focus:border-dark focus:outline-none focus:ring-2 focus:ring-dark/10"
                    />
                </div>

                <div>
                    <label for="brand-sort" class="text-sm font-semibold text-dark">{{ __('messages.brands_index_sort_label') }}</label>
                    <select
                        id="brand-sort"
                        wire:model.live="sortBy"
                        class="mt-2 w-full rounded-full border border-ash bg-sage/20 px-4 py-3 text-sm font-medium text-dark focus:border-dark focus:outline-none focus:ring-2 focus:ring-dark/10"
                    >
                        <option value="name">{{ __('messages.brands_index_sort_option_name') }}</option>
                        <option value="name_desc">{{ __('messages.brands_index_sort_option_name_desc') }}</option>
                        <option value="products_count">{{ __('messages.brands_index_sort_option_products') }}</option>
                        <option value="created_at">{{ __('messages.brands_index_sort_option_newest') }}</option>
                        <option value="featured">{{ __('messages.brands_index_sort_option_featured') }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 lg:justify-end">
                    @if ($hasFilters)
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="inline-flex items-center justify-center rounded-full border border-dark bg-dark px-5 py-3 text-sm font-semibold text-sage shadow-soft transition-colors hover:bg-stone"
                        >
                            {{ __('messages.brands_index_reset_filters') }}
                        </button>
                    @endif
                </div>
            </div>

            <div class="mt-5 space-y-4 border-t border-ash/80 pt-4">
                <p class="text-sm text-dark/75">
                    @if ($paginator->count() > 0)
                        {{ __('messages.brands_index_showing_results', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $totalBrands]) }}
                    @else
                        {{ __('messages.brands_index_no_results') }}
                    @endif
                </p>

                @if ($alphabet->isNotEmpty())
                    <nav aria-label="{{ __('messages.brands') }}">
                        <ol class="flex flex-wrap items-center gap-2 text-xs font-semibold text-dark/70">
                            @foreach ($alphabet as $letter)
                                <li>
                                    <a
                                        href="#brands-letter-{{ $letter }}"
                                        class="inline-flex min-w-9 items-center justify-center rounded-full border border-ash bg-sage/40 px-3 py-1.5 transition-colors hover:border-dark hover:bg-sage hover:text-dark"
                                    >
                                        {{ $letter }}
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                @endif
            </div>
        </section>

        <div class="relative mt-8">
            <div wire:loading.delay.longer class="absolute inset-0 z-10 rounded-3xl bg-sage/70 backdrop-blur-sm"></div>

            @if ($paginator->count() > 0)
                @php
                    $renderedLetters = [];
                @endphp

                <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($paginator as $brand)
                        @php
                            $letter = mb_strtoupper(mb_substr((string) ($brand->name ?? ''), 0, 1));
                            $rowNumber = ($paginator->firstItem() ?? 1) + $loop->index;
                            $anchorId = '';
                            if (! in_array($letter, $renderedLetters, true)) {
                                $anchorId = 'brands-letter-' . $letter;
                                $renderedLetters[] = $letter;
                            }

                            $brandDescription = $brand->description
                                ? \Illuminate\Support\Str::limit(strip_tags((string) $brand->description), 150)
                                : __('messages.brands_index_description_placeholder');
                            $brandInitials = mb_strtoupper(mb_substr(trim((string) ($brand->name ?? '')), 0, 2));
                        @endphp

                        <article
                            id="{{ $anchorId }}"
                            class="group flex h-full scroll-mt-24 flex-col overflow-hidden rounded-3xl border border-ash bg-white shadow-soft transition duration-200 hover:-translate-y-1 hover:border-dark/25 hover:shadow-medium"
                        >
                            <div class="h-1.5 bg-gradient-to-r from-dark via-stone to-sage"></div>

                            <div class="flex h-full flex-col p-6">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex min-w-0 items-center gap-4">
                                        @if ($brand->logo)
                                            <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl border border-dark/10 bg-sage/40">
                                                <img
                                                    src="{{ $brand->logo }}"
                                                    alt="{{ $brand->name ?? '' }}"
                                                    loading="lazy"
                                                    class="h-10 w-10 object-contain"
                                                />
                                            </div>
                                        @else
                                            <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl border border-dark/10 bg-sage text-lg font-semibold tracking-[0.24em] text-dark">
                                                {{ $brandInitials !== '' ? $brandInitials : '--' }}
                                            </div>
                                        @endif

                                        <div class="min-w-0 space-y-2">
                                            <span class="inline-flex items-center rounded-full border border-ash bg-sage/40 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.24em] text-dark/70">
                                                {{ $letter }}{{ str_pad((string) $rowNumber, 2, '0', STR_PAD_LEFT) }}
                                            </span>
                                            <h2 class="text-xl font-semibold leading-tight text-dark">
                                                <a
                                                    href="{{ route('localized.brands.show', ['slug' => $brand->slug ?? '']) }}"
                                                    class="transition-colors hover:text-stone"
                                                >
                                                    {{ $brand->name ?? '' }}
                                                </a>
                                            </h2>
                                        </div>
                                    </div>

                                    <span class="shrink-0 rounded-full border border-dark/15 bg-dark px-3 py-1 text-xs font-semibold text-sage">
                                        {{ number_format($brand->products_count ?? 0) }} {{ __('messages.products') }}
                                    </span>
                                </div>

                                <div class="mt-6 flex-1 space-y-4">
                                    <p class="text-sm leading-6 text-dark/70">
                                        {{ $brandDescription }}
                                    </p>

                                    <div class="flex min-h-8 flex-wrap gap-2">
                                        @if ($brand->is_featured)
                                            <span class="inline-flex items-center rounded-full border border-dark/15 bg-sage px-3 py-1 text-xs font-semibold text-dark">
                                                {{ __('messages.featured') }}
                                            </span>
                                        @endif

                                        @if ($brand->website_domain)
                                            <span class="inline-flex items-center rounded-full border border-ash bg-sage/20 px-3 py-1 text-xs font-medium text-dark/70">
                                                {{ $brand->website_domain }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-6 border-t border-ash/80 pt-4">
                                    <a
                                        href="{{ route('localized.brands.show', ['slug' => $brand->slug ?? '']) }}"
                                        class="inline-flex items-center gap-2 rounded-full border border-dark bg-dark px-4 py-2.5 text-sm font-semibold text-sage shadow-soft transition-colors hover:bg-stone"
                                    >
                                        {{ __('messages.brands_index_visit_brand') }}
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>

                @if ($paginator->hasPages())
                    <div class="brands-pagination mt-8 rounded-3xl border border-ash bg-white p-6 shadow-soft">
                        {{ $paginator->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                @endif
            @else
                <section class="rounded-3xl border border-dashed border-ash bg-white/80 p-10 text-center shadow-soft">
                    <h2 class="text-xl font-semibold text-dark">{{ __('messages.brands_index_empty_title') }}</h2>
                    <p class="mt-2 text-sm text-dark/70">{{ __('messages.brands_index_empty_description') }}</p>

                    @if ($hasFilters)
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="mt-6 inline-flex items-center rounded-full border border-dark bg-dark px-5 py-2.5 text-sm font-semibold text-sage shadow-soft transition-colors hover:bg-stone"
                        >
                            {{ __('messages.brands_index_reset_filters') }}
                        </button>
                    @endif
                </section>
            @endif
        </div>
    </x-container>
</div>

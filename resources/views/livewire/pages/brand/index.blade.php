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

                <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3 lg:grid-cols-1 lg:justify-items-end">
                    <div class="border-b border-sage/40 pb-2 lg:min-w-56">
                        <dt class="text-sage/70">{{ __('messages.brands') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">{{ number_format($totalBrands) }}</dd>
                    </div>
                    <div class="border-b border-sage/40 pb-2 lg:min-w-56">
                        <dt class="text-sage/70">{{ __('messages.brands_index_filters_title') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">
                            @if ($activeFilterCount > 0)
                                {{ trans_choice('messages.brands_index_status', $activeFilterCount, ['count' => $activeFilterCount]) }}
                            @else
                                {{ __('messages.brands_index_status_none') }}
                            @endif
                        </dd>
                    </div>
                    <div class="border-b border-sage/40 pb-2 lg:min-w-56">
                        <dt class="text-sage/70">{{ __('messages.brands_index_sort_label') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">{{ $activeSortLabel }}</dd>
                    </div>
                </dl>
            </div>
        </x-container>
    </header>

    <x-container class="px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <section class="border border-dark/25 bg-sage/60">
            <div class="grid gap-4 p-4 sm:p-6 lg:grid-cols-[minmax(0,1fr)_240px_auto] lg:items-end">
                <div>
                    <label for="brand-search" class="text-sm font-semibold text-dark">{{ __('messages.brands_index_search_label') }}</label>
                    <input
                        id="brand-search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="{{ __('messages.brands_index_search_placeholder') }}"
                        class="mt-2 w-full border border-dark/30 bg-white/80 px-4 py-2.5 text-sm text-dark placeholder:text-dark/50 focus:border-dark focus:outline-none focus:ring-2 focus:ring-dark/20"
                    />
                </div>

                <div>
                    <label for="brand-sort" class="text-sm font-semibold text-dark">{{ __('messages.brands_index_sort_label') }}</label>
                    <select
                        id="brand-sort"
                        wire:model.live="sortBy"
                        class="mt-2 w-full border border-dark/30 bg-white/80 px-4 py-2.5 text-sm font-medium text-dark focus:border-dark focus:outline-none focus:ring-2 focus:ring-dark/20"
                    >
                        <option value="name">{{ __('messages.brands_index_sort_option_name') }}</option>
                        <option value="name_desc">{{ __('messages.brands_index_sort_option_name_desc') }}</option>
                        <option value="products_count">{{ __('messages.brands_index_sort_option_products') }}</option>
                        <option value="created_at">{{ __('messages.brands_index_sort_option_newest') }}</option>
                        <option value="featured">{{ __('messages.brands_index_sort_option_featured') }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    @if (filled($search) || $sortBy !== 'name')
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="inline-flex items-center border border-dark/30 px-4 py-2.5 text-sm font-semibold text-dark transition-colors hover:border-dark hover:bg-sage/50"
                        >
                            {{ __('messages.brands_index_reset_filters') }}
                        </button>
                    @endif
                </div>
            </div>

            <p class="border-t border-dark/20 px-4 py-3 text-sm text-dark/75 sm:px-6">
                @if ($paginator->count() > 0)
                    {{ __('messages.brands_index_showing_results', ['from' => $paginator->firstItem() ?? 0, 'to' => $paginator->lastItem() ?? 0, 'total' => $totalBrands]) }}
                @else
                    {{ __('messages.brands_index_no_results') }}
                @endif
            </p>

            @if ($alphabet->isNotEmpty())
                <nav class="border-t border-dark/20 px-4 py-3 sm:px-6" aria-label="{{ __('messages.brands') }}">
                    <ol class="flex flex-wrap items-center gap-2 text-xs font-semibold text-dark/70">
                        @foreach ($alphabet as $letter)
                            <li>
                                <a href="#brands-letter-{{ $letter }}" class="inline-flex min-w-8 justify-center border border-dark/25 px-2.5 py-1 transition-colors hover:border-dark hover:bg-sage/50 hover:text-dark">
                                    {{ $letter }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @endif
        </section>

        <div class="relative mt-8">
            <div wire:loading.delay.longer class="absolute inset-0 z-10 bg-white/70"></div>

            @if ($paginator->count() > 0)
                <section class="border border-dark/25 bg-white/50">
                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full table-auto border-collapse">
                            <thead class="sticky top-0 z-10 bg-dark text-sage">
                                <tr class="text-left text-xs uppercase tracking-wider">
                                    <th scope="col" class="w-12 px-4 py-3 text-right font-semibold sm:px-6">#</th>
                                    <th scope="col" class="px-4 py-3 font-semibold sm:px-6">{{ __('messages.brand') }}</th>
                                    <th scope="col" class="px-4 py-3 font-semibold sm:px-6">{{ __('messages.description') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right font-semibold sm:px-6">{{ __('messages.products') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right font-semibold sm:px-6">{{ __('messages.view') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-dark/15">
                                @php
                                    $renderedLetters = [];
                                @endphp
                                @foreach ($paginator as $brand)
                                    @php
                                        $letter = mb_strtoupper(mb_substr((string) ($brand->name ?? ''), 0, 1));
                                        $rowNumber = ($paginator->firstItem() ?? 1) + $loop->index;
                                        $anchorId = '';
                                        if (! in_array($letter, $renderedLetters, true)) {
                                            $anchorId = 'brands-letter-' . $letter;
                                            $renderedLetters[] = $letter;
                                        }
                                    @endphp
                                    <tr class="align-top odd:bg-white/40 even:bg-sage/20 hover:bg-sage/50">
                                        <td id="{{ $anchorId }}" class="scroll-mt-20 px-4 py-4 text-right text-sm font-semibold text-dark/55 sm:px-6">
                                            {{ $rowNumber }}
                                        </td>
                                        <td class="px-4 py-4 sm:px-6">
                                            <a href="{{ route('localized.brands.show', ['slug' => $brand->slug ?? '']) }}" class="text-base font-semibold text-dark underline decoration-dark/40 underline-offset-4 transition-colors hover:decoration-dark">
                                                {{ $brand->name ?? '' }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-4 text-sm leading-6 text-dark/75 sm:px-6">
                                            @if ($brand->description)
                                                {{ \Illuminate\Support\Str::limit(strip_tags((string) $brand->description), 180) }}
                                            @else
                                                {{ __('messages.brands_index_description_placeholder') }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-right text-sm font-semibold text-dark sm:px-6">
                                            {{ number_format($brand->products_count ?? 0) }}
                                        </td>
                                        <td class="px-4 py-4 text-right sm:px-6">
                                            <a href="{{ route('localized.brands.show', ['slug' => $brand->slug ?? '']) }}" class="text-sm font-semibold text-dark underline decoration-dark/40 underline-offset-4 transition-colors hover:decoration-dark">
                                                {{ __('messages.brands_index_visit_brand') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <ul class="divide-y divide-dark/15 md:hidden">
                        @foreach ($paginator as $brand)
                            @php
                                $letter = mb_strtoupper(mb_substr((string) ($brand->name ?? ''), 0, 1));
                                $rowNumber = ($paginator->firstItem() ?? 1) + $loop->index;
                            @endphp
                            <li class="space-y-3 px-4 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <span class="inline-flex border border-dark/25 px-2 py-0.5 text-[10px] font-semibold text-dark/70">
                                            {{ $letter }}{{ str_pad((string) $rowNumber, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                        <a href="{{ route('localized.brands.show', ['slug' => $brand->slug ?? '']) }}" class="block text-base font-semibold text-dark underline decoration-dark/40 underline-offset-4">
                                            {{ $brand->name ?? '' }}
                                        </a>
                                    </div>
                                    <span class="shrink-0 border border-dark/25 px-2.5 py-1 text-xs font-semibold text-dark">
                                        {{ number_format($brand->products_count ?? 0) }} {{ __('messages.products') }}
                                    </span>
                                </div>
                                <p class="text-sm leading-6 text-dark/75">
                                    @if ($brand->description)
                                        {{ \Illuminate\Support\Str::limit(strip_tags((string) $brand->description), 120) }}
                                    @else
                                        {{ __('messages.brands_index_description_placeholder') }}
                                    @endif
                                </p>
                                <a href="{{ route('localized.brands.show', ['slug' => $brand->slug ?? '']) }}" class="inline-flex text-sm font-semibold text-dark underline decoration-dark/40 underline-offset-4">
                                    {{ __('messages.brands_index_visit_brand') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>

                @if ($paginator->hasPages())
                    <div class="brands-pagination mt-8 border-t border-dark/20 pt-6">
                        {{ $paginator->onEachSide(1)->links('pagination::tailwind') }}
                    </div>
                @endif
            @else
                <section class="border border-dashed border-dark/30 bg-sage/40 p-10 text-center">
                    <h2 class="text-xl font-semibold text-dark">{{ __('messages.brands_index_empty_title') }}</h2>
                    <p class="mt-2 text-sm text-dark/70">{{ __('messages.brands_index_empty_description') }}</p>
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="mt-6 inline-flex items-center border border-dark bg-dark px-5 py-2.5 text-sm font-semibold text-sage transition-colors hover:bg-dark/90"
                    >
                        {{ __('messages.brands_index_reset_filters') }}
                    </button>
                </section>
            @endif
        </div>
    </x-container>
</div>

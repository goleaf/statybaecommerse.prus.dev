<div
    class="container mx-auto px-4 py-10"
    wire:loading.attr="aria-busy"
    aria-busy="false"
>
    {{-- Skip link keeps the refactored layout accessible --}}
    <a href="#results" class="sr-only focus:not-sr-only focus:underline">
        {{ __('search_skip_to_results') }}
    </a>

    {{-- Surface flash messages from search filters or cart actions --}}
    @if (session('status'))
        <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif
    @if ($errors->any())
        <x-alert type="error" class="mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <section class="rounded-3xl bg-gradient-to-br from-blue-600 via-blue-500 to-blue-700 px-6 py-10 text-white shadow-xl">
        <div class="grid gap-6 lg:grid-cols-[2fr,1fr] lg:items-center">
            <div>
                {{-- Hero title summarises the current search intent --}}
                <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">
                    {{ __('nav_search') }}
                </h1>
                <p class="mt-3 max-w-2xl text-sm text-blue-100 md:text-base">
                    {{ __('search_help') }}
                </p>
                @if ($term)
                    <p class="mt-4 inline-flex items-center rounded-full bg-white/15 px-4 py-2 text-sm font-medium text-white">
                        <x-heroicon-o-magnifying-glass class="mr-2 h-4 w-4" />
                        {{ __('search_for') }}
                        <span class="ml-1 font-semibold">“{{ $term }}”</span>
                    </p>
                @endif
            </div>
            <div class="space-y-3 rounded-2xl bg-white/10 p-4 backdrop-blur">
                {{-- Key metrics give immediate feedback about the query --}}
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-100">{{ __('search_total_results') }}</span>
                    <span class="font-semibold">
                        {{ number_format($products->total() ?? $products->count()) }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-100">{{ __('search_page_label') }}</span>
                    <span class="font-semibold">
                        {{ $products->currentPage() }} / {{ $products->lastPage() }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-100">{{ __('search_sort_label') }}</span>
                    <span class="font-semibold">
                        @switch($sort)
                            @case('name_asc')
                                {{ __('search_name_asc') }}
                                @break
                            @case('name_desc')
                                {{ __('search_name_desc') }}
                                @break
                            @default
                                {{ __('search_newest') }}
                        @endswitch
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Search controls wrap the interactive modules and sort picker --}}
    <section class="mt-10 grid gap-6 lg:grid-cols-[2fr,1fr]">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                        {{ __('search_refine_heading') }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('search_refine_description') }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <label for="sort" class="text-sm font-medium text-slate-600 dark:text-slate-300">
                        {{ __('search_sort') }}
                    </label>
                    <select
                        id="sort"
                        wire:model.live="sort"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    >
                        <option value="">{{ __('search_newest') }}</option>
                        <option value="name_asc">{{ __('search_name_asc') }}</option>
                        <option value="name_desc">{{ __('search_name_desc') }}</option>
                    </select>
                </div>
            </header>

            <div class="mt-6">
                {{-- Enhanced Search with Autocomplete keeps power users productive --}}
                <livewire:components.live-search
                    :max-results="20"
                    :search-types="['products', 'categories', 'brands', 'collections']"
                    :enable-suggestions="true"
                    :enable-recent-searches="true"
                    :enable-popular-searches="true"
                    placeholder="{{ __('search_products') }}"
                />
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                {{-- Additional query context cards --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ __('search_active_filters') }}
                    </dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $term ? __('search_filter_keyword', ['term' => $term]) : __('search_filter_none') }}
                    </dd>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800">
                    <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        {{ __('search_last_updated') }}
                    </dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                        {{ now()->translatedFormat('Y-m-d H:i') }}
                    </dd>
                </div>
            </dl>
        </div>

        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            {{-- Quick help section outlines how to use the search page --}}
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                {{ __('search_support_title') }}
            </h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                <li class="flex items-start gap-3">
                    <x-heroicon-o-sparkles class="mt-0.5 h-4 w-4 text-blue-500" />
                    <span>{{ __('search_support_tip_one') }}</span>
                </li>
                <li class="flex items-start gap-3">
                    <x-heroicon-o-adjustments-horizontal class="mt-0.5 h-4 w-4 text-blue-500" />
                    <span>{{ __('search_support_tip_two') }}</span>
                </li>
                <li class="flex items-start gap-3">
                    <x-heroicon-o-clock class="mt-0.5 h-4 w-4 text-blue-500" />
                    <span>{{ __('search_support_tip_three') }}</span>
                </li>
            </ul>
            <div class="mt-6 rounded-2xl bg-blue-50 p-4 text-sm text-blue-800 dark:bg-blue-900/40 dark:text-blue-100">
                <p class="font-semibold">{{ __('search_support_need_help') }}</p>
                <p class="mt-1">{{ __('search_support_contact_cta') }}</p>
                <a
                    href="{{ route('contact', ['locale' => app()->getLocale()]) }}"
                    class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-600 dark:text-blue-200 dark:hover:text-blue-100"
                >
                    <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" />
                    {{ __('search_support_contact_link') }}
                </a>
            </div>
        </aside>
    </section>

    {{-- Loading indicator stays visible while results refresh --}}
    <div
        wire:loading
        class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
        role="status"
        aria-live="polite"
    >
        @for ($i = 0; $i < 8; $i++)
            <x-skeleton.product-card />
        @endfor
    </div>

    @if ($products->isEmpty())
        <section
            class="mt-12 rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900"
            aria-live="polite"
        >
            {{-- Empty state informs the shopper how to recover --}}
            <x-heroicon-o-face-frown class="mx-auto h-12 w-12 text-slate-400" />
            <h2 class="mt-4 text-xl font-semibold text-slate-900 dark:text-white">
                {{ __('search_no_results_found') }}
            </h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                {{ __('frontend.try_different_search') }}
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <button
                    type="button"
                    wire:click="$set('q', '')"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:border-blue-500 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-slate-700 dark:text-slate-200"
                >
                    <x-heroicon-o-x-mark class="h-4 w-4" />
                    {{ __('search_clear_query') }}
                </button>
                <a
                    href="{{ route('localized.collections.index', ['locale' => app()->getLocale()]) }}"
                    class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-200"
                >
                    <x-heroicon-o-rectangle-stack class="h-4 w-4" />
                    {{ __('search_browse_collections') }}
                </a>
            </div>
        </section>
    @else
        <section class="mt-12">
            <header class="flex flex-col gap-2 md:flex-row md:items-baseline md:justify-between">
                {{-- Result summary anchors the layout for screen readers too --}}
                <p class="text-sm text-slate-600 dark:text-slate-400" aria-live="polite">
                    {{ trans_choice(__('search_result_count'), $products->total() ?? $products->count(), ['count' => $products->total() ?? $products->count()]) }}
                    @if ($term)
                        — {{ __('search_for') }} "{{ $term }}"
                    @endif
                </p>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">
                        <x-heroicon-o-bolt class="h-3.5 w-3.5 text-blue-500" />
                        {{ __('search_refresh_hint') }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 dark:bg-slate-800">
                        <x-heroicon-o-eye class="h-3.5 w-3.5 text-blue-500" />
                        {{ __('search_view_hint') }}
                    </span>
                </div>
            </header>

            <div id="results" class="mt-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($products as $product)
                    {{-- Product card already encapsulates pricing and CTA logic --}}
                    <x-product.card :product="$product" />
                @endforeach
            </div>

            <nav class="mt-8 flex justify-center" aria-label="{{ __('search_pagination') }}">
                {{ $products->links() }}
            </nav>
        </section>
    @endif

    {{-- Back button guides the shopper to continue browsing --}}
    <div class="mt-16 text-center">
        <a
            href="{{ route('home', ['locale' => app()->getLocale()]) }}"
            class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-200"
        >
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            {{ __('frontend.buttons.back_to_home') }}
        </a>
    </div>
</div>

@push('scripts')
    @php
        $elements = [];
        $position = 1;
        foreach ($products as $p) {
            $productSlug = $p->trans('slug') ?? $p->slug;
            if (empty($productSlug)) {
                continue; // Skip products without valid slug
            }
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => route('product.show', $productSlug),
                'name' => $p->trans('name') ?? $p->name,
            ];
        }
        $searchUrl = route('search', ['locale' => app()->getLocale()]);
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $searchUrl . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp
    <script type="application/ld+json">
        {!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    @if (!empty($elements))
        <script type="application/ld+json">
        {!! json_encode(['@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => $elements], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endpush

@section('meta')
    @php
        $first = $products->first();
        $cname = config('media.storage.collection_name');
        $ogImage = $first?->getFirstMediaUrl($cname, 'large');
        $preSmall = $first?->getFirstMediaUrl($cname, 'small');
        $preMedium = $first?->getFirstMediaUrl($cname, 'medium');
        $preLarge = $first?->getFirstMediaUrl($cname, 'large');
        $preSrc = $preMedium ?: ($preLarge ?: ($preSmall ?: ''));
        $preSrcset = [];
        if ($preSmall) {
            $preSrcset[] = $preSmall . ' 300w';
        }
        if ($preMedium) {
            $preSrcset[] = $preMedium . ' 500w';
        }
        if ($preLarge) {
            $preSrcset[] = $preLarge . ' 800w';
        }
        $preSizes = '(max-width: 640px) 45vw, (max-width: 1024px) 22vw, 200px';
    @endphp
    <x-meta
            :title="__('nav_search') . ' - ' . config('app.name')"
            :description="__('search_help')"
            robots="noindex,follow"
            :og-image="$ogImage"
            :prev="$products instanceof \Illuminate\Contracts\Pagination\Paginator ||
            $products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                ? $products->previousPageUrl()
                : null"
            :next="$products instanceof \Illuminate\Contracts\Pagination\Paginator ||
            $products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                ? $products->nextPageUrl()
                : null"
            :preload-image="(string) $preSrc"
            :preload-srcset="implode(', ', $preSrcset)"
            :preload-sizes="$preSizes"
            canonical="{{ url()->current() }}" />
@endsection

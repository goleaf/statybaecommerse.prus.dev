<div class="container mx-auto px-4 py-8" wire:loading.attr="aria-busy" aria-busy="false">
    <a href="#results" class="sr-only focus:not-sr-only focus:underline">{{ __('Skip to results') }}</a>
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
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Search') }}</h1>
        <div class="flex items-center gap-2">
            <label for="sort" class="sr-only">{{ __('Sort') }}</label>
            <select id="sort" wire:model.live="sort" class="rounded-md border-gray-300 text-sm">
                <option value="">{{ __('Newest') }}</option>
                <option value="name_asc">{{ __('Name (A–Z)') }}</option>
                <option value="name_desc">{{ __('Name (Z–A)') }}</option>
            </select>
        </div>
    </div>

    <div wire:loading role="status" aria-live="polite" class="mb-3 text-sm text-gray-600">
        {{ __('Loading…') }}
    </div>

    <form method="GET" action="{{ route('search.index', ['locale' => app()->getLocale()]) }}" class="mb-6">
        <input type="text" name="q" value="{{ $term }}" placeholder="{{ __('Search products') }}"
               aria-label="{{ __('Search products') }}"
               class="w-full md:w-1/2 rounded-md border-gray-300" />
    </form>

    <div wire:loading class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-6" role="status"
         aria-live="polite">
        @for ($i = 0; $i < 8; $i++)
            <x-skeleton.product-card />
        @endfor
    </div>
    @if ($products->isEmpty())
        <p class="text-gray-500" aria-live="polite">{{ __('No results found.') }}</p>
    @else
        <p class="text-sm text-gray-600 mb-2" aria-live="polite">
            {{ trans_choice(':count result found|:count results found', $products->total() ?? $products->count(), ['count' => $products->total() ?? $products->count()]) }}
            @if ($term)
                — {{ __('for') }} “{{ $term }}”
            @endif
        </p>
        <div id="results" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($products as $product)
                <x-product.card :product="$product" />
            @endforeach
        </div>
        <nav class="mt-6" aria-label="{{ __('Pagination') }}">{{ $products->links() }}</nav>
    @endif
</div>

@push('scripts')
    @php
        $elements = [];
        $position = 1;
        foreach ($products as $p) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => route('product.show', [
                    'locale' => app()->getLocale(),
                    'slug' => $p->trans('slug') ?? $p->slug,
                ]),
                'name' => $p->trans('name') ?? $p->name,
            ];
        }
        $searchUrl = route('search.index', ['locale' => app()->getLocale()]);
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
        $cname = config('shopper.media.storage.collection_name');
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
            :title="__('Search') . ' - ' . config('app.name')"
            :description="__('Search products across categories, brands, and collections.')"
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

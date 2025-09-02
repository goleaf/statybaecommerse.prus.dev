@section('meta')
    @php($ogImage = $category->getFirstMediaUrl(config('shopper.media.storage.collection_name')))
    @php
        $firstProduct = $products->first();
        $cname = config('shopper.media.storage.collection_name');
        $preSmall = $firstProduct?->getFirstMediaUrl($cname, 'small');
        $preMedium = $firstProduct?->getFirstMediaUrl($cname, 'medium');
        $preLarge = $firstProduct?->getFirstMediaUrl($cname, 'large');
        $preSrc = $preMedium ?: ($preLarge ?: ($preSmall ?: null));
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
            :title="$category->trans('seo_title') ?? $category->name"
            :description="$category->trans('seo_description') ?? Str::limit(strip_tags($category->description), 150)"
            :og-image="$ogImage"
            :prev="$products->previousPageUrl()"
            :next="$products->nextPageUrl()"
            :preload-image="(string) $preSrc"
            :preload-srcset="implode(', ', $preSrcset)"
            :preload-sizes="$preSizes"
            canonical="{{ url()->current() }}" />
@endsection

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
    <x-breadcrumbs :items="[
        ['label' => __('Categories'), 'url' => route('category.index', ['locale' => app()->getLocale()])],
        ['label' => $category->trans('name') ?? $category->name],
    ]" />
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ $category->trans('name') ?? $category->name }}</h1>
        <div class="flex items-center gap-2">
            <label for="sort" class="sr-only">{{ __('Sort') }}</label>
            <select id="sort" wire:model.live="sort" class="rounded-md border-gray-300 text-sm">
                <option value="">{{ __('Newest') }}</option>
                <option value="name_asc">{{ __('Name (A–Z)') }}</option>
                <option value="name_desc">{{ __('Name (Z–A)') }}</option>
            </select>
        </div>
    </div>

    <div wire:loading role="status" aria-live="polite" class="mb-4 text-sm text-gray-600">
        {{ __('Loading…') }}
    </div>

    @if ($category->description)
        <div class="prose max-w-none mb-8">{!! $category->description !!}</div>
    @endif

    @if ($category->children && $category->children->count())
        <h2 class="text-xl font-semibold mb-3">{{ __('Subcategories') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            @foreach ($category->children as $child)
                <a href="{{ route('category.show', ['locale' => app()->getLocale(), 'slug' => $child->trans('slug') ?? $child->slug]) }}"
                   class="block border rounded-lg p-4 hover:shadow-sm">
                    <div class="aspect-square bg-gray-50 flex items-center justify-center mb-3">
                        @php($thumb = $child->getFirstMediaUrl(config('shopper.media.storage.thumbnail_collection')) ?: ($child->getFirstMediaUrl(config('shopper.media.storage.collection_name'), 'small') ?: $child->getFirstMediaUrl(config('shopper.media.storage.collection_name'))))
                        @if ($thumb)
                            <img loading="lazy" src="{{ $thumb }}"
                                 alt="{{ $child->trans('name') ?? $child->name }}" class="max-h-24 object-contain" />
                        @endif
                    </div>
                    <div class="text-base font-medium">{{ $child->trans('name') ?? $child->name }}</div>
                </a>
            @endforeach
        </div>
    @endif

    @if ($options->isNotEmpty())
        <h2 class="text-xl font-semibold mb-3">{{ __('Filter by') }}</h2>
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @foreach (collect($selectedValues)->filter() as $valId)
                @php($val = $options->flatten(1)->firstWhere('id', (int) $valId) ?? null)
                @if ($val)
                    <button type="button" wire:click="removeFilter({{ (int) $valId }})"
                            class="inline-flex items-center gap-1 text-xs bg-gray-100 rounded-full px-2 py-1">
                        <span>{{ $val->value }}</span>
                        <span aria-hidden="true">×</span>
                    </button>
                @endif
            @endforeach
            @if (collect($selectedValues)->filter()->isNotEmpty())
                <button type="button" wire:click="clearFilters" class="text-xs underline">
                    {{ __('Clear all') }}
                </button>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            @foreach ($options as $group)
                <div>
                    <div class="text-sm font-medium mb-2">{{ $group['attribute']->name }}</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($group['values'] as $val)
                            <label class="inline-flex items-center gap-1 text-sm">
                                <input type="checkbox" wire:model.live="selectedValues" value="{{ $val->id }}" />
                                <span>{{ $val->value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h2 class="text-xl font-semibold mb-4">{{ __('Products') }}</h2>
    <div wire:loading class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-6" role="status"
         aria-live="polite">
        @for ($i = 0; $i < 8; $i++)
            <x-skeleton.product-card />
        @endfor
    </div>
    @if ($products->isEmpty())
        <div class="text-slate-500" aria-live="polite">{{ __('No products in this category yet.') }}</div>
    @else
        <p class="text-sm text-gray-600 mb-2" aria-live="polite">
            {{ trans_choice(':count result found|:count results found', $products->total() ?? $products->count(), ['count' => $products->total() ?? $products->count()]) }}
        </p>
        <div id="results" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($products as $product)
                <a href="{{ route('product.show', ['locale' => app()->getLocale(), 'slug' => $product->trans('slug') ?? $product->slug]) }}"
                   class="block border rounded-lg p-4 hover:shadow-sm">
                    <x-product.thumbnail :product="$product" containerClass="mb-3" />
                    <div class="text-base font-medium">{{ $product->trans('name') ?? $product->name }}</div>
                    <x-product.price :product="$product" class="mt-1" />
                </a>
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
    @endphp
    @if (!empty($elements))
        <script type="application/ld+json">
        {!! json_encode(['@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => $elements], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endpush

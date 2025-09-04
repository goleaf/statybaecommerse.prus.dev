@section('meta')
    @php($ogImage = $collection->getFirstMediaUrl(config('media.storage.collection_name'), 'large') ?: $collection->getFirstMediaUrl(config('media.storage.collection_name')))
    @php
        $firstProduct = $products->first();
        $cname = config('media.storage.collection_name');
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
            :title="$collection->trans('name') ?? $collection->name"
            :description="$collection->trans('description')
                ? Str::limit(strip_tags($collection->trans('description')), 150)
                : ''"
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
        ['label' => __('Collections'), 'url' => route('collection.index', ['locale' => app()->getLocale()])],
        ['label' => $collection->trans('name') ?? $collection->name],
    ]" />
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ $collection->trans('name') ?? $collection->name }}</h1>
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

    @if ($collection->trans('description') ?? $collection->description)
        <div class="prose max-w-none mb-8">{!! $collection->trans('description') ?? $collection->description !!}</div>
    @endif

    <div class="mb-4">
        <h2 class="text-xl font-semibold mb-2">{{ __('Filter by brand') }}</h2>
        <div class="flex flex-wrap items-center gap-2 mb-2">
            @foreach (collect($brandIds)->filter() as $bid)
                @php($b = $this->availableBrands->firstWhere('id', (int) $bid))
                @if ($b)
                    <button type="button" wire:click="removeBrandFilter({{ (int) $bid }})"
                            class="inline-flex items-center gap-1 text-xs bg-gray-100 rounded-full px-2 py-1">
                        <span>{{ $b->trans('name') ?? $b->name }}</span>
                        <span aria-hidden="true">×</span>
                    </button>
                @endif
            @endforeach
            @if (collect($brandIds)->filter()->isNotEmpty())
                <button type="button" wire:click="clearBrandFilters" class="text-xs underline">
                    {{ __('Clear all') }}
                </button>
            @endif
        </div>
        <div class="flex flex-wrap gap-3">
            @foreach ($this->availableBrands as $brand)
                <label class="inline-flex items-center gap-1 text-sm">
                    <input type="checkbox" wire:model.live="brandIds" value="{{ $brand->id }}" />
                    <span>{{ $brand->trans('name') ?? $brand->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @if (isset($options) && $options->isNotEmpty())
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">{{ __('Filter by') }}</h2>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                @foreach (collect($selectedValues)->filter() as $valId)
                    @php($val = $options->flatten(1)->firstWhere('id', (int) $valId) ?? null)
                    @if ($val)
                        <button type="button" wire:click="removeAttributeFilter({{ (int) $valId }})"
                                class="inline-flex items-center gap-1 text-xs bg-gray-100 rounded-full px-2 py-1">
                            <span>{{ $val->value }}</span>
                            <span aria-hidden="true">×</span>
                        </button>
                    @endif
                @endforeach
                @if (collect($selectedValues)->filter()->isNotEmpty())
                    <button type="button" wire:click="clearAttributeFilters" class="text-xs underline">
                        {{ __('Clear all') }}
                    </button>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($options as $group)
                    <div>
                        <div class="text-sm font-medium mb-2">{{ $group['attribute']->name }}</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($group['values'] as $val)
                                <label class="inline-flex items-center gap-1 text-sm">
                                    <input type="checkbox" wire:model.live="selectedValues"
                                           value="{{ $val->id }}" />
                                    <span>{{ $val->value }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
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
        <div class="text-slate-500" aria-live="polite">{{ __('No products in this collection yet.') }}</div>
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

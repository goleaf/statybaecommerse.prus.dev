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
        ['label' => __('Collections'), 'url' => route('localized.collections.index', ['locale' => app()->getLocale()])],
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
        @php
            $collectionDescription = $collection->trans('description') ?? $collection->description ?? '';
        @endphp
        <x-sanitized-html class="prose max-w-none mb-8" :content="$collectionDescription" />
    @endif

    <div class="mb-4">
        <h2 class="text-xl font-semibold mb-2">{{ __('Filter by brand') }}</h2>
        @php
            $brandOptions = $brandOptions ?? [];
            $activeBrandIds = $activeBrandIds ?? [];
            $brandLookup = collect($brandOptions)->keyBy('id');
        @endphp
        <div class="flex flex-wrap items-center gap-2 mb-2">
            @foreach ($activeBrandIds as $brandId)
                @php($brand = $brandLookup->get($brandId))
                @if ($brand)
                    <button type="button"
                            wire:key="active-brand-{{ $brandId }}"
                            wire:click="removeBrandFilter({{ $brandId }})"
                            wire:confirm="{{ __('translations.confirm_remove_brand_filter') }}"
                            class="inline-flex items-center gap-1 text-xs rounded-full bg-gray-100 px-2 py-1">
                        <span>{{ $brand['name'] }}</span>
                        <span aria-hidden="true">×</span>
                    </button>
                @endif
            @endforeach
            @if (! empty($activeBrandIds))
                <button type="button"
                        wire:click="clearBrandFilters"
                        wire:confirm="{{ __('translations.confirm_clear_brand_filters') }}"
                        class="text-xs underline">
                    {{ __('Clear all') }}
                </button>
            @endif
        </div>
        <div class="flex flex-wrap gap-3">
            @foreach ($brandOptions as $brand)
                <label wire:key="brand-option-{{ $brand['id'] }}"
                       class="inline-flex items-center gap-1 text-sm">
                    <input type="checkbox"
                           wire:model.live="brandIds"
                           value="{{ $brand['id'] }}" />
                    <span>{{ $brand['name'] }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @php
        $filterGroups = $filterGroups ?? [];
        $filterValueLookup = collect($filterValueLookup ?? []);
        $activeValueIds = $activeValueIds ?? [];
    @endphp
    @if (! empty($filterGroups))
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">{{ __('Filter by') }}</h2>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                @foreach ($activeValueIds as $valueId)
                    @php($selected = $filterValueLookup->get($valueId))
                    @if ($selected)
                        <button type="button"
                                wire:key="active-filter-{{ (int) $valueId }}"
                                wire:click="removeAttributeFilter({{ (int) $valueId }})"
                                wire:confirm="{{ __('translations.confirm_remove_attribute_filter') }}"
                                class="inline-flex items-center gap-1 text-xs rounded-full bg-gray-100 px-2 py-1">
                            <span>{{ data_get($selected, 'label') }}</span>
                            <span aria-hidden="true">×</span>
                        </button>
                    @endif
                @endforeach
                @if (! empty($activeValueIds))
                    <button type="button"
                            wire:click="clearAttributeFilters"
                            wire:confirm="{{ __('translations.confirm_clear_attribute_filters') }}"
                            class="text-xs underline">
                        {{ __('Clear all') }}
                    </button>
                @endif
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ($filterGroups as $group)
                    @php
                        $attribute = $group['attribute'] ?? [];
                        $attributeId = (int) ($attribute['id'] ?? 0);
                        $attributeName = $attribute['name'] ?? __('Filters');
                    @endphp
                    <div wire:key="filter-group-{{ $attributeId ?: uniqid('attr-', false) }}">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">
                            {{ $attributeName }}
                        </h3>
                        <ul class="flex flex-wrap gap-2">
                            @foreach ($group['values'] ?? [] as $value)
                                @php($valueId = (int) ($value['id'] ?? 0))
                                <li wire:key="filter-{{ $attributeId }}-{{ $valueId }}">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm transition {{ ! empty($value['selected']) ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                        wire:click="toggleFilter({{ $attributeId }}, {{ $valueId }})"
                                    >
                                        <span>{{ $value['label'] ?? '' }}</span>
                                        @if (! empty($value['selected']))
                                            <span aria-hidden="true">×</span>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>
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
                <a href="{{ route('product.show', $product->trans('slug') ?? $product->slug) }}"
                   class="block border rounded-lg p-4 hover:shadow-sm">
                    <x-product.thumbnail :product="$product" containerClass="mb-3" />
                    <div class="text-base font-medium">{{ $product->trans('name') ?? $product->name }}</div>
                    <x-product.price :product="$product" class="mt-1" />
                </a>
            @endforeach
        </div>
        <nav class="mt-6" aria-label="{{ __('Pagination') }}">{{ $products->links() }}</nav>
    @endif

    <!-- Back Button -->
    <div class="mt-8 text-center">
        <a href="{{ route('localized.collections.index', ['locale' => app()->getLocale()]) }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition duration-200">
            <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
            {{ __('frontend.buttons.back_to_collections') }}
        </a>
    </div>
</div>

@push('scripts')
    @php
        $elements = [];
        $position = 1;
        foreach ($products as $p) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => route('product.show', $p->trans('slug') ?? $p->slug),
                'name' => $p->trans('name') ?? $p->name,
            ];
        }
    @endphp
    @if (!empty($elements))
        <script nonce="{{ csp_nonce() }}" type="application/ld+json">
        {!! json_encode(['@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => $elements], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endpush

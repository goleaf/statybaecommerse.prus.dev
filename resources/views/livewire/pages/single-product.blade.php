@section('meta')
    @php
        $ogImage =
            $product->getFirstMediaUrl(config('media.storage.collection_name'), 'large') ?:
            $product->getFirstMediaUrl(config('media.storage.collection_name'));
    @endphp
    <x-meta
            :title="$product->trans('seo_title') ?? $product->name"
            :description="$product->trans('seo_description') ?? Str::limit(strip_tags($product->description), 150)"
            :og-image="$ogImage"
            ogType="product"
            :canonical="url()->current()"
            :preload-image="(string) ($ogImage ?: '')" />
@endsection

<div class="bg-gradient-to-b from-slate-50 via-white to-white" wire:loading.attr="aria-busy" aria-busy="false">
    @if (session('status'))
        <x-container class="max-w-7xl"><x-alert type="success" class="mb-6">{{ session('status') }}</x-alert></x-container>
    @endif
    @if (session('error'))
        <x-container class="max-w-7xl"><x-alert type="error" class="mb-6">{{ session('error') }}</x-alert></x-container>
    @endif

    <div class="py-12 lg:py-16">
        <x-container class="max-w-7xl space-y-10">
            <x-breadcrumbs :items="[
                [
                    'label' => __('frontend.navigation.products'),
                    'url' => route('products.index', ['locale' => app()->getLocale()]),
                ],
                [
                    'label' => $product->brand?->trans('name') ?? $product->brand?->name,
                    'url' =>
                        $product->brand && function_exists('route') && Route::has('brands.show')
                            ? route('brands.show', [
                                'locale' => app()->getLocale(),
                                'brand' => $product->brand->trans('slug') ?? $product->brand->slug,
                            ])
                            : null,
                ],
                ['label' => $product->trans('name') ?? $product->name],
            ]" aria-label="{{ __('frontend.navigation.breadcrumbs') }}" />

            @php
                $brandLabel = $product->brand?->trans('name') ?? $product->brand?->name;
                $categoryLabels = $product->categories
                    ->map(fn($category) => $category->trans('name') ?? $category->name)
                    ->filter()
                    ->values();
                $averageRating = round((float) ($product->average_rating ?? 0), 1);
                $reviewCount = (int) ($product->reviews_count ?? 0);
                $priceData = $product->getPrice();
                $currentCurrency = function_exists('current_currency') ? current_currency() : null;
                $currentPrice = $priceData?->value?->amount ?? $product->price;
                $comparePrice = $priceData?->compare?->amount ?? $product->compare_price;
                $discountPercent = $priceData?->percentage ?? null;
                $shortDescription = $product->trans('short_description') ?? $product->short_description;
                $recentHistories = $product->recentHistories()->limit(4)->get();
                $contactUrl = Route::has('contact') ? route('contact', ['locale' => app()->getLocale()]) : 'mailto:' . (config('mail.from.address') ?? 'info@example.com');
            @endphp

            <div class="grid gap-10 lg:grid-cols-12">
                <div class="lg:col-span-7 space-y-8">
                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <div class="flex flex-wrap items-center gap-3">
                                @if($brandLabel)
                                    <span class="inline-flex items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                        {{ $brandLabel }}
                                    </span>
                                @endif
                                @foreach($categoryLabels as $categoryName)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                        {{ $categoryName }}
                                    </span>
                                @endforeach
                            </div>

                            <div>
                                <h1 class="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">
                                    {{ $product->trans('name') ?? $product->name }}
                                </h1>
                                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-600">
                                    <div class="flex items-center gap-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @php($starActive = $averageRating >= $i - 0.25)
                                            <x-heroicon-o-star class="h-4 w-4 {{ $starActive ? 'text-amber-500' : 'text-slate-200' }}" />
                                        @endfor
                                        <span class="ml-2 font-semibold text-slate-700">{{ number_format($averageRating, 1) }}</span>
                                    </div>
                                    <span class="text-slate-300">•</span>
                                    <span class="font-medium text-slate-600">{{ $reviewCount }} {{ __('translations.reviews') }}</span>
                                    @if($product->sku)
                                        <span class="text-slate-300">•</span>
                                        <span class="text-slate-500">{{ __('translations.sku') }}: {{ $product->sku }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    @if($currentPrice)
                                        <p class="text-4xl font-semibold text-primary-600">
                                            {{ \Illuminate\Support\Number::currency((float) $currentPrice, $currentCurrency, app()->getLocale()) }}
                                        </p>
                                    @endif
                                    @if($comparePrice && $currentPrice && $comparePrice > $currentPrice)
                                        <p class="flex items-center gap-2 text-sm text-slate-500">
                                            <span class="line-through">
                                                {{ \Illuminate\Support\Number::currency((float) $comparePrice, $currentCurrency, app()->getLocale()) }}
                                            </span>
                                            @if($discountPercent)
                                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600">
                                                    -{{ number_format($discountPercent, 0) }}%
                                                </span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="sm:text-right">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('translations.available') }}</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        {{ $product->availableQuantity() }}
                                    </p>
                                </div>
                            </div>

                            @if($shortDescription)
                                <p class="text-base leading-relaxed text-slate-600">
                                    {{ $shortDescription }}
                                </p>
                            @endif
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="p-4 sm:p-6">
                            <livewire:components.product-image-gallery :product="$product" image-size="xl" />
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-slate-900">{{ __('Key Features') }}</h2>
                                <span class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $this->attributeFeatures->count() }} {{ __('Features') }}</span>
                            </div>

                            @if($this->attributeFeatures->isEmpty())
                                <p class="text-sm text-slate-500">{{ __('No additional feature information is available for this product yet.') }}</p>
                            @else
                                <div class="grid gap-4 sm:grid-cols-2">
                                    @foreach($this->attributeFeatures as $feature)
                                        <div class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/60 p-4">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-sm">
                                                <x-heroicon-o-check-badge class="h-5 w-5 text-emerald-500" />
                                            </div>
                                            <div class="space-y-1">
                                                <p class="text-sm font-semibold text-slate-900">{{ $feature['label'] }}</p>
                                                <p class="text-sm text-slate-600">{{ $feature['value'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <h2 class="text-lg font-semibold text-slate-900">{{ __('Detailed Description') }}</h2>
                            <div class="prose prose-slate max-w-none text-slate-700">
                                {!! $product->trans('description') ?? $product->description ?? '<p>'.__('No description provided for this product yet.').'</p>' !!}
                            </div>
                        </div>
                    </section>

                    @php
                        $technicalMetrics = [
                            ['label' => __('frontend.products.height'), 'value' => $product->height ? \Illuminate\Support\Number::format((float) $product->height) . ' ' . ($product->height_unit?->value ?? 'cm') : null],
                            ['label' => __('frontend.products.width'), 'value' => $product->width ? \Illuminate\Support\Number::format((float) $product->width) . ' ' . ($product->width_unit?->value ?? 'cm') : null],
                            ['label' => __('frontend.products.depth'), 'value' => $product->length ? \Illuminate\Support\Number::format((float) $product->length) . ' ' . ($product->depth_unit?->value ?? 'cm') : null],
                            ['label' => __('translations.weight'), 'value' => $this->formatMeasurement($product->weight, $product->weight_unit?->value ?? null)],
                            ['label' => __('Minimum quantity'), 'value' => $product->getMinimumQuantity() > 1 ? $product->getMinimumQuantity() : null],
                        ];
                    @endphp

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <h2 class="text-lg font-semibold text-slate-900">{{ __('Technical Details') }}</h2>
                            <dl class="grid gap-4 sm:grid-cols-2">
                                @foreach($technicalMetrics as $metric)
                                    @if(filled($metric['value']))
                                        <div class="rounded-2xl bg-slate-50/80 p-4">
                                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $metric['label'] }}</dt>
                                            <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $metric['value'] }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <h2 class="text-lg font-semibold text-slate-900">{{ __('Change History Snapshot') }}</h2>
                                <a href="{{ route('localized.products.history', ['locale' => app()->getLocale(), 'product' => $product->trans('slug') ?? $product->slug]) }}" class="inline-flex items-center gap-2 text-sm font-medium text-primary-600 hover:text-primary-700">
                                    <x-heroicon-o-clock class="h-4 w-4" />
                                    {{ __('frontend.products.view_full_history') }}
                                </a>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('frontend.total_changes') }}</p>
                                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $product->getChangeCount(30) }}</p>
                                    <p class="text-xs text-slate-400">{{ __('frontend.last_30_days') }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('frontend.price_changes') }}</p>
                                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $product->getPriceChangeCount(30) }}</p>
                                    <p class="text-xs text-slate-400">{{ __('frontend.last_30_days') }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Change frequency (30 days)') }}</p>
                                    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $product->getChangeFrequency(30) }}</p>
                                    <p class="text-xs text-slate-400">{{ __('Average changes per day (last 30 days)') }}</p>
                                </div>
                            </div>

                            @if($recentHistories->isNotEmpty())
                                <ul class="space-y-3">
                                    @foreach($recentHistories as $history)
                                        <li class="flex items-start justify-between rounded-2xl border border-slate-100 bg-slate-50/60 p-3 text-xs text-slate-600">
                                            <div class="flex items-center gap-2">
                                                @switch($history->action)
                                                    @case('price_changed')
                                                        <x-heroicon-o-currency-euro class="h-4 w-4 text-emerald-500" />
                                                        @break
                                                    @case('stock_updated')
                                                        <x-heroicon-o-cube class="h-4 w-4 text-sky-500" />
                                                        @break
                                                    @case('status_changed')
                                                        <x-heroicon-o-check-circle class="h-4 w-4 text-amber-500" />
                                                        @break
                                                    @default
                                                        <x-heroicon-o-pencil class="h-4 w-4 text-slate-400" />
                                                @endswitch
                                                <span class="font-medium text-slate-700">{{ __('frontend.events.' . $history->action) }}</span>
                                            </div>
                                            <span class="text-slate-400">{{ $history->created_at->diffForHumans() }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-6 p-6 lg:p-8">
                            <h2 class="text-lg font-semibold text-slate-900">{{ __('All Variants & Options') }}</h2>
                            @if($this->variantMatrix->isEmpty())
                                <p class="text-sm text-slate-600">{{ __('This product is available in a single configuration. Variant options will appear here for configurable products.') }}</p>
                            @else
                                <div class="space-y-4">
                                    @foreach($this->variantMatrix as $variant)
                                        <div class="flex flex-col gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-5 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-start gap-4">
                                                @if($variant['thumbnail'])
                                                    <img src="{{ $variant['thumbnail'] }}" alt="{{ $variant['name'] }}" class="h-16 w-16 rounded-xl object-cover shadow-sm">
                                                @endif
                                                <div class="space-y-2">
                                                    <div>
                                                        <p class="text-base font-semibold text-slate-900">{{ $variant['name'] }}</p>
                                                        @if($variant['sku'])
                                                            <p class="text-xs text-slate-500">{{ __('translations.sku') }}: {{ $variant['sku'] }}</p>
                                                        @endif
                                                    </div>
                                                    <dl class="flex flex-wrap gap-2 text-xs text-slate-600">
                                                        @foreach($variant['attributes'] as $attribute)
                                                            <div class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 shadow-sm">
                                                                <span class="font-medium text-slate-700">{{ $attribute['attribute'] }}:</span>
                                                                <span>{{ $attribute['value'] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </dl>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                @if($variant['price'])
                                                    <p class="text-lg font-semibold text-primary-600">{{ $variant['price'] }}</p>
                                                @endif
                                                @if($variant['compare_price'])
                                                    <p class="text-xs text-slate-500 line-through">{{ $variant['compare_price'] }}</p>
                                                @endif
                                                <p class="mt-2 text-xs font-medium uppercase tracking-wide {{ $variant['is_out_of_stock'] ? 'text-red-500' : 'text-emerald-500' }}">
                                                    {{ $variant['is_out_of_stock'] ? __('translations.out_of_stock') : __('translations.available') }}
                                                </p>
                                                <p class="text-xs text-slate-400">{{ __('translations.available') }}: {{ $variant['available_quantity'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>

                    @if($product->documents && $product->documents->isNotEmpty())
                        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                            <div class="space-y-6 p-6 lg:p-8">
                                <h2 class="text-lg font-semibold text-slate-900">{{ __('Downloads & Guides') }}</h2>
                                <ul class="space-y-3 text-sm text-slate-600">
                                    @foreach($product->documents as $document)
                                        <li class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                            <div class="flex items-center gap-3">
                                                <x-heroicon-o-document-text class="h-5 w-5 text-primary-500" />
                                                <span class="font-medium text-slate-700">{{ $document->name }}</span>
                                            </div>
                                            <a href="{{ $document->url }}" target="_blank" rel="noopener" class="text-sm font-medium text-primary-600 hover:text-primary-700">
                                                {{ __('translations.download') }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    @endif

                    @if ((bool) (config('app-features.features.review') ?? true))
                        <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                            <div class="space-y-6 p-6 lg:p-8">
                                <h2 class="text-lg font-semibold text-slate-900">{{ __('Customer Feedback') }}</h2>
                                <livewire:components.product.reviews :productId="$product->id" />
                                <livewire:components.product.review-form :productId="$product->id" />
                            </div>
                        </section>
                    @endif
                </div>

                <div class="lg:col-span-5 space-y-6">
                    <section class="rounded-3xl border border-slate-100 bg-white shadow-lg lg:sticky lg:top-24">
                        <div class="space-y-6 p-6 lg:p-8">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('translations.brand') }}</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ $brandLabel ?? __('Unknown brand') }}</p>
                                </div>
                                @if($currentPrice)
                                    <p class="text-3xl font-semibold text-primary-600">
                                        {{ \Illuminate\Support\Number::currency((float) $currentPrice, $currentCurrency, app()->getLocale()) }}
                                    </p>
                                @endif
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 text-sm text-slate-600">
                                <p class="flex items-center gap-2 text-slate-700">
                                    <x-heroicon-o-shield-check class="h-5 w-5 text-primary-500" />
                                    {{ __('Original supplier certified quality guarantee.') }}
                                </p>
                            </div>
                            <div class="variant-selector-card">
                                <livewire:components.variants-selector :product="$product" />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">{{ __('Quick Facts') }}</h2>
                            <dl class="grid gap-4 sm:grid-cols-2">
                                @foreach($this->productQuickFacts as $fact)
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $fact['label'] }}</dt>
                                        <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $fact['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">{{ __('Shipping & Service') }}</h2>
                            <div class="space-y-4">
                                <div class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <x-untitledui-globe-05 class="h-6 w-6 text-slate-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ __('frontend.products.international_delivery') }}</p>
                                        <p class="text-sm text-slate-600">{{ __('frontend.products.delivery_eta_2_weeks') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                                    <x-untitledui-gift-02 class="h-6 w-6 text-slate-500" />
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ __('frontend.products.loyalty_rewards') }}</p>
                                        <p class="text-sm text-slate-600">{{ __('frontend.products.loyalty_rewards_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <div class="space-y-4 p-6 lg:p-8">
                            <h2 class="text-base font-semibold text-slate-900">{{ __('Need a tailored offer?') }}</h2>
                            <p class="text-sm text-slate-600">{{ __('Contact our specialists for bulk pricing, project support, or technical consultations.') }}</p>
                            <a href="{{ $contactUrl }}" class="inline-flex items-center justify-center rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                                <x-heroicon-o-phone class="mr-2 h-4 w-4" />
                                {{ __('translations.contact_us') }}
                            </a>
                        </div>
                    </section>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 hover:text-slate-900">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    {{ __('frontend.buttons.back_to_products') }}
                </a>
            </div>
        </x-container>
    </div>

    <livewire:components.related-products :product="$product" />

    @if($product->brand)
        <div class="bg-slate-50">
            <livewire:components.advanced-related-products :product="$product" type="brand" :limit="4" class="bg-slate-50" />
        </div>
    @endif

    @if($product->categories->isNotEmpty())
        <livewire:components.advanced-related-products :product="$product" type="category" :limit="4" />
    @endif
</div>

@push('styles')
    <style>
        .variant-selector-card h1 {
            display: none;
        }

        .variant-selector-card form {
            margin-top: 1.5rem;
        }
    </style>
@endpush

@push('scripts')
    @php
        $price = $product->getPrice();
        $image =
            $product->getFirstMediaUrl(config('media.storage.collection_name'), 'large') ?:
            $product->getFirstMediaUrl(config('media.storage.collection_name'));
        $brandName = $product->brand?->trans('name') ?? $product->brand?->name;
        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->trans('name') ?? $product->name,
            'image' => $image ? [$image] : [],
            'description' => Str::limit(strip_tags($product->trans('description') ?? $product->description), 300),
        ];
        if ($brandName) {
            $productSchema['brand'] = [
                '@type' => 'Brand',
                'name' => $brandName,
            ];
        }
        if ($price) {
            $productSchema['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => current_currency(),
                'price' => number_format($price->value->amount, 2, '.', ''),
                'availability' => 'https://schema.org/' . ($product->isPublished() ? 'InStock' : 'OutOfStock'),
                'url' => url()->current(),
            ];
        }
        $recentReviews = \App\Models\Review::query()
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->latest('id')
            ->limit(5)
            ->get(['title', 'content', 'rating', 'created_at']);
        $reviewsSchema = null;
        if ($recentReviews->isNotEmpty()) {
            $reviewsSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'itemListElement' => $recentReviews
                    ->map(function ($r) {
                        return [
                            '@type' => 'Review',
                            'name' => $r->title,
                            'reviewBody' => Str::limit(strip_tags($r->content), 300),
                            'reviewRating' => [
                                '@type' => 'Rating',
                                'ratingValue' => (int) $r->rating,
                                'bestRating' => '5',
                            ],
                            'datePublished' => optional($r->created_at)->toDateString(),
                        ];
                    })
                    ->toArray(),
            ];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($productSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @if ($reviewsSchema)
        <script type="application/ld+json">{!! json_encode($reviewsSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endpush

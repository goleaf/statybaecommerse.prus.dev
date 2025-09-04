@section('meta')
    @php($ogImage = $product->getFirstMediaUrl(config('media.storage.collection_name'), 'large') ?: $product->getFirstMediaUrl(config('media.storage.collection_name')))
    <x-meta
            :title="$product->trans('seo_title') ?? $product->name"
            :description="$product->trans('seo_description') ?? Str::limit(strip_tags($product->description), 150)"
            :og-image="$ogImage"
            ogType="product"
            canonical="{{ url()->current() }}"
            :preload-image="(string) ($ogImage ?: '')" />
@endsection

<div class="bg-white" wire:loading.attr="aria-busy" aria-busy="false">
    @if (session('status'))
        <x-container><x-alert type="success" class="mb-4">{{ session('status') }}</x-alert></x-container>
    @endif
    @if (session('error'))
        <x-container><x-alert type="error" class="mb-4">{{ session('error') }}</x-alert></x-container>
    @endif
    <div class="pb-16 pt-10 sm:pb-24">
        <x-container class="mt-8 max-w-2xl">
            <x-breadcrumbs :items="[
                ['label' => __('Products'), 'url' => route('home', ['locale' => app()->getLocale()])],
                [
                    'label' => $product->brand?->trans('name') ?? $product->brand?->name,
                    'url' => $product->brand
                        ? route('brand.show', [
                            'locale' => app()->getLocale(),
                            'slug' => $product->brand->trans('slug') ?? $product->brand->slug,
                        ])
                        : null,
                ],
                ['label' => $product->trans('name') ?? $product->name],
            ]" />
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-8">
                <div class="lg:col-span-3">
                    <aside class="space-y-10 lg:sticky lg:top-40" aria-labelledby="product-description">
                        <!-- Product details -->
                        <div>
                            <h2 class="text-sm font-medium text-gray-900">{{ __('Description') }}</h2>

                            <div class="prose prose-sm mt-4 text-gray-500">
                                {!! $product->trans('description') ?? $product->description !!}
                            </div>
                        </div>

                        <x-product.additionnal-infos
                                                     :product="$product"
                                                     :categories="$product->categories->pluck('name')->join(', ')" />
                    </aside>
                </div>

                <!-- Product gallery -->
                <div class="lg:col-span-6 lg:px-8">
                    <div wire:loading role="status" aria-live="polite" class="mb-4 text-sm text-gray-600">
                        {{ __('Loading…') }}
                    </div>

                    {{-- Enhanced Image Gallery Component --}}
                    <livewire:components.product-image-gallery
                                                               :product="$product"
                                                               image-size="xl" />

                    {{-- Alternative: Static Component --}}
                    {{-- <x-product.detail-images :product="$product" /> --}}

                    @if ((bool) (config('app-features.features.review') ?? true))
                        <livewire:components.product.reviews :productId="$product->id" />
                        <livewire:components.product.review-form :productId="$product->id" />
                    @endif
                </div>

                <div class="lg:col-span-3">
                    <aside class="space-y-10 lg:sticky lg:top-40" aria-labelledby="product-variant">
                        <livewire:components.variants-selector :$product />

                        <!-- Policies -->
                        <section aria-labelledby="policies-heading">
                            <h2 id="policies-heading" class="sr-only">{{ __('Our privacy') }}</h2>

                            <dl class="space-y-4">
                                <div class="border border-gray-200 bg-gray-50 p-6">
                                    <dt class="flex items-center gap-2">
                                        <x-untitledui-globe-05 class="size-6 text-gray-400" stroke-width="1.5"
                                                               aria-hidden="true" />
                                        <span
                                              class="text-sm font-medium text-gray-900">{{ __('International delivery') }}</span>
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        {{ __('Get your order in 2 weeks') }}
                                    </dd>
                                </div>
                                <div class="border border-gray-200 bg-gray-50 p-6">
                                    <dt class="flex items-center gap-2">
                                        <x-untitledui-gift-02 class="size-6 text-gray-400" stroke-width="1.5"
                                                              aria-hidden="true" />
                                        <span
                                              class="text-sm font-medium text-gray-900">{{ __('Loyalty rewards') }}</span>
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        {{ __('Don\'t look at other tees') }}
                                    </dd>
                                </div>
                            </dl>
                        </section>
                    </aside>
                </div>
            </div>
        </x-container>
    </div>
</div>

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

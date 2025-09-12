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
            <div class="lg:grid lg:grid-cols-12 lg:gap-x-8">
                <div class="lg:col-span-3">
                    <aside class="space-y-10 lg:sticky lg:top-40" aria-labelledby="product-description">
                        <!-- Product details -->
                        <div>
                            <h2 class="text-sm font-medium text-gray-900">{{ __('frontend.products.description') }}</h2>

                            <div class="prose prose-sm mt-4 text-gray-500">
                                {!! $product->trans('description') ?? $product->description !!}
                            </div>
                            
                            <!-- Product History Link -->
                            <div class="mt-4">
                                <a href="{{ route('localized.products.history', [
                                    'locale' => app()->getLocale(),
                                    'product' => $product->trans('slug') ?? $product->slug,
                                ]) }}" 
                                   class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
                                    <x-heroicon-s-clock class="mr-1 h-4 w-4" />
                                    {{ __('frontend.products.view_history') }}
                                </a>
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
                        {{ __('frontend.buttons.loading') }}
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
                        <livewire:components.variants-selector :product="$product" />

                        <!-- Policies -->
                        <section aria-labelledby="policies-heading">
                            <h2 id="policies-heading" class="sr-only">{{ __('frontend.products.policies_title') }}</h2>

                            <dl class="space-y-4">
                                <div class="border border-gray-200 bg-gray-50 p-6">
                                    <dt class="flex items-center gap-2">
                                        <x-untitledui-globe-05 class="size-6 text-gray-400" stroke-width="1.5"
                                                               aria-hidden="true" />
                                        <span
                                              class="text-sm font-medium text-gray-900">{{ __('frontend.products.international_delivery') }}</span>
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        {{ __('frontend.products.delivery_eta_2_weeks') }}
                                    </dd>
                                </div>
                                <div class="border border-gray-200 bg-gray-50 p-6">
                                    <dt class="flex items-center gap-2">
                                        <x-untitledui-gift-02 class="size-6 text-gray-400" stroke-width="1.5"
                                                              aria-hidden="true" />
                                        <span
                                              class="text-sm font-medium text-gray-900">{{ __('frontend.products.loyalty_rewards') }}</span>
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        {{ __('frontend.products.loyalty_rewards_desc') }}
                                    </dd>
                                </div>
                            </dl>
                        </section>
                    </aside>
                </div>
            </div>
        </x-container>
    </div>

    {{-- Related Products Section --}}
    <livewire:components.related-products :product="$product" />

    {{-- Additional Product Recommendations --}}
    @if($product->brand)
        <div class="bg-gray-50">
            <livewire:components.advanced-related-products 
                :product="$product" 
                type="brand" 
                :limit="4" 
                class="bg-gray-50" />
        </div>
    @endif

    @if($product->categories->isNotEmpty())
        <livewire:components.advanced-related-products 
            :product="$product" 
            type="category" 
            :limit="4" />
    @endif
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

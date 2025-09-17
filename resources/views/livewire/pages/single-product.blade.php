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
                            
                            <!-- Product History Section -->
                            <div class="mt-6 border-t border-gray-200 pt-4">
                                <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('frontend.products.history_title') }}</h3>
                                
                                <!-- History Statistics -->
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-xs text-gray-500">{{ __('frontend.total_changes') }}</div>
                                        <div class="text-lg font-semibold text-gray-900">{{ $product->getChangeCount(30) }}</div>
                                        <div class="text-xs text-gray-400">{{ __('frontend.last_30_days') }}</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-xs text-gray-500">{{ __('frontend.price_changes') }}</div>
                                        <div class="text-lg font-semibold text-gray-900">{{ $product->getPriceChangeCount(30) }}</div>
                                        <div class="text-xs text-gray-400">{{ __('frontend.last_30_days') }}</div>
                                    </div>
                                </div>

                                <!-- Recent Changes Preview -->
                                @if($product->hasRecentChanges(7))
                                    <div class="mb-4">
                                        <div class="text-xs text-gray-500 mb-2">{{ __('frontend.products.recent_changes') }}</div>
                                        <div class="space-y-2">
                                            @foreach($product->recentHistories()->limit(3)->get() as $history)
                                                <div class="flex items-center justify-between text-xs">
                                                    <div class="flex items-center">
                                                        @switch($history->action)
                                                            @case('price_changed')
                                                                <x-heroicon-s-currency-euro class="h-3 w-3 text-green-500 mr-1" />
                                                                @break
                                                            @case('stock_updated')
                                                                <x-heroicon-s-cube class="h-3 w-3 text-blue-500 mr-1" />
                                                                @break
                                                            @case('status_changed')
                                                                <x-heroicon-s-check-circle class="h-3 w-3 text-yellow-500 mr-1" />
                                                                @break
                                                            @default
                                                                <x-heroicon-s-pencil class="h-3 w-3 text-gray-500 mr-1" />
                                                        @endswitch
                                                        <span class="text-gray-600">{{ __('frontend.events.' . $history->action) }}</span>
                                                    </div>
                                                    <span class="text-gray-400">{{ $history->created_at->diffForHumans() }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- View Full History Link -->
                                <a href="{{ route('localized.products.history', [
                                    'locale' => app()->getLocale(),
                                    'product' => $product->trans('slug') ?? $product->slug,
                                ]) }}" 
                                   class="inline-flex items-center justify-center w-full px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100 hover:text-indigo-700 transition-colors">
                                    <x-heroicon-s-clock class="mr-2 h-4 w-4" />
                                    {{ __('frontend.products.view_full_history') }}
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

    <!-- Back Button -->
    <div class="mt-8 text-center">
        <a href="{{ route('products.index', ['locale' => app()->getLocale()]) }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition duration-200">
            <x-heroicon-o-arrow-left class="w-4 h-4 mr-2" />
            {{ __('frontend.buttons.back_to_products') }}
        </a>
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

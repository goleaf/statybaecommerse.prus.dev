<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Pricing\VariantPriceService;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * SingleProduct
 *
 * Livewire component for SingleProduct with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property Product $product
 * @property int     $quantity
 */
final class SingleProduct extends Component
{
    use WithCart;

    public Product $product;

    public int $quantity = 1;

    public ?int $activeVariantId = null;

    public string $stockStatus = 'unavailable';

    public string $stockMessage = '';

    /**
     * Structured pricing summary for the currently selected context (product or variant).
     *
     * @var array{current: float|null, compare: float|null, discount: float|null, currency: string|null}
     */
    public array $pricingSummary = [
        'current'  => null,
        'compare'  => null,
        'discount' => null,
        'currency' => null,
    ];

    /**
     * Inventory summary for the active product context.
     *
     * @var array{reserved: int|null, available: int|null}
     */
    public array $inventorySummary = [
        'reserved'  => null,
        'available' => null,
    ];

    protected ?SupportCollection $recentHistoriesCollection = null;

    protected ?SupportCollection $recentApprovedReviewsCollection = null;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(Product $product): void
    {
        abort_if(! $product->is_visible, 404);

        $locale = app()->getLocale();
        $productId = $product->getKey();

        $productTags = array_merge(
            [CacheTags::locale($locale), CacheTags::products()],
            CacheTags::productIds([$productId]),
            [CacheTags::categories(), CacheTags::brands()]
        );

        // Cache the heavy product aggregate to avoid repeating the eager-loading work.
        $this->product = Cache::tags($productTags)->remember(
            CacheKeys::productDetail($productId, $locale),
            now()->addSeconds(180),
            static function () use ($product): Product {
                return Product::query()
                    ->whereKey($product)
                    ->with([
                        'brand.translations',
                        'categories.translations',
                        'translations',
                        'media',
                        'documents',
                        'attributes' => fn ($query) => $query->with([
                            'translations',
                            'values' => fn ($valueQuery) => $valueQuery->with('translations'),
                        ]),
                        'variants' => fn ($variantQuery) => $variantQuery->with([
                            'media',
                            'prices.currency',
                            'variantAttributeValues.attribute.translations',
                        ]),
                    ])
                    ->withCount([
                        'reviews as approved_reviews_count' => fn ($query) => $query->approved(),
                    ])
                    ->withAvg([
                        'reviews as approved_reviews_avg_rating' => fn ($query) => $query->approved(),
                    ], 'rating')
                    ->firstOrFail();
            }
        );

        $this->recentHistoriesCollection = Cache::tags($productTags)->remember(
            CacheKeys::productRecentHistories($productId),
            now()->addSeconds(120),
            function (): SupportCollection {
                return $this->product
                    ->recentHistories()
                    ->orderByDesc('created_at')
                    ->limit(4)
                    ->get(['id', 'product_id', 'action', 'field_name', 'old_value', 'new_value', 'description', 'created_at']);
            }
        );

        $this->recentApprovedReviewsCollection = Cache::tags([
            CacheTags::locale($locale),
            CacheTags::reviews(),
            CacheTags::products(),
            ...CacheTags::productIds([$productId]),
        ])->remember(
            CacheKeys::productRecentReviews($productId),
            now()->addSeconds(120),
            function (): SupportCollection {
                return $this->product
                    ->reviews()
                    ->latest('id')
                    ->limit(5)
                    ->get(['id', 'product_id', 'title', 'content', 'rating', 'created_at']);
            }
        );

        $this->trackProductView();
        $this->trackProductViewHistory();

        $this->activeVariantId = $this->determineDefaultVariantId();
        $this->refreshVariantState($this->activeVariantId);
    }

    /**
     * Handle trackProductView functionality with proper error handling.
     */
    public function trackProductView(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        // Track in session for recently viewed products
        $viewedProducts = session('recently_viewed', []);
        // Remove if already exists and add to front
        $viewedProducts = array_filter($viewedProducts, fn ($id) => $id !== $this->product->id);
        array_unshift($viewedProducts, $this->product->id);
        // Keep only last 10 viewed products
        $viewedProducts = array_slice($viewedProducts, 0, 10);
        session(['recently_viewed' => $viewedProducts]);
        // Track analytics event if analytics is enabled
        if (class_exists(\App\Models\AnalyticsEvent::class)) {
            \App\Models\AnalyticsEvent::create([
                'event_type' => 'product_view',
                'event_data' => [
                    'product_id'       => $this->product->id,
                    'product_name'     => $this->product->name,
                    'product_category' => $this->product->categories->pluck('name')->join(', '),
                    'user_id'          => auth()->id(),
                    'session_id'       => session()->getId(),
                    'referrer'         => request()->header('referer'),
                ],
                'user_id'    => auth()->id(),
                'session_id' => session()->getId(),
            ]);
        }
    }

    /**
     * Handle trackProductViewHistory functionality with proper error handling.
     */
    public function trackProductViewHistory(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        // Only track if user is authenticated to avoid spam
        if (! auth()->check()) {
            return;
        }
        // Check if we already tracked this view in the last hour
        $lastView = \App\Models\ProductHistory::where('product_id', $this->product->id)->where('user_id', auth()->id())->where('action', 'viewed')->where('created_at', '>', now()->subHour())->first();
        if ($lastView) {
            return;
        }
        // Create history entry for product view
        \App\Models\ProductHistory::create(['product_id' => $this->product->id, 'user_id' => auth()->id(), 'action' => 'viewed', 'field_name' => 'page_view', 'old_value' => null, 'new_value' => 'product_page', 'description' => 'Product page viewed', 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'metadata' => ['referrer' => request()->header('referer'), 'session_id' => session()->getId(), 'view_timestamp' => now()->toISOString()], 'causer_type' => \App\Models\User::class, 'causer_id' => auth()->id()]);
    }

    /**
     * Handle trackAddToCartHistory functionality with proper error handling.
     */
    public function trackAddToCartHistory(Product $product, int $quantity): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        // Only track if user is authenticated
        if (! auth()->check()) {
            return;
        }
        // Create history entry for add to cart
        \App\Models\ProductHistory::create(['product_id' => $product->id, 'user_id' => auth()->id(), 'action' => 'added_to_cart', 'field_name' => 'cart_quantity', 'old_value' => null, 'new_value' => (string) $quantity, 'description' => "Added {$quantity} item(s) to cart", 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'metadata' => ['product_name' => $product->name, 'product_sku' => $product->sku, 'unit_price' => $product->price, 'total_price' => $product->price * $quantity, 'session_id' => session()->getId(), 'cart_timestamp' => now()->toISOString()], 'causer_type' => \App\Models\User::class, 'causer_id' => auth()->id()]);
    }

    /**
     * Handle addToCart functionality with proper error handling.
     */
    public function addToCart(): void
    {
        // Check if product should hide add to cart
        if ($this->product->shouldHideAddToCart()) {
            $this->addError('quantity', __('frontend.product.cannot_add_to_cart'));

            return;
        }
        $this->validate(['quantity' => 'required|integer|min:1|max:' . $this->product->availableQuantity()]);
        // Check minimum quantity
        if ($this->quantity < $this->product->getMinimumQuantity()) {
            $this->addError('quantity', __('frontend.product.minimum_quantity_required', ['min' => $this->product->getMinimumQuantity()]));

            return;
        }
        // Call the trait method directly
        $this->addToCartTrait($this->product->id, $this->quantity);
    }

    /**
     * Handle addToCartTrait functionality with proper error handling.
     */
    private function addToCartTrait(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);
        if ($product->availableQuantity() < $quantity) {
            $this->addError('quantity', __('frontend.product.not_enough_stock'));

            return;
        }
        // Create or update cart item in database
        $cartItem = \App\Models\CartItem::updateOrCreate(['session_id' => session()->getId(), 'product_id' => $productId], ['quantity' => \App\Models\CartItem::where('session_id', session()->getId())->where('product_id', $productId)->sum('quantity') + $quantity, 'minimum_quantity' => $product->getMinimumQuantity(), 'unit_price' => $product->price, 'total_price' => $product->price * $quantity, 'product_snapshot' => ['name' => $product->name, 'sku' => $product->sku, 'image' => $product->getFirstMediaUrl('images')]]);
        $cartItem->updateTotalPrice();
        // Track add to cart in history
        $this->trackAddToCartHistory($product, $quantity);
        $this->dispatch('cart-updated');
    }

    /**
     * Handle relatedProducts functionality with proper error handling.
     */
    #[Computed]
    public function relatedProducts(): Collection
    {
        return $this->product->getRelatedProducts(4);
    }

    #[Computed]
    public function attributeFeatures(): \Illuminate\Support\Collection
    {
        if (! $this->product->relationLoaded('attributes')) {
            $this->product->loadMissing(['attributes.values']);
        }

        if (! $this->product->relationLoaded('variants')) {
            $this->product->loadMissing(['variants.variantAttributeValues.attribute']);
        }

        $variantFeatures = collect();

        $variantFeatures = $this
            ->product
            ->variants
            ->flatMap(function (ProductVariant $variant) {
                if (! $variant->relationLoaded('variantAttributeValues')) {
                    $variant->loadMissing(['variantAttributeValues.attribute']);
                }

                return $variant
                    ->variantAttributeValues
                    ->map(function ($value): array {
                        $attribute = $value->attribute;

                        return [
                            'id'    => $attribute?->id,
                            'label' => $attribute?->trans('name') ?? $attribute?->name ?? $value->attribute_name,
                            'value' => $value->getLocalizedValue(),
                            'icon'  => $attribute?->icon,
                            'color' => $attribute?->color,
                        ];
                    });
            })
            ->filter(fn (array $feature) => filled($feature['id']) && filled($feature['value']))
            ->groupBy('id')
            ->map(function ($group): array {
                $first = $group->first();

                return [
                    'id'    => $first['id'],
                    'label' => $first['label'],
                    'value' => $group->pluck('value')->filter()->unique()->implode(', '),
                    'icon'  => $first['icon'],
                    'color' => $first['color'],
                ];
            });

        $variantFeaturesById = $variantFeatures->keyBy('id');

        $productFeatures = $this
            ->product
            ->attributes
            ->map(function ($attribute) use ($variantFeaturesById): array {
                $value = null;
                $valueId = $attribute->pivot->attribute_value_id ?? null;

                if ($valueId) {
                    $valueModel = $attribute->values->firstWhere('id', $valueId);
                    $value = $valueModel ? ($valueModel->trans('value') ?? $valueModel->value) : null;
                }

                if (! filled($value) && $variantFeaturesById->has($attribute->id)) {
                    $value = $variantFeaturesById->get($attribute->id)['value'];
                }

                $icon = $attribute->icon;
                $color = $attribute->color;

                if ($variantFeaturesById->has($attribute->id)) {
                    $variantFeature = $variantFeaturesById->get($attribute->id);
                    $icon = $icon ?: $variantFeature['icon'];
                    $color = $color ?: $variantFeature['color'];
                }

                return [
                    'id'    => $attribute->id,
                    'label' => $attribute->trans('name') ?? $attribute->name,
                    'value' => $value,
                    'icon'  => $icon,
                    'color' => $color,
                ];
            })
            ->filter(fn (array $feature) => filled($feature['value']))
            ->keyBy('id');

        $combined = $productFeatures->union($variantFeaturesById);

        return $combined
            ->filter(fn (array $feature) => filled($feature['value']))
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    #[Computed]
    public function variantMatrix(): \Illuminate\Support\Collection
    {
        if (! $this->product->relationLoaded('variants')) {
            $this->product->loadMissing(['variants.media', 'variants.variantAttributeValues.attribute', 'variants.prices.currency']);
        }

        return $this
            ->product
            ->variants
            ->map(function (ProductVariant $variant): array {
                $price = $variant->getPrice();
                $currentCurrency = function_exists('current_currency') ? current_currency() : null;
                $priceValue = $price?->value?->amount ?? $variant->price;
                $priceFormatted = $priceValue !== null ? app_money_format((float) $priceValue, $currentCurrency) : null;
                $compareFormatted = null;

                if ($price && $price->compare) {
                    $compareFormatted = app_money_format((float) $price->compare, $currentCurrency);
                } elseif ($variant->compare_price) {
                    $compareFormatted = app_money_format((float) $variant->compare_price, $currentCurrency);
                }

                $thumbnail = $variant->getFirstMediaUrl(config('media.storage.thumbnail_collection'))
                    ?: ($variant->getFirstMediaUrl(config('media.storage.collection_name'), 'small')
                        ?: $variant->getFirstMediaUrl(config('media.storage.collection_name')));

                $attributes = $variant
                    ->variantAttributeValues
                    ->map(function ($value): array {
                        $attribute = $value->attribute;

                        return [
                            'attribute' => $attribute?->trans('name') ?? $attribute?->name ?? $value->attribute_name,
                            'value'     => $value->getLocalizedValue(),
                        ];
                    })
                    ->values();

                return [
                    'id'                 => $variant->id,
                    'name'               => $variant->name,
                    'sku'                => $variant->sku,
                    'price'              => $priceFormatted,
                    'compare_price'      => $compareFormatted,
                    'is_out_of_stock'    => $variant->isOutOfStock(),
                    'available_quantity' => $variant->availableQuantity(),
                    'thumbnail'          => $thumbnail,
                    'attributes'         => $attributes,
                ];
            })
            ->values();
    }

    #[Computed]
    public function recentHistories(): SupportCollection
    {
        return $this->recentHistoriesCollection ??= collect();
    }

    #[Computed]
    public function recentApprovedReviewsLimited(): SupportCollection
    {
        return $this->recentApprovedReviewsCollection ??= collect();
    }

    #[On('variant.selected')]
    public function handleVariantSelected(?int $variantId): void
    {
        $this->refreshVariantState($variantId);
    }

    protected function determineDefaultVariantId(): ?int
    {
        if (! isset($this->product) || ! $this->product->relationLoaded('variants')) {
            return null;
        }

        $variants = $this->product->variants;

        if ($variants->isEmpty()) {
            return null;
        }

        $defaultVariant = $variants->firstWhere('is_default_variant', true)
            ?? $variants->firstWhere('is_default', true)
            ?? $variants->first();

        return $defaultVariant?->id;
    }

    protected function refreshVariantState(?int $variantId): void
    {
        $variant = $this->resolveVariantById($variantId);

        $this->activeVariantId = $variant?->id;
        $this->pricingSummary = $this->buildPricingSummary($variant);
        $this->inventorySummary = $this->buildInventorySummary($variant);
        $this->updateStockState($variant);
    }

    protected function resolveVariantById(?int $variantId): ?ProductVariant
    {
        if (! $variantId) {
            return null;
        }

        $variants = $this->product->relationLoaded('variants')
            ? $this->product->variants
            : collect();

        $variant = $variants->firstWhere('id', $variantId);

        if ($variant) {
            return $variant;
        }

        $variant = $this->product
            ->variants()
            ->with(['variantAttributeValues.attribute', 'prices.currency', 'images'])
            ->find($variantId);

        if ($variant && $this->product->relationLoaded('variants')) {
            $this->product->setRelation(
                'variants',
                $this->product->variants->push($variant)->unique('id')->values()
            );
        }

        return $variant;
    }

    protected function buildPricingSummary(?ProductVariant $variant = null): array
    {
        $currency = function_exists('current_currency') ? current_currency() : null;

        if ($variant) {
            /** @var VariantPriceService $priceService */
            $priceService = app(VariantPriceService::class);

            // Build the pricing context so the service can honour quantity-driven rules and locale currency.
            $context = ['quantity' => $this->quantity];

            if ($currency) {
                $context['currency'] = $currency;
            }

            $result = $priceService->calculate($variant, $context);

            $current = (float) $result->finalPrice;
            $compare = $result->compareAtPrice !== null ? (float) $result->compareAtPrice : null;

            // Fall back to other reference prices (regular, sale, or price-list) when compare-at is absent but
            // the server-calculated figure indicates a higher anchor price for discount messaging.
            if ($compare === null) {
                $fallbackAnchors = array_filter([
                    $result->regularPrice,
                    $result->salePrice,
                    $result->priceListPrice,
                ], static fn (?float $amount) => $amount !== null && $amount > ($current + 0.0001));

                if ($fallbackAnchors !== []) {
                    $compare = (float) max($fallbackAnchors);
                }
            }

            $discount = null;

            if ($compare !== null && $compare > ($current + 0.0001)) {
                $discount = round((($compare - $current) / $compare) * 100);
            }

            return [
                'current'  => $current,
                'compare'  => $compare,
                'discount' => $discount,
                'currency' => $result->currency ?: $currency,
            ];
        }

        $priceData = $this->product->getPrice();
        $current = $priceData?->value ?? ($this->product->price !== null ? (float) $this->product->price : null);
        $compare = $priceData?->compare ?? ($this->product->compare_price !== null ? (float) $this->product->compare_price : null);
        $discount = $priceData?->percentage;

        if ($discount === null && $compare && $current && $compare > $current) {
            $discount = round((($compare - $current) / $compare) * 100);
        }

        return [
            'current'  => $current,
            'compare'  => $compare,
            'discount' => $discount,
            'currency' => $currency,
        ];
    }

    protected function buildInventorySummary(?ProductVariant $variant = null): array
    {
        if ($variant) {
            return [
                'reserved'  => $variant->reservedQuantity(),
                'available' => $variant->availableQuantity(),
            ];
        }

        return [
            'reserved'  => method_exists($this->product, 'reservedQuantity') ? $this->product->reservedQuantity() : null,
            'available' => method_exists($this->product, 'availableQuantity') ? $this->product->availableQuantity() : null,
        ];
    }

    protected function updateStockState(?ProductVariant $variant = null): void
    {
        if ($variant) {
            $available = $variant->availableQuantity();
            $threshold = (int) ($variant->low_stock_threshold ?? 0);

            if ($available <= 0) {
                $this->stockStatus = 'out_of_stock';
                $this->stockMessage = __('product_variants.messages.out_of_stock');

                return;
            }

            if ($threshold > 0 && $available <= $threshold) {
                $this->stockStatus = 'low_stock';
                $this->stockMessage = __('product_variants.messages.low_stock', ['quantity' => $available]);

                return;
            }

            $this->stockStatus = 'in_stock';
            $this->stockMessage = __('product_variants.messages.in_stock', ['quantity' => $available]);

            return;
        }

        if ($this->product->relationLoaded('variants') && $this->product->variants->isNotEmpty()) {
            $this->stockStatus = 'unavailable';
            $this->stockMessage = __('product_variants.messages.select_variant');

            return;
        }

        $this->stockStatus = $this->resolveStockStatus();
        $this->stockMessage = $this->resolveStockMessage();
    }

    protected function resolveStockStatus(): string
    {
        $status = $this->product->getStockStatus();

        return $status === 'not_tracked' ? 'in_stock' : $status;
    }

    protected function resolveStockMessage(): string
    {
        if (! $this->product->manage_stock) {
            return __('translations.in_stock');
        }

        $available = $this->product->availableQuantity();

        if ($available <= 0) {
            return __('translations.out_of_stock');
        }

        $threshold = (int) ($this->product->low_stock_threshold ?? 0);

        if ($threshold > 0 && $this->product->isLowStock()) {
            return __('product_variants.messages.low_stock', ['quantity' => $available]);
        }

        return __('product_variants.messages.in_stock', ['quantity' => $available]);
    }

    #[Computed]
    public function productQuickFacts(): array
    {
        $brandName = $this->product->brand?->trans('name') ?? $this->product->brand?->name;
        $categoryNames = $this
            ->product
            ->categories
            ->map(fn ($category) => $category->trans('name') ?? $category->name)
            ->filter()
            ->implode(', ');

        $facts = [
            ['label' => __('translations.brand'), 'value' => $brandName],
            ['label' => __('translations.category'), 'value' => $categoryNames],
            ['label' => __('translations.sku'), 'value' => $this->product->sku],
            ['label' => __('frontend.availability'), 'value' => $this->product->isInStock() ? __('translations.in_stock') : __('translations.out_of_stock')],
            ['label' => __('translations.weight'), 'value' => $this->formatMeasurement($this->product->weight, $this->product->weight_unit?->value ?? null)],
            ['label' => __('Dimensions'), 'value' => $this->product->getDimensions()],
            ['label' => __('translations.last_updated'), 'value' => $this->product->updated_at?->diffForHumans()],
        ];

        return array_values(array_filter($facts, fn (array $fact) => filled($fact['value'])));
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        if (! $this->product->is_visible) {
            abort(404);
        }

        return view('livewire.pages.single-product', ['relatedProducts' => $this->relatedProducts])->layout('components.layouts.base', ['title' => $this->product->name]);
    }

    private function formatMeasurement(null|int|float|string $value, ?string $unit): ?string
    {
        if ($value === null) {
            return null;
        }

        $numeric = (float) $value;

        if ($numeric <= 0) {
            return null;
        }

        $formatted = rtrim(rtrim(number_format($numeric, 2, '.', ''), '0'), '.');

        return trim($formatted . ' ' . ($unit ?? '')) ?: null;
    }
}

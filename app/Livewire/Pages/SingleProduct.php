<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use App\Services\Pricing\VariantPriceService;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
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
     * Cached route key exposed for Livewire assertions and integrations.
     */
    public ?string $productRouteKey = null;

    public ?string $ogImage = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $variantMatrixSnapshot = [];

    /**
     * @var array<string, string>
     */
    public array $selectedVariantAttributes = [];

    /**
     * @var array<int, array{current: float|null, compare: float|null, discount: float|null, currency: string|null}>
     */
    private array $variantCardPricingCache = [];

    #[Computed]
    public function specifications(): \Illuminate\Support\Collection
    {
        return $this->product->attributes
            ->filter(fn ($attribute) => $attribute->pivot->attribute_value_id !== null)
            ->map(function ($attribute) {
                $valueId = $attribute->pivot->attribute_value_id;
                $valueModel = $attribute->values->firstWhere('id', $valueId);

                return [
                    'label' => $this->resolveAttributeLabel($attribute, null, $attribute->slug),
                    'value' => $valueModel ? ($valueModel->trans('value') ?? $valueModel->value) : null,
                    'icon'  => $attribute->icon,
                ];
            })
            ->filter(fn ($spec) => $spec['value'] !== null)
            ->values();
    }

    #[Computed]
    public function productSchema(): array
    {
        $priceData = $this->product->getPrice();
        $brandName = $this->product->brand?->trans('name') ?? $this->product->brand?->name;
        $rawDescription = $this->product->trans('description') ?? $this->product->description;
        $schemaDescription = is_string($rawDescription)
            ? Str::limit(strip_tags($rawDescription), 300)
            : '';

        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $this->product->trans('name') ?? $this->product->name,
            'image'       => $this->ogImage ? [$this->ogImage] : [],
            'description' => $schemaDescription,
        ];

        if ($brandName) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name'  => $brandName,
            ];
        }

        if ($priceData) {
            $schema['offers'] = [
                '@type'         => 'Offer',
                'priceCurrency' => function_exists('current_currency') ? current_currency() : 'EUR',
                'price'         => number_format((float) ($priceData->value->amount ?? $priceData->value), 2, '.', ''),
                'availability'  => 'https://schema.org/' . ($this->product->isPublished() ? 'InStock' : 'OutOfStock'),
                'url'           => url()->current(),
            ];
        }

        return $schema;
    }

    #[Computed]
    public function contactUrl(): string
    {
        return Route::has('frontend.contact.index')
            ? route('frontend.contact.index')
            : 'mailto:' . (config('mail.from.address') ?? 'info@egisstatyba.lt');
    }

    #[Computed]
    public function brandLabel(): ?string
    {
        return $this->product->brand?->trans('name') ?? $this->product->brand?->name;
    }

    #[Computed]
    public function categoryLabels(): \Illuminate\Support\Collection
    {
        return $this->product->categories
            ->map(fn ($category) => $category->trans('name') ?? $category->name)
            ->filter()
            ->values();
    }

    #[Computed]
    public function shortDescription(): ?string
    {
        return $this->product->trans('short_description') ?? $this->product->short_description;
    }

    #[Computed]
    public function stockToneClass(): string
    {
        return match ($this->stockStatus) {
            'in_stock'  => 'text-emerald-600',
            'low_stock' => 'text-amber-600',
            'out_of_stock', 'unavailable' => 'text-rose-600',
            default => 'text-slate-600',
        };
    }

    #[Computed]
    public function availableQuantity(): int
    {
        return (int) ($this->inventorySummary['available'] ?? 0);
    }

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

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(Product $product): void
    {
        abort_if(! $product->isPublished(), 404);

        $locale = app()->getLocale();
        $productId = $product->getKey();

        $productTags = array_merge(
            [CacheTags::locale($locale), CacheTags::products()],
            CacheTags::productIds([$productId]),
            [CacheTags::categories(), CacheTags::brands()]
        );

        // Cache the heavy product aggregate to avoid repeating the eager-loading work.
        $this->product = TagAwareCache::remember(
            CacheKeys::productDetail($productId, $locale),
            180,
            static function () use ($product): Product {
                return Product::query()
                    ->whereKey($product)
                    ->with([
                        'brand.translations',
                        'categories.translations',
                        'translations',
                        'media',
                        'attributes' => fn ($query) => $query->with([
                            'translations',
                            'values' => fn ($valueQuery) => $valueQuery->with('translations'),
                        ]),
                        'variants' => fn ($variantQuery) => $variantQuery->with([
                            'media',
                            'inventories',
                            'prices.currency',
                            'variantAttributeValues.attribute.values',
                            'variantAttributeValues.attribute.translations',
                        ]),
                    ])
                    ->firstOrFail();
            },
            $productTags
        );

        $this->trackProductView();
        $this->trackProductViewHistory();

        $this->activeVariantId = $this->determineDefaultVariantId();
        $this->refreshVariantState($this->activeVariantId);
        $this->rebuildVariantMatrixSnapshot();
        $this->selectedVariantAttributes = [];

        $this->ogImage = $this->product->getImageUrl('preview')
            ?: $this->product->getImageUrl();
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
        // Product view tracking removed
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
        // Add to cart history tracking removed
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
     * Dispatch a browser share event that contains the localized product URL.
     */
    public function shareProduct(): void
    {
        // Resolve and cache the canonical route key so downstream listeners can assert state.
        $routeKey = $this->resolveProductRouteKey($this->product);
        $this->productRouteKey = $routeKey;

        // Determine the appropriate URL prioritising localized routes before falling back.
        $shareUrl = $this->resolveProductShareUrl($routeKey);

        // Emit the Livewire browser event with the computed URL payload.
        $this->dispatch('share-product', url: $shareUrl);
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
        $sessionId = (string) session()->getId();
        $userId = auth()->id();

        $existingQuantity = \App\Models\CartItem::withoutGlobalScopes()
            ->where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->whereNull('variant_id')
            ->sum('quantity');

        // Create or update a single base-product cart line.
        $cartItem = \App\Models\CartItem::withoutGlobalScopes()->updateOrCreate(
            [
                'session_id' => $sessionId,
                'user_id'    => is_numeric($userId) ? (int) $userId : null,
                'product_id' => $productId,
                'variant_id' => null,
            ],
            [
                'quantity'         => (int) $existingQuantity + $quantity,
                'minimum_quantity' => $product->getMinimumQuantity(),
                'unit_price'       => $product->price,
                'price'            => $product->price,
                'total_price'      => $product->price * ((int) $existingQuantity + $quantity),
                'product_snapshot' => [
                    'name'  => $product->name,
                    'sku'   => $product->sku,
                    'image' => $this->resolveProductCartImage($product),
                ],
            ]
        );
        $cartItem->updateTotalPrice();
        // Track add to cart in history
        $this->trackAddToCartHistory($product, $quantity);
        $this->dispatch('add-to-cart', productId: $productId, quantity: $quantity);
        $this->dispatch('cart-updated');
    }

    private function resolveProductCartImage(Product $product): ?string
    {
        $candidates = [
            $product->getImageUrl('thumb'),
            $product->getImageUrl('preview'),
            $product->main_image,
            $product->thumbnail,
            $product->getImageUrl(),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Derive the most reliable route key for the current product instance.
     */
    private function resolveProductRouteKey(Product $product): string
    {
        // Prefer the standard route key, falling back to slug or attribute lookups when needed.
        $routeKey = $product->getRouteKey() ?: ($product->slug ?? $product->getAttribute($product->getRouteKeyName()));

        if (empty($routeKey) && $product->exists) {
            // Persisted products always have a primary key we can expose to ensure deterministic URLs.
            $routeKey = (string) $product->getKey();
        }

        return (string) ($routeKey ?? '');
    }

    /**
     * Resolve the shareable URL, prioritising localized routes with safe fallbacks.
     */
    private function resolveProductShareUrl(string $routeKey): string
    {
        $resolvedUrl = null;

        if ($routeKey !== '' && Route::has('frontend.products.show')) {
            try {
                // Attempt to use the canonical localized route first.
                $resolvedUrl = route('frontend.products.show', [
                    'product' => $routeKey,
                ]);
            } catch (UrlGenerationException) {
                // Ignore invalid parameters and continue through the fallback chain.
            }
        }

        if (! $resolvedUrl && $routeKey !== '' && Route::has('frontend.products.show')) {
            try {
                // Defer to the storefront-facing route before exposing internal defaults.
                $resolvedUrl = route('frontend.products.show', ['product' => $routeKey]);
            } catch (UrlGenerationException) {
                // Continue falling back when the route requires additional parameters.
            }
        }

        if (! $resolvedUrl && $routeKey !== '' && Route::has('products.show')) {
            try {
                // As a final named-route fallback, use the generic product show route.
                $resolvedUrl = route('products.show', ['product' => $routeKey]);
            } catch (UrlGenerationException) {
                // Ignore and fall through to the manual URL builder.
            }
        }

        if (! $resolvedUrl) {
            // Default to the conventional /products/{slug} pattern when routes are unavailable.
            $resolvedUrl = $routeKey !== ''
                ? url(sprintf('/products/%s', $routeKey))
                : url('/products');
        }

        if ($routeKey !== '' && ! str_contains($resolvedUrl, $routeKey)) {
            // Guarantee the share link highlights the same product even if route helpers stripped the slug.
            $resolvedUrl = url(sprintf('/products/%s', $routeKey));
        }

        return $resolvedUrl;
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
            $this->product->loadMissing(['attributes.translations', 'attributes.values']);
        }

        if (! $this->product->relationLoaded('variants')) {
            $this->product->loadMissing(['variants.variantAttributeValues.attribute.translations']);
        }

        $variantFeatures = collect();

        $variantFeatures = $this
            ->product
            ->variants
            ->flatMap(function (ProductVariant $variant) {
                if (! $variant->relationLoaded('variantAttributeValues')) {
                    $variant->loadMissing(['variantAttributeValues.attribute.translations']);
                }

                return $variant
                    ->variantAttributeValues
                    ->map(function (VariantAttributeValue $value): array {
                        $attribute = $value->attribute;
                        $attributeSlug = $attribute?->slug ?? Str::slug((string) ($value->attribute_name ?? 'attribute'));
                        $valueSlug = $value->attribute_value_slug ?? Str::slug((string) ($value->getLocalizedValue() ?? $value->attribute_value ?? 'value'));

                        return [
                            'id'    => $attribute?->id,
                            'label' => $this->resolveVariantAttributeLabel($attribute, $value, $attributeSlug),
                            'value' => $this->resolveVariantAttributeValue(null, $value, $attributeSlug, $valueSlug),
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
                    'label' => $this->resolveAttributeLabel($attribute, null, $attribute->slug),
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
        if ($this->variantMatrixSnapshot === []) {
            $this->rebuildVariantMatrixSnapshot();
        }

        return collect($this->variantMatrixSnapshot)
            ->map(fn (array $variant): array => [
                ...$variant,
                'is_active' => (int) ($variant['id'] ?? 0) === (int) ($this->activeVariantId ?? 0),
            ])
            ->values();
    }

    private function rebuildVariantMatrixSnapshot(): void
    {
        if (! $this->product->relationLoaded('variants')) {
            $this->product->loadMissing([
                'variants.media',
                'variants.inventories',
                'variants.prices.currency',
                'variants.variantAttributeValues.attribute.translations',
                'variants.variantAttributeValues.attribute.values.translations',
            ]);
        } else {
            $this->product->variants->loadMissing([
                'media',
                'inventories',
                'prices.currency',
                'variantAttributeValues.attribute.translations',
                'variantAttributeValues.attribute.values.translations',
            ]);
        }

        $this->variantCardPricingCache = [];

        $this->variantMatrixSnapshot = $this
            ->product
            ->variants
            ->map(fn (ProductVariant $variant): array => $this->formatVariantForDisplay($variant))
            ->values()
            ->all();
    }

    #[Computed]
    public function activeVariantData(): ?array
    {
        $activeVariantId = $this->activeVariantId;
        $activeVariant = $this->variantMatrix->first(
            static fn (array $variant): bool => (int) ($variant['id'] ?? 0) === (int) ($activeVariantId ?? 0)
        );

        if (! is_array($activeVariant)) {
            $activeVariant = $this->variantMatrix->first();
        }

        return is_array($activeVariant) ? $activeVariant : null;
    }

    #[Computed]
    public function variantOptionGroups(): SupportCollection
    {
        $variants = $this->variantMatrix;

        if ($variants->isEmpty()) {
            return collect();
        }

        $groups = [];

        foreach ($variants as $variantData) {
            foreach ($variantData['attributes'] as $attribute) {
                $groupSlug = $this->normalizeAttributeTranslationKey((string) ($attribute['attribute_slug'] ?? ''));
                $groupKey = (string) ($attribute['attribute_id'] ?? $groupSlug);

                if ($groupKey === '' || $groupSlug === '') {
                    continue;
                }

                if (! isset($groups[$groupKey])) {
                    $groups[$groupKey] = [
                        'id'         => $attribute['attribute_id'],
                        'name'       => $attribute['attribute'],
                        'slug'       => $groupSlug,
                        'sort_order' => $attribute['attribute_sort_order'],
                        'values'     => [],
                    ];
                }

                $valueKey = $this->normalizeVariantOptionKey($attribute['value_slug'] ?? null, $attribute['value'] ?? null);

                if ($valueKey === '') {
                    continue;
                }

                if (! isset($groups[$groupKey]['values'][$valueKey])) {
                    $groups[$groupKey]['values'][$valueKey] = [
                        'key'                 => $valueKey,
                        'label'               => $attribute['value'],
                        'hex_color'           => $attribute['hex_color'],
                        'swatch_image'        => $attribute['swatch_image'],
                        'variant_ids'         => [],
                        'primary_variant_id'  => null,
                        'is_active'           => false,
                        'is_available'        => false,
                        'available_quantity'  => 0,
                        'value_sort_order'    => $attribute['value_sort_order'],
                        'price_samples'       => [],
                        'price_currency_hint' => $variantData['pricing']['currency'] ?? (function_exists('current_currency') ? current_currency() : null),
                    ];
                }

                $valueEntry = &$groups[$groupKey]['values'][$valueKey];
                $valueEntry['variant_ids'][] = $variantData['id'];
                $valueEntry['price_samples'][] = $variantData['pricing']['current'];

                unset($valueEntry);
            }
        }

        $variantCount = max(1, $variants->count());
        $groupMetaByKey = [];

        foreach ($groups as $groupKey => $groupData) {
            $isHighCardinality = $this->isHighCardinalityVariantGroup($groupData, $variantCount);
            $groupMetaByKey[$groupKey] = [
                'is_high_cardinality' => $isHighCardinality,
                'is_constraint'       => ! $isHighCardinality,
                'presentation'        => $isHighCardinality ? 'compact_list' : 'chips',
            ];
        }

        $groupMetaBySlug = collect($groups)
            ->mapWithKeys(static function (array $groupData, string $groupKey) use ($groupMetaByKey): array {
                return [(string) ($groupData['slug'] ?? $groupKey) => $groupMetaByKey[$groupKey] ?? []];
            })
            ->all();

        $activeSelectionMap = $this->normalizeSelectedVariantAttributeMap($this->selectedVariantAttributes);
        $selectedConstraintAttributes = $this->resolveSelectedConstraintAttributes(
            $activeSelectionMap,
            $groupMetaBySlug
        );

        $variantsById = $variants
            ->filter(static fn (array $variant): bool => isset($variant['id']) && is_numeric($variant['id']))
            ->mapWithKeys(static fn (array $variant): array => [(int) $variant['id'] => $variant])
            ->all();

        $groupEligibleVariantIds = [];

        foreach ($groups as $groupKey => $groupData) {
            $groupSlug = (string) ($groupData['slug'] ?? '');
            $isHighCardinality = (bool) ($groupMetaByKey[$groupKey]['is_high_cardinality'] ?? false);

            if ($groupSlug === '' || $isHighCardinality) {
                continue;
            }

            $groupEligibleVariantIds[$groupSlug] = $variants
                ->filter(
                    fn (array $variantData): bool => $this->variantMatchesSelectedAttributes(
                        $variantData,
                        $selectedConstraintAttributes,
                        $groupSlug
                    )
                )
                ->pluck('id')
                ->filter(static fn ($id): bool => is_numeric($id))
                ->map(static fn ($id): int => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        return collect($groups)
            ->map(function (array $group, string $groupKey) use ($activeSelectionMap, $groupEligibleVariantIds, $groupMetaByKey, $variantsById): array {
                $groupMeta = $groupMetaByKey[$groupKey] ?? [
                    'is_high_cardinality' => false,
                    'is_constraint'       => true,
                    'presentation'        => 'chips',
                ];
                $groupSlug = (string) ($group['slug'] ?? '');
                $eligibleVariantLookup = array_flip($groupEligibleVariantIds[$groupSlug] ?? []);

                $values = collect($group['values'])
                    ->map(function (array $value) use ($activeSelectionMap, $eligibleVariantLookup, $groupMeta, $groupSlug, $variantsById): array {
                        $candidateVariantIds = collect($value['variant_ids'] ?? [])
                            ->filter(static fn ($id): bool => is_numeric($id))
                            ->map(static fn ($id): int => (int) $id)
                            ->unique()
                            ->values()
                            ->all();

                        if (! (bool) ($groupMeta['is_high_cardinality'] ?? false)) {
                            $candidateVariantIds = array_values(
                                array_filter(
                                    $candidateVariantIds,
                                    static fn (int $id): bool => isset($eligibleVariantLookup[$id])
                                )
                            );
                        }

                        $candidateVariants = collect($candidateVariantIds)
                            ->map(static fn (int $variantId): ?array => $variantsById[$variantId] ?? null)
                            ->filter(static fn (?array $variant): bool => is_array($variant))
                            ->values();

                        $prices = $candidateVariants
                            ->pluck('pricing.current')
                            ->filter(static fn ($price): bool => is_numeric($price))
                            ->map(static fn ($price): float => (float) $price)
                            ->values()
                            ->all();

                        $priceHint = null;

                        if ($prices !== []) {
                            $minPrice = min($prices);
                            $currency = $candidateVariants
                                ->pluck('pricing.currency')
                                ->filter()
                                ->first()
                                ?? ($value['price_currency_hint'] ?? null);

                            if ($currency) {
                                $priceHint = __('products.page.variant_option_from_price', [
                                    'price' => Number::currency((float) $minPrice, $currency, app()->getLocale()),
                                ]);
                            }
                        }

                        $primaryVariantId = $this->resolvePreferredVariantIdForSelection($candidateVariants);

                        return [
                            'key'                => $value['key'],
                            'label'              => $value['label'],
                            'hex_color'          => $value['hex_color'],
                            'swatch_image'       => $value['swatch_image'],
                            'variant_ids'        => $candidateVariantIds,
                            'primary_variant_id' => $primaryVariantId,
                            'is_active'          => ($activeSelectionMap[$groupSlug] ?? null) === $value['key'],
                            'is_available'       => $candidateVariants->contains(
                                static fn (array $variant): bool => (bool) ($variant['is_available'] ?? false)
                            ),
                            'available_quantity' => (int) $candidateVariants->sum(
                                static fn (array $variant): int => (int) ($variant['available_quantity'] ?? 0)
                            ),
                            'price_hint'       => $priceHint,
                            'value_sort_order' => $value['value_sort_order'],
                        ];
                    })
                    ->sortBy(static fn (array $value): array => [$value['value_sort_order'] ?? 0, $value['label'] ?? ''])
                    ->values();

                if ($groupMeta['is_high_cardinality']) {
                    $values = $values
                        ->filter(static fn (array $value): bool => ($value['primary_variant_id'] ?? null) !== null)
                        ->values();
                }

                $selectedValueKey = $activeSelectionMap[$groupSlug] ?? null;
                $selectedValue = is_string($selectedValueKey)
                    ? $values->firstWhere('key', $selectedValueKey)
                    : null;

                return [
                    'id'                   => $group['id'],
                    'name'                 => $group['name'],
                    'slug'                 => $group['slug'],
                    'sort_order'           => $group['sort_order'],
                    'presentation'         => $groupMeta['presentation'],
                    'is_high_cardinality'  => $groupMeta['is_high_cardinality'],
                    'is_constraint'        => $groupMeta['is_constraint'],
                    'selected_value_key'   => $selectedValueKey,
                    'selected_value_label' => is_array($selectedValue)
                        ? ($selectedValue['label'] ?? null)
                        : null,
                    'values' => $values,
                ];
            })
            ->sortBy(static fn (array $group): array => [$group['sort_order'] ?? 0, $group['name'] ?? ''])
            ->values();
    }

    #[Computed]
    public function filteredVariantData(): SupportCollection
    {
        $variants = $this->variantMatrix;

        if ($variants->isEmpty()) {
            return collect();
        }

        $selectedAttributes = $this->normalizeSelectedVariantAttributeMap($this->selectedVariantAttributes);

        if ($selectedAttributes === []) {
            return $variants->values();
        }

        return $variants
            ->filter(fn (array $variantData): bool => $this->variantMatchesSelectedAttributes($variantData, $selectedAttributes, ''))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $variantData
     * @return array<string, string>
     */
    private function extractVariantAttributeSelectionMap(array $variantData): array
    {
        $selection = [];

        foreach (($variantData['attributes'] ?? []) as $attribute) {
            if (! is_array($attribute)) {
                continue;
            }

            $attributeSlug = $this->normalizeAttributeTranslationKey((string) ($attribute['attribute_slug'] ?? ''));
            $valueKey = $this->normalizeVariantOptionKey($attribute['value_slug'] ?? null, $attribute['value'] ?? null);

            if ($attributeSlug === '' || $valueKey === '') {
                continue;
            }

            $selection[$attributeSlug] = $valueKey;
        }

        return $selection;
    }

    /**
     * @param  array<string, mixed>  $selection
     * @return array<string, string>
     */
    private function normalizeSelectedVariantAttributeMap(array $selection): array
    {
        $normalizedSelection = [];

        foreach ($selection as $attributeSlug => $value) {
            if (! is_string($attributeSlug)) {
                continue;
            }

            $normalizedAttributeSlug = $this->normalizeAttributeTranslationKey($attributeSlug);
            $normalizedValue = $this->normalizeVariantOptionKey($value, $value);

            if ($normalizedAttributeSlug === '' || $normalizedValue === '') {
                continue;
            }

            $normalizedSelection[$normalizedAttributeSlug] = $normalizedValue;
        }

        return $normalizedSelection;
    }

    /**
     * @param  array<string, string>               $selection
     * @param  array<string, array<string, mixed>> $groupMetaBySlug
     * @return array<string, string>
     */
    private function resolveSelectedConstraintAttributes(array $selection, array $groupMetaBySlug): array
    {
        $selectedConstraintAttributes = collect($selection)
            ->filter(static function (string $selectedValue, string $attributeSlug) use ($groupMetaBySlug): bool {
                if ($selectedValue === '') {
                    return false;
                }

                return (bool) ($groupMetaBySlug[$attributeSlug]['is_constraint'] ?? true);
            })
            ->all();

        if ($selectedConstraintAttributes === []) {
            return [];
        }

        $requiredPrimarySlugs = collect(['color', 'size'])
            ->filter(static fn (string $slug): bool => (bool) ($groupMetaBySlug[$slug]['is_constraint'] ?? false))
            ->values();

        if ($requiredPrimarySlugs->count() >= 2) {
            foreach ($requiredPrimarySlugs as $requiredSlug) {
                if (! isset($selectedConstraintAttributes[$requiredSlug])) {
                    return [];
                }
            }
        }

        return $selectedConstraintAttributes;
    }

    /**
     * @param array<string, mixed>  $variantData
     * @param array<string, string> $selectedAttributes
     */
    private function variantMatchesSelectedAttributes(array $variantData, array $selectedAttributes, string $ignoredAttributeSlug): bool
    {
        if ($selectedAttributes === []) {
            return true;
        }

        $variantSelection = $this->extractVariantAttributeSelectionMap($variantData);

        foreach ($selectedAttributes as $attributeSlug => $selectedValue) {
            if ($attributeSlug === $ignoredAttributeSlug) {
                continue;
            }

            if (($variantSelection[$attributeSlug] ?? null) !== $selectedValue) {
                return false;
            }
        }

        return true;
    }


    /**
     * @param SupportCollection<int, array<string, mixed>> $candidateVariants
     */
    private function resolvePreferredVariantIdForSelection(SupportCollection $candidateVariants): ?int
    {
        if ($candidateVariants->isEmpty()) {
            return null;
        }

        $activeVariantId = $this->activeVariantId;

        if ($activeVariantId !== null) {
            $activeCandidate = $candidateVariants->first(
                static fn (array $variant): bool => (int) ($variant['id'] ?? 0) === (int) $activeVariantId
            );

            if (is_array($activeCandidate)) {
                return (int) $activeVariantId;
            }
        }

        $availableCandidate = $candidateVariants->first(
            static fn (array $variant): bool => (bool) ($variant['is_available'] ?? false)
        );

        if (is_array($availableCandidate) && isset($availableCandidate['id']) && is_numeric($availableCandidate['id'])) {
            return (int) $availableCandidate['id'];
        }

        $fallback = $candidateVariants->first();

        return is_array($fallback) && isset($fallback['id']) && is_numeric($fallback['id'])
            ? (int) $fallback['id']
            : null;
    }

    private function normalizeVariantOptionKey(mixed $valueSlug, mixed $value): string
    {
        if (is_string($valueSlug)) {
            $trimmed = trim($valueSlug);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed !== '') {
                return Str::slug($trimmed);
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $group
     */
    private function isHighCardinalityVariantGroup(array $group, int $variantCount): bool
    {
        $slug = $this->normalizeAttributeTranslationKey((string) ($group['slug'] ?? ''));

        if (in_array($slug, ['size_type'], true)) {
            return true;
        }

        $values = collect($group['values'] ?? []);
        $valueCount = $values->count();

        if ($valueCount < 2) {
            return false;
        }

        $numericLikeCount = $values->filter(function (array $value): bool {
            $candidate = $this->firstFilledString([$value['key'] ?? null, $value['label'] ?? null]);

            return $this->isNumericString($candidate);
        })->count();

        $numericRatio = $numericLikeCount / max(1, $valueCount);
        $uniqueRatio = $valueCount / max(1, $variantCount);

        return $valueCount >= 12 || ($numericRatio >= 0.7 && $uniqueRatio >= 0.45);
    }

    #[On('variant.selected')]
    public function handleVariantSelected(?int $variantId): void
    {
        $this->refreshVariantState($variantId);
        $this->syncSelectionFromActiveVariant();
    }

    /**
     * Allow the Blade view to request variant changes while keeping nested components in sync.
     */
    public function selectVariant(?int $variantId = null): void
    {
        // Update the current pricing and inventory snapshot for the chosen variant.
        $this->refreshVariantState($variantId);
        $this->syncSelectionFromActiveVariant();

        // Propagate the selection to child Livewire components listening for the shared event channel.
        $this->dispatch('variantSelected', $variantId);
    }

    public function selectVariantOption(string $attributeSlug, string $valueKey, ?int $preferredVariantId = null): void
    {
        $normalizedAttributeSlug = $this->normalizeAttributeTranslationKey($attributeSlug);
        $normalizedValueKey = $this->normalizeVariantOptionKey($valueKey, $valueKey);

        if ($normalizedAttributeSlug === '' || $normalizedValueKey === '') {
            return;
        }

        $currentValue = $this->selectedVariantAttributes[$normalizedAttributeSlug] ?? null;

        if ($currentValue === $normalizedValueKey) {
            unset($this->selectedVariantAttributes[$normalizedAttributeSlug]);
        } else {
            $this->selectedVariantAttributes[$normalizedAttributeSlug] = $normalizedValueKey;
        }

        $this->selectedVariantAttributes = $this->normalizeSelectedVariantAttributeMap($this->selectedVariantAttributes);

        $groupMetaBySlug = collect($this->variantOptionGroups)
            ->mapWithKeys(static fn (array $group): array => [
                (string) ($group['slug'] ?? '') => [
                    'is_constraint' => (bool) ($group['is_constraint'] ?? true),
                ],
            ])
            ->all();

        $selectedConstraintAttributes = $this->resolveSelectedConstraintAttributes(
            $this->selectedVariantAttributes,
            $groupMetaBySlug
        );

        if ($selectedConstraintAttributes === []) {
            return;
        }

        $candidateVariants = $this->variantMatrix
            ->filter(fn (array $variantData): bool => $this->variantMatchesSelectedAttributes($variantData, $selectedConstraintAttributes, ''))
            ->values();

        if ($candidateVariants->isEmpty()) {
            return;
        }

        $targetVariantId = null;

        if ($preferredVariantId !== null) {
            $preferredMatch = $candidateVariants->first(
                static fn (array $variantData): bool => (int) ($variantData['id'] ?? 0) === $preferredVariantId
            );

            if (is_array($preferredMatch)) {
                $targetVariantId = $preferredVariantId;
            }
        }

        $targetVariantId ??= $this->resolvePreferredVariantIdForSelection($candidateVariants);

        if ($targetVariantId === null) {
            return;
        }

        $this->refreshVariantState($targetVariantId);
        $this->syncSelectionFromActiveVariant();
        $this->dispatch('variantSelected', $targetVariantId);
    }

    public function clearVariantSelection(?string $attributeSlug = null): void
    {
        if ($attributeSlug === null || trim($attributeSlug) === '') {
            $this->selectedVariantAttributes = [];

            return;
        }

        $normalizedAttributeSlug = $this->normalizeAttributeTranslationKey($attributeSlug);
        unset($this->selectedVariantAttributes[$normalizedAttributeSlug]);
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

    private function syncSelectionFromActiveVariant(): void
    {
        $activeVariant = $this->activeVariantData;

        if (! is_array($activeVariant)) {
            return;
        }

        $activeVariantSelection = $this->extractVariantAttributeSelectionMap($activeVariant);
        $manuallyManagedSelection = collect($this->selectedVariantAttributes)
            ->filter(fn (string $value, string $attributeSlug): bool => ! $this->shouldAutoSyncSelectedAttribute($attributeSlug))
            ->all();
        $autoSyncedSelection = collect($activeVariantSelection)
            ->filter(fn (string $value, string $attributeSlug): bool => $this->shouldAutoSyncSelectedAttribute($attributeSlug))
            ->all();

        $this->selectedVariantAttributes = [
            ...$manuallyManagedSelection,
            ...$autoSyncedSelection,
        ];
    }

    private function shouldAutoSyncSelectedAttribute(string $attributeSlug): bool
    {
        return ! in_array($this->normalizeAttributeTranslationKey($attributeSlug), ['size_type'], true);
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
            ->with(['inventories', 'variantAttributeValues.attribute.values', 'prices.currency', 'images'])
            ->find($variantId);

        if ($variant && $this->product->relationLoaded('variants')) {
            $this->product->setRelation(
                'variants',
                $this->product->variants->push($variant)->unique('id')->values()
            );
        }

        return $variant;
    }

    /**
     * Build lightweight pricing for variant cards without running the full pricing engine per variant.
     *
     * @return array{current: float|null, compare: float|null, discount: float|null, currency: string|null}
     */
    private function buildVariantCardPricingSummary(ProductVariant $variant): array
    {
        $variantId = (int) $variant->getKey();

        if (isset($this->variantCardPricingCache[$variantId])) {
            return $this->variantCardPricingCache[$variantId];
        }

        $current = $variant->getCurrentPrice();
        $compare = $variant->compare_price !== null ? (float) $variant->compare_price : null;
        $discount = null;

        if ($compare !== null && $compare > ($current + 0.0001)) {
            $discount = round((($compare - $current) / $compare) * 100, 0);
        }

        return $this->variantCardPricingCache[$variantId] = [
            'current'  => $current,
            'compare'  => $compare,
            'discount' => $discount,
            'currency' => function_exists('current_currency') ? current_currency() : null,
        ];
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
            $compare = $result->compareAtPrice;

            if ($compare === null) {
                $fallbackAnchors = array_filter([
                    $result->regularPrice,
                    $result->priceListPrice,
                    $variant->compare_price !== null ? (float) $variant->compare_price : null,
                ], static fn (?float $amount): bool => $amount !== null && $amount > ($current + 0.0001));

                if ($fallbackAnchors !== []) {
                    $compare = max($fallbackAnchors);
                }
            }

            $discount = null;

            if ($compare !== null && $compare > ($current + 0.0001)) {
                $discount = round((($compare - $current) / $compare) * 100, 0);
            }

            return [
                'current'  => $current,
                'compare'  => $compare !== null ? (float) $compare : null,
                'discount' => $discount,
                'currency' => $result->currency ?: $currency,
            ];
        }

        $priceData = $this->product->getPrice();
        $current = $priceData?->value ?? ($this->product->price !== null ? (float) $this->product->price : null);
        $compare = $priceData?->compare;
        $discount = $priceData?->percentage;

        if ($discount === null && $compare !== null && $current !== null && $compare > ($current + 0.0001)) {
            $discount = round((($compare - $current) / $compare) * 100, 0);
        }

        return [
            'current'  => $current,
            'compare'  => $compare !== null ? (float) $compare : null,
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
                $this->stockMessage = __('product.variants.messages.out_of_stock');

                return;
            }

            if ($threshold > 0 && $available <= $threshold) {
                $this->stockStatus = 'low_stock';
                $this->stockMessage = __('product.variants.messages.low_stock', ['quantity' => $available]);

                return;
            }

            $this->stockStatus = 'in_stock';
            $this->stockMessage = __('product.variants.messages.in_stock', ['quantity' => $available]);

            return;
        }

        if ($this->product->relationLoaded('variants') && $this->product->variants->isNotEmpty()) {
            $this->stockStatus = 'unavailable';
            $this->stockMessage = __('product.variants.messages.select_variant');

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
            return __('product.variants.messages.low_stock', ['quantity' => $available]);
        }

        return __('product.variants.messages.in_stock', ['quantity' => $available]);
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
            ['label' => __('translations.availability'), 'value' => $this->product->isInStock() ? __('translations.in_stock') : __('translations.out_of_stock')],
            ['label' => __('translations.weight'), 'value' => $this->formatMeasurement($this->product->weight, $this->product->weight_unit?->value ?? null)],
            ['label' => __('translations.dimensions'), 'value' => $this->product->getDimensions()],
        ];

        return array_values(array_filter($facts, fn (array $fact) => filled($fact['value'])));
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        if (! $this->product->isPublished()) {
            abort(404);
        }

        return view('livewire.pages.single-product', ['relatedProducts' => $this->relatedProducts])->layout('components.layouts.base', ['title' => $this->product->name]);
    }

    /**
     * Normalise variant metadata so Blade can reuse a single snapshot for cards and selectors.
     */
    private function formatVariantForDisplay(ProductVariant $variant): array
    {
        if (! $variant->relationLoaded('variantAttributeValues')) {
            $variant->loadMissing(['variantAttributeValues.attribute.translations', 'variantAttributeValues.attribute.values.translations']);
        }

        $pricing = $this->buildVariantCardPricingSummary($variant);
        $currency = $pricing['currency'] ?? (function_exists('current_currency') ? current_currency() : null);
        $currentPrice = $pricing['current'];
        $availableQuantity = $variant->availableQuantity();
        $isOutOfStock = $availableQuantity < 1;
        $isAvailableForPurchase = (bool) $variant->is_enabled
            && (! (bool) $variant->track_inventory || (bool) $variant->allow_backorder || $availableQuantity > 0);

        $priceFormatted = $currentPrice !== null ? app_money_format((float) $currentPrice, $currency) : null;

        $thumbnail = $variant->getFirstMediaUrl(config('media.storage.thumbnail_collection'))
            ?: ($variant->getFirstMediaUrl(config('media.storage.collection_name'), 'small')
                ?: $variant->getFirstMediaUrl(config('media.storage.collection_name')));

        $attributeValues = $variant
            ->variantAttributeValues
            ->map(function (VariantAttributeValue $value): array {
                $attribute = $value->attribute;

                if ($attribute && ! $attribute->relationLoaded('values')) {
                    $attribute->loadMissing('values.translations');
                }

                $attributeSlug = $attribute?->slug ?? Str::slug((string) ($value->attribute_name ?? 'attribute'));
                $valueSlug = $value->attribute_value_slug ?? Str::slug((string) ($value->getLocalizedValue() ?? $value->attribute_value ?? 'value'));

                $attributeValues = ($attribute && $attribute->relationLoaded('values'))
                    ? $attribute->getRelation('values')
                    : collect();

                $attributeValueModel = $attributeValues->firstWhere('slug', $valueSlug)
                    ?? $attributeValues->firstWhere('value', $value->attribute_value)
                    ?? null;

                if (! $attributeValueModel instanceof AttributeValue) {
                    $attributeValueModel = null;
                }

                $label = $this->resolveVariantAttributeLabel($attribute, $value, $attributeSlug);
                $displayValue = $this->resolveVariantAttributeValue($attributeValueModel, $value, $attributeSlug, $valueSlug);

                return [
                    'attribute_id'         => $attribute?->getKey(),
                    'attribute'            => $label,
                    'attribute_slug'       => $attributeSlug,
                    'attribute_sort_order' => $attribute?->sort_order ?? $value->sort_order ?? 0,
                    'value'                => $displayValue,
                    'value_slug'           => $valueSlug,
                    'value_sort_order'     => $value->sort_order ?? $attributeValueModel?->sort_order ?? 0,
                    'hex_color'            => $attributeValueModel?->hex_color,
                    'swatch_image'         => $attributeValueModel?->image,
                ];
            })
            ->values();

        return [
            'id'                 => $variant->getKey(),
            'name'               => $variant->getLocalizedName(),
            'sku'                => $variant->sku,
            'price'              => $priceFormatted,
            'is_out_of_stock'    => $isOutOfStock,
            'is_available'       => $isAvailableForPurchase,
            'available_quantity' => $availableQuantity,
            'thumbnail'          => $thumbnail,
            'attributes'         => $attributeValues,
            'attribute_summary'  => $attributeValues->pluck('value')->filter()->implode(' • '),
            'pricing'            => $pricing,
            'is_active'          => $variant->getKey() === $this->activeVariantId,
        ];
    }

    private function resolveVariantAttributeLabel(?Attribute $attribute, VariantAttributeValue $value, string $attributeSlug): string
    {
        return $this->resolveAttributeLabel($attribute, $value->attribute_name, $attributeSlug);
    }

    private function resolveVariantAttributeValue(
        ?AttributeValue $attributeValueModel,
        VariantAttributeValue $value,
        string $attributeSlug,
        string $valueSlug
    ): string {
        $normalizedAttributeKey = $this->normalizeAttributeTranslationKey($attributeSlug);
        $normalizedValueKey = $this->normalizeAttributeValueTranslationKey($valueSlug !== '' ? $valueSlug : (string) ($value->attribute_value ?? ''));
        $rawVariantValue = $this->firstFilledString([
            $value->getLocalizedValue(),
            $value->attribute_value_display,
            $value->attribute_value,
        ]);

        if ($this->isNumericLiteralAttributeKey($normalizedAttributeKey) && $this->isNumericString($rawVariantValue)) {
            return $rawVariantValue;
        }

        foreach ($this->attributeValueTranslationKeys($normalizedAttributeKey, $normalizedValueKey) as $translationKey) {
            $translated = $this->translatedLineOrNull($translationKey);

            if ($translated !== null) {
                return $translated;
            }
        }

        $candidateValues = [
            $value->getLocalizedValue(),
            $value->attribute_value_display,
            $value->attribute_value,
            $attributeValueModel?->trans('value'),
            $attributeValueModel?->display_value,
            $attributeValueModel?->value,
        ];

        foreach ($candidateValues as $candidate) {
            if (! is_string($candidate)) {
                continue;
            }

            $candidate = trim($candidate);

            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * @param array<int, mixed> $candidates
     */
    private function firstFilledString(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_numeric($candidate)) {
                return (string) $candidate;
            }

            if (! is_string($candidate)) {
                continue;
            }

            $trimmed = trim($candidate);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }

    private function resolveAttributeLabel(?Attribute $attribute, ?string $fallbackName = null, ?string $attributeSlug = null): string
    {
        $normalizedAttributeKey = $this->normalizeAttributeTranslationKey(
            $attribute?->slug
                ?? $attributeSlug
                ?? $fallbackName
                ?? 'attribute'
        );

        foreach ($this->attributeLabelTranslationKeys($normalizedAttributeKey) as $translationKey) {
            $translated = $this->translatedLineOrNull($translationKey);

            if ($translated !== null) {
                return $translated;
            }
        }

        $candidateValues = [
            $attribute?->trans('name'),
            $attribute?->name,
            $fallbackName,
        ];

        foreach ($candidateValues as $candidate) {
            if (! is_string($candidate)) {
                continue;
            }

            $candidate = trim($candidate);

            if ($candidate !== '') {
                return $candidate;
            }
        }

        return Str::headline(str_replace('_', ' ', $normalizedAttributeKey));
    }

    /**
     * @return list<string>
     */
    private function attributeLabelTranslationKeys(string $attributeKey): array
    {
        return [
            "products.attributes.{$attributeKey}",
            "admin.labels.{$attributeKey}",
            "attribute.{$attributeKey}",
            "products.{$attributeKey}",
            "translations.{$attributeKey}",
            "messages.{$attributeKey}",
        ];
    }

    /**
     * @return list<string>
     */
    private function attributeValueTranslationKeys(string $attributeKey, string $valueKey): array
    {
        $keys = ["products.attribute_values.{$attributeKey}.{$valueKey}"];

        if ($this->isBooleanLikeAttributeKey($attributeKey)) {
            $keys[] = "products.attribute_values.common.{$valueKey}";
        }

        return $keys;
    }

    private function isBooleanLikeAttributeKey(string $attributeKey): bool
    {
        return in_array($attributeKey, [
            'allow_backorder',
            'hide_add_to_cart',
            'is_active',
            'is_enabled',
            'is_featured',
            'is_requestable',
            'manage_stock',
            'track_inventory',
        ], true);
    }

    private function isNumericLiteralAttributeKey(string $attributeKey): bool
    {
        return in_array($attributeKey, [
            'pack_size',
            'size',
            'height',
            'length',
            'width',
            'weight',
            'volume',
            'minimum_quantity',
            'stock_quantity',
            'warehouse_quantity',
        ], true);
    }

    private function isNumericString(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $normalized = str_replace(',', '.', trim($value));

        return $normalized !== '' && is_numeric($normalized);
    }

    private function translatedLineOrNull(string $key): ?string
    {
        $translated = __($key);

        if (! is_string($translated) || $translated === $key) {
            return null;
        }

        $translated = trim($translated);

        return $translated !== '' ? $translated : null;
    }

    private function normalizeAttributeTranslationKey(string $key): string
    {
        $normalized = Str::of(Str::snake($key))
            ->ascii()
            ->replaceMatches('/[^a-z0-9_]/', '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->value();

        return $normalized !== '' ? $normalized : 'attribute';
    }

    private function normalizeAttributeValueTranslationKey(string $key): string
    {
        $normalized = Str::of($key)
            ->ascii()
            ->lower()
            ->replace(['-', ' '], '_')
            ->replaceMatches('/[^a-z0-9_]/', '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->value();

        return $normalized !== '' ? $normalized : 'value';
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

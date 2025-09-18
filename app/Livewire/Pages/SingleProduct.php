<?php

declare (strict_types=1);
namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
/**
 * SingleProduct
 * 
 * Livewire component for SingleProduct with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property Product $product
 * @property int $quantity
 */
final class SingleProduct extends Component
{
    use WithCart;
    public Product $product;
    public int $quantity = 1;
    /**
     * Initialize the Livewire component with parameters.
     * @param Product $product
     * @return void
     */
    public function mount(Product $product): void
    {
        // Ensure product is visible and load relationships
        if (!$product->is_visible) {
            abort(404);
        }
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        $product->loadMissing([
            'brand.translations',
            'categories.translations',
            'media',
            'variants.media',
            'variants.values.attribute',
            'variants.prices.currency',
            'reviews',
            'translations',
            'histories',
            'documents',
            'attributes.values',
        ]);
        $this->product = $product;
        // Track product view for recommendations
        $this->trackProductView();
        // Track product view in history
        $this->trackProductViewHistory();
    }
    /**
     * Handle trackProductView functionality with proper error handling.
     * @return void
     */
    public function trackProductView(): void
    {
        // Track in session for recently viewed products
        $viewedProducts = session('recently_viewed', []);
        // Remove if already exists and add to front
        $viewedProducts = array_filter($viewedProducts, fn($id) => $id !== $this->product->id);
        array_unshift($viewedProducts, $this->product->id);
        // Keep only last 10 viewed products
        $viewedProducts = array_slice($viewedProducts, 0, 10);
        session(['recently_viewed' => $viewedProducts]);
        // Track analytics event if analytics is enabled
        if (class_exists(\App\Models\AnalyticsEvent::class)) {
            \App\Models\AnalyticsEvent::create(['event_type' => 'product_view', 'event_data' => ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'product_category' => $this->product->categories->pluck('name')->join(', '), 'user_id' => auth()->id(), 'session_id' => session()->getId(), 'referrer' => request()->header('referer')], 'user_id' => auth()->id(), 'session_id' => session()->getId()]);
        }
    }
    /**
     * Handle trackProductViewHistory functionality with proper error handling.
     * @return void
     */
    public function trackProductViewHistory(): void
    {
        // Only track if user is authenticated to avoid spam
        if (!auth()->check()) {
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
     * @param Product $product
     * @param int $quantity
     * @return void
     */
    public function trackAddToCartHistory(Product $product, int $quantity): void
    {
        // Only track if user is authenticated
        if (!auth()->check()) {
            return;
        }
        // Create history entry for add to cart
        \App\Models\ProductHistory::create(['product_id' => $product->id, 'user_id' => auth()->id(), 'action' => 'added_to_cart', 'field_name' => 'cart_quantity', 'old_value' => null, 'new_value' => (string) $quantity, 'description' => "Added {$quantity} item(s) to cart", 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'metadata' => ['product_name' => $product->name, 'product_sku' => $product->sku, 'unit_price' => $product->price, 'total_price' => $product->price * $quantity, 'session_id' => session()->getId(), 'cart_timestamp' => now()->toISOString()], 'causer_type' => \App\Models\User::class, 'causer_id' => auth()->id()]);
    }
    /**
     * Handle addToCart functionality with proper error handling.
     * @return void
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
     * @param int $productId
     * @param int $quantity
     * @return void
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
     * @return Collection
     */
    #[Computed]
    public function relatedProducts(): Collection
    {
        return $this->product->getRelatedProducts(4);
    }

    #[Computed]
    public function attributeFeatures(): \Illuminate\Support\Collection
    {
        if (!$this->product->relationLoaded('attributes')) {
            $this->product->loadMissing(['attributes.values']);
        }

        return $this->product->attributes
            ->map(function ($attribute): array {
                $valueId = $attribute->pivot->attribute_value_id ?? null;
                $value = null;

                if ($valueId) {
                    $valueModel = $attribute->values->firstWhere('id', $valueId);
                    $value = $valueModel ? ($valueModel->trans('value') ?? $valueModel->value) : null;
                }

                return [
                    'id' => $attribute->id,
                    'label' => $attribute->trans('name') ?? $attribute->name,
                    'value' => $value,
                    'icon' => $attribute->icon,
                ];
            })
            ->filter(fn(array $feature) => filled($feature['value']))
            ->values();
    }

    #[Computed]
    public function variantMatrix(): \Illuminate\Support\Collection
    {
        if (!$this->product->relationLoaded('variants')) {
            $this->product->loadMissing(['variants.media', 'variants.values.attribute', 'variants.prices.currency']);
        }

        return $this->product->variants
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

                $attributes = $variant->values
                    ->map(function ($value): array {
                        return [
                            'attribute' => $value->attribute->trans('name') ?? $value->attribute->name,
                            'value' => $value->trans('value') ?? $value->value,
                        ];
                    })
                    ->values();

                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                    'price' => $priceFormatted,
                    'compare_price' => $compareFormatted,
                    'is_out_of_stock' => $variant->isOutOfStock(),
                    'available_quantity' => $variant->availableQuantity(),
                    'thumbnail' => $thumbnail,
                    'attributes' => $attributes,
                ];
            })
            ->values();
    }

    #[Computed]
    public function productQuickFacts(): array
    {
        $brandName = $this->product->brand?->trans('name') ?? $this->product->brand?->name;
        $categoryNames = $this->product->categories
            ->map(fn($category) => $category->trans('name') ?? $category->name)
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

        return array_values(array_filter($facts, fn(array $fact) => filled($fact['value'])));
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.pages.single-product', ['relatedProducts' => $this->relatedProducts])->layout('components.layouts.templates.app', ['title' => $this->product->name]);
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

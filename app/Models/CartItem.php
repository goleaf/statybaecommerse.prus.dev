<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Builders\CartItemBuilder;
use App\Models\Scopes\UserOwnedScope;
use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CartItem
 *
 * Eloquent model representing the CartItem entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed                     $fillable
 * @property mixed                     $casts
 * @property mixed                     $appends
 * @property array<string, mixed>|null $product_snapshot
 * @property array<string, mixed>|null $attributes
 *
 * @method static CartItemBuilder newModelQuery()
 * @method static CartItemBuilder newQuery()
 * @method static CartItemBuilder query()
 * @method static CartItemBuilder forSession(string $sessionId)
 * @method static CartItemBuilder forUser(int $userId)
 * @method static CartItemBuilder forProduct(int $productId)
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['session_id', 'user_id', 'product_id', 'variant_id', 'product_variant_id', 'quantity', 'minimum_quantity', 'unit_price', 'discount_amount', 'total_price', 'price', 'product_snapshot', 'notes', 'attributes'];

    /**
     * Provide native attribute casting for frequently accessed fields.
     */
    protected function casts(): array
    {
        return [
            'quantity'         => 'integer',
            'minimum_quantity' => 'integer',
            'unit_price'       => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'total_price'      => 'decimal:2',
            'price'            => 'decimal:2',
            'product_snapshot' => 'array',
            'attributes'       => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Align persisted price metrics whenever the record is created or updated through Filament forms.
        self::saving(static function (CartItem $item): void {
            $item->synchronizePriceAttributes();
        });
    }

    public function newEloquentBuilder($query): CartItemBuilder
    {
        return new CartItemBuilder($query);
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['formatted_total_price', 'formatted_unit_price', 'subtotal', 'product_name', 'product_sku'];

    /**
     * Handle user functionality with proper error handling.
     */
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handle product functionality with proper error handling.
     */
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle variant functionality with proper error handling.
     */
    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Handle productVariant functionality with proper error handling.
     */
    /**
     * @return BelongsTo<ProductVariant, $this>
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Handle updateTotalPrice functionality with proper error handling.
     */
    public function updateTotalPrice(): void
    {
        // Synchronise monetary attributes and persist the refreshed totals.
        $this->synchronizePriceAttributes();
        $this->save();
    }

    /**
     * Handle needsRestocking functionality with proper error handling.
     */
    public function needsRestocking(): bool
    {
        return $this->quantity < $this->getMinimumQuantity();
    }

    /**
     * Handle getMinimumQuantity functionality with proper error handling.
     */
    public function getMinimumQuantity(): int
    {
        return max(1, (int) ($this->minimum_quantity ?? 1));
    }

    /**
     * Handle getFormattedTotalPriceAttribute functionality with proper error handling.
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return app_money_format((float) ($this->total_price ?? 0.0));
    }

    /**
     * Handle getFormattedUnitPriceAttribute functionality with proper error handling.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return app_money_format((float) ($this->unit_price ?? 0.0));
    }

    /**
     * Handle getSubtotalAttribute functionality with proper error handling.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->calculateSubtotal();
    }

    /**
     * Handle scopeForSession functionality with proper error handling.
     */
    public function scopeForSession(CartItemBuilder $query, string $sessionId): CartItemBuilder
    {
        // Filter cart items that belong to a specific browser session identifier.
        return $query->where('session_id', $sessionId);
    }

    /**
     * Handle scopeForUser functionality with proper error handling.
     */
    public function scopeForUser(CartItemBuilder $query, int $userId): CartItemBuilder
    {
        // Filter cart items for the given authenticated user identifier.
        return $query->where('user_id', $userId);
    }

    /**
     * Handle scopeForProduct functionality with proper error handling.
     */
    public function scopeForProduct(CartItemBuilder $query, int $productId): CartItemBuilder
    {
        // Filter cart items that reference a specific product record.
        return $query->where('product_id', $productId);
    }

    /**
     * Handle updateQuantity functionality with proper error handling.
     */
    public function updateQuantity(int $quantity): void
    {
        // Normalise the requested quantity and persist the refreshed totals.
        $this->quantity = max(0, $quantity);
        $this->updateTotalPrice();
    }

    /**
     * Handle incrementQuantity functionality with proper error handling.
     */
    public function incrementQuantity(int $amount = 1): void
    {
        // Increment the quantity by the provided amount and keep totals consistent.
        $this->quantity += $amount;
        $this->updateTotalPrice();
    }

    /**
     * Handle decrementQuantity functionality with proper error handling.
     */
    public function decrementQuantity(int $amount = 1): void
    {
        // Safely decrement the quantity and clean up rows that reach zero items.
        $this->quantity = max(0, $this->quantity - $amount);

        if ($this->quantity === 0) {
            $this->forceDelete();

            return;
        }

        $this->updateTotalPrice();
    }

    /**
     * Handle calculateSubtotal functionality with proper error handling.
     */
    public function calculateSubtotal(): float
    {
        // Calculate the subtotal using the resolved price value before discounts.
        $price = (float) ($this->price ?? $this->unit_price ?? 0.0);
        $quantity = max(0, (int) ($this->quantity ?? 0));

        return $price * $quantity;
    }

    /**
     * Normalise price, discount, and total monetary values on the model instance.
     */
    public function synchronizePriceAttributes(): void
    {
        // Always treat quantities below zero as zero to avoid negative totals.
        $this->quantity = max(0, (int) ($this->quantity ?? 0));

        // Resolve a base unit price and ensure fallbacks behave consistently.
        $unitPrice = round((float) ($this->unit_price ?? $this->price ?? 0.0), 2);
        $this->unit_price = $unitPrice;

        // If callers adjust the unit price without explicitly overriding "price", keep them in sync.
        $resolvedPriceSource = $this->price;
        if ($this->isDirty('unit_price') && ! $this->isDirty('price')) {
            $resolvedPriceSource = null;
        }

        // Ensure the "price" column mirrors the current unit price when not explicitly provided.
        $resolvedPrice = round((float) ($resolvedPriceSource ?? $unitPrice), 2);
        $this->price = $resolvedPrice;

        // Respect stored discount values, guarding against negative totals.
        $discount = round((float) ($this->discount_amount ?? 0.0), 2);
        $rawTotal = $resolvedPrice * $this->quantity;
        $this->total_price = max(0.0, round($rawTotal - $discount, 2));
    }

    /**
     * Provide a convenient accessor for the product name using the relation or snapshot cache.
     */
    public function getProductNameAttribute(): ?string
    {
        // Attempt to read the live relationship first, falling back to the stored snapshot for resiliency.
        if ($this->product !== null) {
            return $this->product->name;
        }

        $snapshot = $this->product_snapshot;
        if (! is_array($snapshot)) {
            return null;
        }

        $name = $snapshot['name'] ?? null;

        return is_string($name) ? $name : null;
    }

    /**
     * Provide a convenient accessor for the product SKU using the relation or snapshot cache.
     */
    public function getProductSkuAttribute(): ?string
    {
        // Attempt to read the live relationship first, falling back to the stored snapshot for resiliency.
        if ($this->product !== null) {
            return $this->product->sku;
        }

        $snapshot = $this->product_snapshot;
        if (! is_array($snapshot)) {
            return null;
        }

        $sku = $snapshot['sku'] ?? null;

        return is_string($sku) ? $sku : null;
    }
}

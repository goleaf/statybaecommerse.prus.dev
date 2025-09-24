<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
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
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class CartItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['session_id', 'user_id', 'product_id', 'variant_id', 'product_variant_id', 'quantity', 'minimum_quantity', 'unit_price', 'total_price', 'price', 'product_snapshot', 'notes', 'attributes'];

    protected $casts = ['quantity' => 'integer', 'minimum_quantity' => 'integer', 'unit_price' => 'decimal:2', 'total_price' => 'decimal:2', 'price' => 'decimal:2', 'product_snapshot' => 'array', 'attributes' => 'array'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['formatted_total_price', 'formatted_unit_price', 'subtotal'];

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle variant functionality with proper error handling.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Handle productVariant functionality with proper error handling.
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
        $this->total_price = $this->unit_price * $this->quantity;
        $this->save();
    }

    /**
     * Handle needsRestocking functionality with proper error handling.
     */
    public function needsRestocking(): bool
    {
        return $this->quantity < $this->minimum_quantity;
    }

    /**
     * Handle getMinimumQuantity functionality with proper error handling.
     */
    public function getMinimumQuantity(): int
    {
        return $this->minimum_quantity ?? 1;
    }

    /**
     * Handle getFormattedTotalPriceAttribute functionality with proper error handling.
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return app_money_format($this->total_price);
    }

    /**
     * Handle getFormattedUnitPriceAttribute functionality with proper error handling.
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return app_money_format($this->unit_price);
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
     *
     * @param  mixed  $query
     */
    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Handle scopeForUser functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Handle scopeForProduct functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Handle updateQuantity functionality with proper error handling.
     */
    public function updateQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->updateTotalPrice();
    }

    /**
     * Handle incrementQuantity functionality with proper error handling.
     */
    public function incrementQuantity(int $amount = 1): void
    {
        $this->quantity += $amount;
        $this->updateTotalPrice();
    }

    /**
     * Handle decrementQuantity functionality with proper error handling.
     */
    public function decrementQuantity(int $amount = 1): void
    {
        $this->quantity = max(0, $this->quantity - $amount);
        if ($this->quantity === 0) {
            $this->forceDelete();
        } else {
            $this->updateTotalPrice();
        }
    }

    /**
     * Handle calculateSubtotal functionality with proper error handling.
     */
    public function calculateSubtotal(): float
    {
        $price = $this->price ?? $this->unit_price;

        return $price * $this->quantity;
    }
}

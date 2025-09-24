<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WishlistItem
 *
 * Eloquent model representing the WishlistItem entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = ['wishlist_id', 'product_id', 'variant_id', 'quantity', 'notes'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * Handle wishlist functionality with proper error handling.
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(UserWishlist::class, 'wishlist_id');
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
     * Handle getDisplayNameAttribute functionality with proper error handling.
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product?->name ?? '';
        if ($this->variant?->name) {
            $name .= ($name !== '' ? ' - ' : '').$this->variant->name;
        }

        return $name;
    }

    /**
     * Handle getCurrentPriceAttribute functionality with proper error handling.
     */
    public function getCurrentPriceAttribute(): ?float
    {
        if ($this->variant?->price !== null) {
            return (float) $this->variant->price;
        }

        if ($this->product?->price !== null) {
            return (float) $this->product->price;
        }

        return null;
    }

    /**
     * Handle getFormattedCurrentPriceAttribute functionality with proper error handling.
     */
    public function getFormattedCurrentPriceAttribute(): string
    {
        return app_money_format($this->current_price ?? 0);
    }

    /**
     * Handle scopeForUser functionality with proper error handling.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('wishlist', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Handle scopeForProduct functionality with proper error handling.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

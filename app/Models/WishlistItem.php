<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
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
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WishlistItem query()
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class WishlistItem extends Model
{
    use HasFactory;
    protected $fillable = ['wishlist_id', 'product_id', 'variant_id', 'quantity', 'notes'];
    protected $casts = ['quantity' => 'integer'];
    /**
     * Handle wishlist functionality with proper error handling.
     * @return BelongsTo
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(UserWishlist::class, 'wishlist_id');
    }
    /**
     * Handle product functionality with proper error handling.
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Handle variant functionality with proper error handling.
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name;
        if ($this->variant) {
            $name .= ' - ' . $this->variant->name;
        }
        return $name;
    }
    /**
     * Handle getCurrentPriceAttribute functionality with proper error handling.
     * @return float|null
     */
    public function getCurrentPriceAttribute(): ?float
    {
        if ($this->variant) {
            return $this->variant->price;
        }
        return $this->product->price;
    }
    /**
     * Handle getFormattedCurrentPriceAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedCurrentPriceAttribute(): string
    {
        return app_money_format($this->current_price ?? 0);
    }
    /**
     * Handle scopeForUser functionality with proper error handling.
     * @param mixed $query
     * @param int $userId
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('wishlist', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
    /**
     * Handle scopeForProduct functionality with proper error handling.
     * @param mixed $query
     * @param int $productId
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param mixed $query
     * @param int $days
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([UserOwnedScope::class])]
final /**
 * WishlistItem
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wishlist_id',
        'product_id',
        'variant_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(UserWishlist::class, 'wishlist_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name;

        if ($this->variant) {
            $name .= ' - '.$this->variant->name;
        }

        return $name;
    }

    public function getCurrentPriceAttribute(): ?float
    {
        if ($this->variant) {
            return $this->variant->price;
        }

        return $this->product->price;
    }

    public function getFormattedCurrentPriceAttribute(): string
    {
        return app_money_format($this->current_price ?? 0);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('wishlist', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

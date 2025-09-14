<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * UserWishlist
 * 
 * Eloquent model representing the UserWishlist entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|UserWishlist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWishlist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserWishlist query()
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class UserWishlist extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'name', 'description', 'is_public', 'is_default'];
    protected $casts = ['is_public' => 'boolean', 'is_default' => 'boolean'];
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Handle items functionality with proper error handling.
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class, 'wishlist_id');
    }
    /**
     * Handle getItemsCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }
    /**
     * Handle hasProduct functionality with proper error handling.
     * @param int $productId
     * @param int|null $variantId
     * @return bool
     */
    public function hasProduct(int $productId, ?int $variantId = null): bool
    {
        return $this->items()->where('product_id', $productId)->when($variantId, fn($query) => $query->where('variant_id', $variantId))->exists();
    }
    /**
     * Handle addProduct functionality with proper error handling.
     * @param int $productId
     * @param int|null $variantId
     * @param int $quantity
     * @param string|null $notes
     * @return WishlistItem
     */
    public function addProduct(int $productId, ?int $variantId = null, int $quantity = 1, ?string $notes = null): WishlistItem
    {
        return $this->items()->create(['product_id' => $productId, 'variant_id' => $variantId, 'quantity' => $quantity, 'notes' => $notes]);
    }
    /**
     * Handle removeProduct functionality with proper error handling.
     * @param int $productId
     * @param int|null $variantId
     * @return bool
     */
    public function removeProduct(int $productId, ?int $variantId = null): bool
    {
        return $this->items()->where('product_id', $productId)->when($variantId, fn($query) => $query->where('variant_id', $variantId))->delete() > 0;
    }
    /**
     * Handle scopePublic functionality with proper error handling.
     * @param mixed $query
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
    /**
     * Handle scopeDefault functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
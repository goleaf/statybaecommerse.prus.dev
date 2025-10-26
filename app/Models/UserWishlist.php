<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Database\Factories\UserWishlistFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
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
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $name
 * @property string|null                     $description
 * @property bool                            $is_public
 * @property bool                            $is_default
 * @property \Illuminate\Support\Carbon      $created_at
 * @property \Illuminate\Support\Carbon      $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read int $items_count
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, WishlistItem> $items
 *
 * @method static Builder|UserWishlist newModelQuery()
 * @method static Builder|UserWishlist newQuery()
 * @method static Builder|UserWishlist query()
 * @method static Builder|UserWishlist public()
 * @method static Builder|UserWishlist default()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class UserWishlist extends Model
{
    /** @use HasFactory<UserWishlistFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['user_id', 'name', 'description', 'is_public', 'is_default'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public'  => 'boolean',
            'is_default' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the wishlist.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all wishlist items.
     *
     * @return HasMany<WishlistItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class, 'wishlist_id');
    }

    /**
     * Handle getItemsCountAttribute functionality with proper error handling.
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Handle hasProduct functionality with proper error handling.
     */
    public function hasProduct(int $productId, ?int $variantId = null): bool
    {
        return $this->items()->where('product_id', $productId)->when($variantId, fn ($query) => $query->where('variant_id', $variantId))->exists();
    }

    /**
     * Add a product to the wishlist.
     */
    public function addProduct(int $productId, ?int $variantId = null, int $quantity = 1, ?string $notes = null): WishlistItem
    {
        /** @var WishlistItem $item */
        $item = $this->items()->create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity'   => $quantity,
            'notes'      => $notes,
        ]);

        return $item;
    }

    /**
     * Handle removeProduct functionality with proper error handling.
     */
    public function removeProduct(int $productId, ?int $variantId = null): bool
    {
        return $this->items()->where('product_id', $productId)->when($variantId, fn ($query) => $query->where('variant_id', $variantId))->delete() > 0;
    }

    /**
     * Scope a query to only include public wishlists.
     *
     * @param  Builder<UserWishlist> $query
     * @return Builder<UserWishlist>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include default wishlists.
     *
     * @param  Builder<UserWishlist> $query
     * @return Builder<UserWishlist>
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include private wishlists.
     *
     * @param  Builder<UserWishlist> $query
     * @return Builder<UserWishlist>
     */
    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('is_public', false);
    }

    /**
     * Scope a query for a specific user.
     *
     * @param  Builder<UserWishlist> $query
     * @return Builder<UserWishlist>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}

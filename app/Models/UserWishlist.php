<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class UserWishlist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'is_default',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class, 'wishlist_id');
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    public function hasProduct(int $productId, ?int $variantId = null): bool
    {
        return $this->items()
            ->where('product_id', $productId)
            ->when($variantId, fn($query) => $query->where('variant_id', $variantId))
            ->exists();
    }

    public function addProduct(int $productId, ?int $variantId = null, int $quantity = 1, ?string $notes = null): WishlistItem
    {
        return $this->items()->create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity,
            'notes' => $notes,
        ]);
    }

    public function removeProduct(int $productId, ?int $variantId = null): bool
    {
        return $this->items()
            ->where('product_id', $productId)
            ->when($variantId, fn($query) => $query->where('variant_id', $variantId))
            ->delete() > 0;
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}

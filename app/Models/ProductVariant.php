<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasProductPricing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class ProductVariant extends Model implements HasMedia
{
    use HasFactory, HasProductPricing, InteractsWithMedia, SoftDeletes;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'compare_price',
        'cost_price',
        'track_quantity',
        'quantity',
        'weight',
        'length',
        'width',
        'height',
        'is_enabled',
        'position',
        'barcode',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'decimal:3',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'track_quantity' => 'boolean',
            'quantity' => 'integer',
            'is_enabled' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function reservedQuantity(): int
    {
        $variantId = (int) $this->id;

        $sum = (int) DB::table('variant_inventories as vi')
            ->where('vi.variant_id', $variantId)
            ->sum('vi.reserved');

        return max($sum, 0);
    }

    public function availableQuantity(): int
    {
        $variantId = (int) $this->id;

        $sum = (int) DB::table('variant_inventories as vi')
            ->where('vi.variant_id', $variantId)
            ->sum(DB::raw('CASE WHEN (vi.stock - vi.reserved) > 0 THEN (vi.stock - vi.reserved) ELSE 0 END'));

        return max($sum, 0);
    }

    public function isOutOfStock(): bool
    {
        return $this->availableQuantity() < 1;
    }

    public function getStockAttribute(): int
    {
        return (int) ($this->quantity ?? 0);
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variant_attributes',
            'variant_id',
            'attribute_value_id'
        );
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(VariantInventory::class, 'variant_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->product->name.' - '.$this->sku;
    }

    public function getProfitMarginAttribute(): ?float
    {
        if (! $this->cost_price || $this->cost_price <= 0) {
            return null;
        }

        return (($this->price - $this->cost_price) / $this->price) * 100;
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10);

        $this
            ->addMediaConversion('small')
            ->width(300)
            ->height(300)
            ->sharpen(10);
    }
}

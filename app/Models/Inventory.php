<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory model orchestrates stock availability for simple products while
 * staying backwards compatible with the legacy column names until the schema
 * migration lands.
 */
#[ScopedBy([ActiveScope::class])]
final class Inventory extends Model
{
    use HasFactory;
    use OrdersByName;

    /**
     * Explicitly pin the table so historic factory expectations keep working.
     */
    protected $table = 'inventories';

    /**
     * Hint to the OrdersByName concern that sku should be preferred when
     * available, falling back to the legacy behaviour otherwise.
     */
    protected string $nameColumn = 'sku';

    /**
     * Tighten the mass-assignment surface to the modern attribute names while
     * legacy setters re-map incoming data seamlessly.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'sku',
        'qty',
        'meta',
    ];

    /**
     * Surface the computed product name attribute for presentation layers.
     *
     * @var array<int, string>
     */
    protected $appends = ['product_name'];

    /**
     * Keep deterministic defaults so arithmetic helpers never receive nulls.
     *
     * @var array<string, int|bool>
     */
    protected $attributes = [
        'quantity'   => 0,
        'reserved'   => 0,
        'incoming'   => 0,
        'threshold'  => 0,
        'is_tracked' => true,
    ];

    /**
     * Document the modern casts even though legacy columns remain the source of
     * truth until the migration executes.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'qty'  => 'int',
        'meta' => 'array',
    ];

    /**
     * Cache schema lookups so repeated attribute writes remain inexpensive.
     *
     * @var array<string, bool>
     */
    private static array $columnPresence = [];

    /**
     * Hold the most recently normalised metadata payload for quick reuse.
     *
     * @var array{reserved:int,incoming:int,threshold:int,is_tracked:bool}|null
     */
    private ?array $metaCache = null;

    /**
     * Determine whether the inventories table currently exposes the provided
     * column, caching the response for the lifetime of the request.
     */
    private static function hasColumn(string $column): bool
    {
        if (! array_key_exists($column, self::$columnPresence)) {
            $instance = new self;
            self::$columnPresence[$column] = Schema::hasColumn($instance->getTable(), $column);
        }

        return self::$columnPresence[$column];
    }

    /**
     * Provide default metadata keys so helper methods can rely on structure.
     *
     * @return array{reserved:int,incoming:int,threshold:int,is_tracked:bool}
     */
    private function metaDefaults(): array
    {
        return [
            'reserved'   => 0,
            'incoming'   => 0,
            'threshold'  => 0,
            'is_tracked' => true,
        ];
    }

    /**
     * Override the trait helper to gracefully fall back when sku is absent.
     */
    protected function getNameColumn(): string
    {
        return self::hasColumn('sku') ? 'sku' : 'name';
    }

    /**
     * Associate the inventory entry with its owning product record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Optionally tie an inventory entry to a specific variant when available.
     */
    public function variant(): BelongsTo
    {
        $foreignKey = self::hasColumn('product_variant_id') ? 'product_variant_id' : 'variant_id';

        return $this->belongsTo(ProductVariant::class, $foreignKey);
    }

    /**
     * Reference the physical warehouse/location backing the inventory row.
     */
    public function warehouse(): BelongsTo
    {
        $foreignKey = self::hasColumn('warehouse_id') ? 'warehouse_id' : 'location_id';

        return $this->belongsTo(Location::class, $foreignKey);
    }

    /**
     * Retain the historic location relation for backwards compatibility.
     */
    public function location(): BelongsTo
    {
        return $this->warehouse();
    }

    /**
     * Expose stock movements when the application records adjustments per row.
     */
    public function movements(): HasMany
    {
        $foreignKey = self::hasColumn('inventory_id') ? 'inventory_id' : 'variant_inventory_id';

        return $this->hasMany(StockMovement::class, $foreignKey);
    }

    /**
     * Filter the query down to tracked inventory entries only.
     *
     * @param Builder<Inventory> $query
     */
    public function scopeTracked(Builder $query): Builder
    {
        if (self::hasColumn('is_tracked')) {
            return $query->where('is_tracked', true);
        }

        return $query->where('meta->is_tracked', true);
    }

    /**
     * Constrain the query to rows where available stock is below the threshold.
     *
     * @param Builder<Inventory> $query
     */
    public function scopeLowStock(Builder $query): Builder
    {
        $quantityColumn = self::hasColumn('qty') ? 'qty' : 'quantity';

        if (self::hasColumn('reserved') && self::hasColumn('threshold')) {
            return $query
                ->whereColumn($quantityColumn, '>', 'reserved')
                ->whereRaw(sprintf('(%s - reserved) <= threshold', $quantityColumn));
        }

        $reservedExpression = self::hasColumn('meta') ? "json_extract(meta, '$.reserved')" : '0';
        $thresholdExpression = self::hasColumn('meta') ? "json_extract(meta, '$.threshold')" : '0';

        return $query->whereRaw(sprintf(
            '(%1$s - COALESCE(%2$s, 0)) <= COALESCE(%3$s, 0)',
            $quantityColumn,
            $reservedExpression,
            $thresholdExpression
        ));
    }

    /**
     * Remap legacy attribute keys before the parent mass-assignment logic runs.
     *
     * @param array<string, mixed> $attributes
     */
    public function fill(array $attributes): static
    {
        if (array_key_exists('location_id', $attributes) && ! array_key_exists('warehouse_id', $attributes)) {
            $attributes['warehouse_id'] = $attributes['location_id'];
            unset($attributes['location_id']);
        }

        if (array_key_exists('quantity', $attributes) && ! array_key_exists('qty', $attributes)) {
            $attributes['qty'] = $attributes['quantity'];
            unset($attributes['quantity']);
        }

        $meta = $attributes['meta'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        foreach (['reserved', 'incoming', 'threshold', 'is_tracked'] as $key) {
            if (array_key_exists($key, $attributes)) {
                $meta[$key] = $attributes[$key];
                unset($attributes[$key]);
            }
        }

        if (! empty($meta)) {
            $attributes['meta'] = $meta;
        }

        return parent::fill($attributes);
    }

    /**
     * Persist the warehouse identifier while mirroring legacy location storage.
     */
    public function setWarehouseIdAttribute(int|string|null $value): void
    {
        $warehouse = $value !== null ? (int) $value : null;

        if (self::hasColumn('warehouse_id')) {
            $this->attributes['warehouse_id'] = $warehouse;
        }

        if (self::hasColumn('location_id')) {
            $this->attributes['location_id'] = $warehouse;
        }
    }

    /**
     * Read the warehouse identifier irrespective of the actual column present.
     */
    public function getWarehouseIdAttribute(): ?int
    {
        if (self::hasColumn('warehouse_id') && array_key_exists('warehouse_id', $this->attributes)) {
            return $this->attributes['warehouse_id'] !== null ? (int) $this->attributes['warehouse_id'] : null;
        }

        if (array_key_exists('location_id', $this->attributes)) {
            return $this->attributes['location_id'] !== null ? (int) $this->attributes['location_id'] : null;
        }

        return null;
    }

    /**
     * Support legacy callers writing the location attribute directly.
     */
    public function setLocationIdAttribute(int|string|null $value): void
    {
        $this->setWarehouseIdAttribute($value);
    }

    /**
     * Support legacy callers reading the location attribute directly.
     */
    public function getLocationIdAttribute(): ?int
    {
        return $this->getWarehouseIdAttribute();
    }

    /**
     * Store the SKU when the schema already exposes the dedicated column.
     */
    public function setSkuAttribute(?string $value): void
    {
        if (self::hasColumn('sku')) {
            $this->attributes['sku'] = $value;
        }
    }

    /**
     * Retrieve the SKU value when present, otherwise default to null.
     */
    public function getSkuAttribute(): ?string
    {
        if (self::hasColumn('sku') && array_key_exists('sku', $this->attributes)) {
            return $this->attributes['sku'];
        }

        return null;
    }

    /**
     * Capture the variant identifier only when the backing column exists.
     */
    public function setProductVariantIdAttribute(int|string|null $value): void
    {
        if (self::hasColumn('product_variant_id')) {
            $this->attributes['product_variant_id'] = $value !== null ? (int) $value : null;
        }
    }

    /**
     * Read the variant identifier, defaulting to null when unavailable.
     */
    public function getProductVariantIdAttribute(): ?int
    {
        if (self::hasColumn('product_variant_id') && array_key_exists('product_variant_id', $this->attributes)) {
            return $this->attributes['product_variant_id'] !== null ? (int) $this->attributes['product_variant_id'] : null;
        }

        return null;
    }

    /**
     * Store the quantity in both the legacy and modern columns.
     */
    public function setQtyAttribute(int|string|null $value): void
    {
        $qty = (int) ($value ?? 0);

        if (self::hasColumn('qty')) {
            $this->attributes['qty'] = $qty;
        }

        if (self::hasColumn('quantity')) {
            $this->attributes['quantity'] = $qty;
        }
    }

    /**
     * Retrieve the quantity consistently regardless of column naming.
     */
    public function getQtyAttribute(): int
    {
        if (self::hasColumn('qty') && array_key_exists('qty', $this->attributes)) {
            return (int) $this->attributes['qty'];
        }

        return (int) ($this->attributes['quantity'] ?? 0);
    }

    /**
     * Legacy quantity getter delegating to the new qty attribute.
     */
    public function getQuantityAttribute(): int
    {
        return $this->getQtyAttribute();
    }

    /**
     * Legacy quantity setter delegating to the new qty attribute.
     */
    public function setQuantityAttribute(int|string|null $value): void
    {
        $this->setQtyAttribute($value);
    }

    /**
     * Resolve the metadata payload while caching the normalised array locally.
     *
     * @return array{reserved:int,incoming:int,threshold:int,is_tracked:bool}
     */
    public function getMetaAttribute(): array
    {
        if ($this->metaCache !== null) {
            return $this->metaCache;
        }

        if (self::hasColumn('meta') && array_key_exists('meta', $this->attributes)) {
            $raw = $this->attributes['meta'];

            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $meta = is_array($decoded) ? $decoded : [];
            } elseif (is_array($raw)) {
                $meta = $raw;
            } else {
                $meta = [];
            }

            $meta = array_merge($this->metaDefaults(), $meta);
        } else {
            $meta = [
                'reserved'   => (int) ($this->attributes['reserved'] ?? 0),
                'incoming'   => (int) ($this->attributes['incoming'] ?? 0),
                'threshold'  => (int) ($this->attributes['threshold'] ?? 0),
                'is_tracked' => (bool) ($this->attributes['is_tracked'] ?? true),
            ];
        }

        return $this->metaCache = [
            'reserved'   => (int) ($meta['reserved'] ?? 0),
            'incoming'   => (int) ($meta['incoming'] ?? 0),
            'threshold'  => (int) ($meta['threshold'] ?? 0),
            'is_tracked' => (bool) ($meta['is_tracked'] ?? true),
        ];
    }

    /**
     * Accept metadata writes from mass-assignment and helper setters alike.
     *
     * @param array<string, mixed>|string|null $value
     */
    public function setMetaAttribute(mixed $value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($value)) {
            $value = [];
        }

        $this->storeMeta($value);
    }

    /**
     * Legacy reserved getter delegating to the metadata payload.
     */
    public function getReservedAttribute(): int
    {
        return $this->getMetaAttribute()['reserved'];
    }

    /**
     * Legacy reserved setter updating the metadata payload in tandem.
     */
    public function setReservedAttribute(int|string|null $value): void
    {
        $meta = $this->getMetaAttribute();
        $meta['reserved'] = (int) ($value ?? 0);
        $this->storeMeta($meta);
    }

    /**
     * Legacy incoming getter delegating to the metadata payload.
     */
    public function getIncomingAttribute(): int
    {
        return $this->getMetaAttribute()['incoming'];
    }

    /**
     * Legacy incoming setter updating the metadata payload in tandem.
     */
    public function setIncomingAttribute(int|string|null $value): void
    {
        $meta = $this->getMetaAttribute();
        $meta['incoming'] = (int) ($value ?? 0);
        $this->storeMeta($meta);
    }

    /**
     * Legacy threshold getter delegating to the metadata payload.
     */
    public function getThresholdAttribute(): int
    {
        return $this->getMetaAttribute()['threshold'];
    }

    /**
     * Legacy threshold setter updating the metadata payload in tandem.
     */
    public function setThresholdAttribute(int|string|null $value): void
    {
        $meta = $this->getMetaAttribute();
        $meta['threshold'] = (int) ($value ?? 0);
        $this->storeMeta($meta);
    }

    /**
     * Legacy is_tracked getter delegating to the metadata payload.
     */
    public function getIsTrackedAttribute(): bool
    {
        return $this->getMetaAttribute()['is_tracked'];
    }

    /**
     * Legacy is_tracked setter updating the metadata payload in tandem.
     */
    public function setIsTrackedAttribute(bool|int|string|null $value): void
    {
        $meta = $this->getMetaAttribute();
        $meta['is_tracked'] = (bool) $value;
        $this->storeMeta($meta);
    }

    /**
     * Calculate the amount of stock that is currently sellable.
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->qty - $this->reserved);
    }

    /**
     * Provide the cached product name or a safe empty string fallback.
     */
    public function getProductNameAttribute(): string
    {
        return $this->product?->name ?? '';
    }

    /**
     * Determine whether the inventory entry should be considered low stock.
     */
    public function isLowStock(): bool
    {
        $meta = $this->getMetaAttribute();

        if ($meta['threshold'] <= 0) {
            return false;
        }

        $available = $this->available_quantity;

        return $available > 0 && $available <= $meta['threshold'];
    }

    /**
     * Determine whether the inventory entry is completely out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->available_quantity <= 0;
    }

    /**
     * Centralise metadata persistence across legacy columns and future JSON
     * storage to keep the interface consistent.
     *
     * @param array<string, mixed> $meta
     */
    private function storeMeta(array $meta): void
    {
        $normalised = [
            'reserved'   => (int) ($meta['reserved'] ?? 0),
            'incoming'   => (int) ($meta['incoming'] ?? 0),
            'threshold'  => (int) ($meta['threshold'] ?? 0),
            'is_tracked' => (bool) ($meta['is_tracked'] ?? true),
        ];

        $this->metaCache = $normalised;

        if (self::hasColumn('meta')) {
            $encoded = json_encode($normalised, JSON_UNESCAPED_UNICODE);
            $this->attributes['meta'] = $encoded === false ? '{}' : $encoded;
        }

        if (self::hasColumn('reserved')) {
            $this->attributes['reserved'] = $normalised['reserved'];
        }

        if (self::hasColumn('incoming')) {
            $this->attributes['incoming'] = $normalised['incoming'];
        }

        if (self::hasColumn('threshold')) {
            $this->attributes['threshold'] = $normalised['threshold'];
        }

        if (self::hasColumn('is_tracked')) {
            $this->attributes['is_tracked'] = $normalised['is_tracked'] ? 1 : 0;
        }
    }
}

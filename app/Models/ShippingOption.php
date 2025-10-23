<?php

declare(strict_types=1);

namespace App\Models;

// use App\Models\Scopes\ActiveScope;
// use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ShippingOption
 *
 * Eloquent model representing the ShippingOption entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 */
// #[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class ShippingOption extends Model
{
    /**
     * @use HasFactory<\Database\Factories\ShippingOptionFactory>
     */
    /** @use HasFactory<\Database\Factories\ShippingOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'carrier_name',
        'service_type',
        'price',
        'currency_code',
        'zone_id',
        'is_enabled',
        'is_default',
        'sort_order',
        'min_weight',
        'max_weight',
        'min_order_amount',
        'max_order_amount',
        'estimated_days_min',
        'estimated_days_max',
        'metadata',
        'shipping_matrix',
        'zone_id',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled'         => 'boolean',
            'is_default'         => 'boolean',
            'price'              => 'decimal:2',
            'min_weight'         => 'integer',
            'max_weight'         => 'integer',
            'min_order_amount'   => 'decimal:2',
            'max_order_amount'   => 'decimal:2',
            'estimated_days_min' => 'integer',
            'estimated_days_max' => 'integer',
            'sort_order'         => 'integer',
            'metadata'           => 'array',
            'shipping_matrix'    => 'array',
        ];
    }

    protected $appends = ['formatted_price', 'estimated_delivery_text'];

    /**
     * Handle orders functionality with proper error handling.
     *
     * @return HasMany<Order, static>
     *
     * @phpstan-return HasMany<Order, ShippingOption>
     */
    public function orders(): HasMany
    {
        /** @var HasMany<Order, ShippingOption> $relation */
        $relation = $this->hasMany(Order::class, 'shipping_option_id');

        return $relation;
    }

    /**
     * Provide direct access to the owning zone relationship.
     *
     * @return BelongsTo<Zone, static>
     *
     * @phpstan-return BelongsTo<Zone, ShippingOption>
     */
    public function zone(): BelongsTo
    {
        // Expose the relation used across factories, seeds, and API resources.
        /** @var BelongsTo<Zone, ShippingOption> $relation */
        $relation = $this->belongsTo(Zone::class);

        return $relation;
    }

    /**
     * Handle zone functionality with proper error handling.
     *
     * @phpstan-return BelongsTo<Zone, ShippingOption>
     */
    public function zone(): BelongsTo
    {
        // Link each shipping option to the geographical zone it belongs to for filtering and reporting.
        return $this->belongsTo(Zone::class);
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     * @param  Builder<ShippingOption> $query
     * @return Builder<ShippingOption>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeDefault functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     * @param  Builder<ShippingOption> $query
     * @return Builder<ShippingOption>
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Handle scopeByCarrier functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     * @param  Builder<ShippingOption> $query
     * @return Builder<ShippingOption>
     */
    public function scopeByCarrier(Builder $query, string $carrier): Builder
    {
        return $query->where('carrier_name', $carrier);
    }

    public function scopeByZone(Builder $query, int|string $zoneId): Builder
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Scope shipping options by zone while tolerating null filters.
     */
    public function scopeByZone(Builder $query, null|int|string $zoneId): Builder
    {
        if ($zoneId === null || $zoneId === '') {
            return $query;
        }

        return $query->where('zone_id', $zoneId);
    }

    /**
     * Handle scopeByZone functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByZone(Builder $query, int $zoneId): Builder
    {
        // Allow filtering by the owning zone so queries remain expressive.
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     * @param  Builder<ShippingOption> $query
     * @return Builder<ShippingOption>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('price')->orderBy('name');
    }

    /**
     * Handle getFormattedPriceAttribute functionality with proper error handling.
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 2) . ' ' . $this->currency_code;
    }

    /**
     * Handle getEstimatedDeliveryTextAttribute functionality with proper error handling.
     */
    public function getEstimatedDeliveryTextAttribute(): string
    {
        // Mirror the admin listing logic by treating nulls as missing while preserving zero-day scenarios.
        $minimum = is_numeric($this->estimated_days_min) ? (int) $this->estimated_days_min : null;
        $maximum = is_numeric($this->estimated_days_max) ? (int) $this->estimated_days_max : null;

        if ($minimum !== null && $maximum !== null) {
            if ($minimum === $maximum) {
                return $minimum . ' ' . __('days');
            }

            return $minimum . '-' . $maximum . ' ' . __('days');
        }

        return __('Standard delivery');
    }

    /**
     * Handle isEligibleForWeight functionality with proper error handling.
     */
    public function isEligibleForWeight(float $weight): bool
    {
        if ($this->min_weight !== null && $weight < (float) $this->min_weight) {
            return false;
        }
        if ($this->max_weight !== null && $weight > (float) $this->max_weight) {
            return false;
        }

        return true;
    }

    /**
     * Handle isEligibleForOrderAmount functionality with proper error handling.
     */
    public function isEligibleForOrderAmount(float $amount): bool
    {
        if ($this->min_order_amount !== null && $amount < (float) $this->min_order_amount) {
            return false;
        }
        if ($this->max_order_amount !== null && $amount > (float) $this->max_order_amount) {
            return false;
        }

        return true;
    }

    /**
     * Handle calculatePriceForOrder functionality with proper error handling.
     */
    public function calculatePriceForOrder(float $weight = 0, float $orderAmount = 0): float
    {
        if (! $this->isEligibleForWeight($weight) || ! $this->isEligibleForOrderAmount($orderAmount)) {
            return 0.0;
        }

        return (float) $this->price;
    }
}

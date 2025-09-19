<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
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
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class ShippingOption extends Model
{
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
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'price' => 'decimal:2',
            'min_weight' => 'integer',
            'max_weight' => 'integer',
            'min_order_amount' => 'decimal:2',
            'max_order_amount' => 'decimal:2',
            'estimated_days_min' => 'integer',
            'estimated_days_max' => 'integer',
            'sort_order' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected $appends = ['formatted_price', 'estimated_delivery_text'];

    /**
     * Handle zone functionality with proper error handling.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Handle orders functionality with proper error handling.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_option_id');
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeDefault functionality with proper error handling.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Handle scopeByZone functionality with proper error handling.
     */
    public function scopeByZone(Builder $query, int $zoneId): Builder
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Handle scopeByCarrier functionality with proper error handling.
     */
    public function scopeByCarrier(Builder $query, string $carrier): Builder
    {
        return $query->where('carrier_name', $carrier);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
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
        if ($this->estimated_days_min && $this->estimated_days_max) {
            if ($this->estimated_days_min === $this->estimated_days_max) {
                return $this->estimated_days_min . ' ' . __('days');
            }
            return $this->estimated_days_min . '-' . $this->estimated_days_max . ' ' . __('days');
        }
        return __('Standard delivery');
    }

    /**
     * Handle isEligibleForWeight functionality with proper error handling.
     */
    public function isEligibleForWeight(float $weight): bool
    {
        if ($this->min_weight && $weight < $this->min_weight) {
            return false;
        }
        if ($this->max_weight && $weight > $this->max_weight) {
            return false;
        }
        return true;
    }

    /**
     * Handle isEligibleForOrderAmount functionality with proper error handling.
     */
    public function isEligibleForOrderAmount(float $amount): bool
    {
        if ($this->min_order_amount && $amount < $this->min_order_amount) {
            return false;
        }
        if ($this->max_order_amount && $amount > $this->max_order_amount) {
            return false;
        }
        return true;
    }

    /**
     * Handle calculatePriceForOrder functionality with proper error handling.
     */
    public function calculatePriceForOrder(float $weight = 0, float $orderAmount = 0): float
    {
        if (!$this->isEligibleForWeight($weight) || !$this->isEligibleForOrderAmount($orderAmount)) {
            return 0.0;
        }
        return (float) $this->price;
    }
}

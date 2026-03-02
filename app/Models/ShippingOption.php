<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ShippingOption
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $slug
 * @property string|null                     $description
 * @property string|null                     $carrier_name
 * @property string|null                     $service_type
 * @property string|null                     $price
 * @property string                          $currency_code
 * @property int|null                        $country_id
 * @property int|null                        $city_id
 * @property int|null                        $zone_id
 * @property bool                            $is_enabled
 * @property bool                            $is_default
 * @property int                             $sort_order
 * @property int|null                        $min_weight
 * @property int|null                        $max_weight
 * @property string|null                     $min_order_amount
 * @property string|null                     $max_order_amount
 * @property int|null                        $estimated_days_min
 * @property int|null                        $estimated_days_max
 * @property array|null                      $metadata
 * @property array|null                      $shipping_matrix
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
final class ShippingOption extends Model
{
    use HasFactory;

    protected $table = 'shipping_options';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'carrier_name',
        'service_type',
        'price',
        'currency_code',
        'country_id',
        'city_id',
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
    ];

    protected static function booted(): void
    {
        self::creating(static function (self $shippingOption): void {
            $shippingOption->currency_code = 'EUR';
        });

        self::updating(static function (self $shippingOption): void {
            $shippingOption->currency_code = 'EUR';
        });
    }

    protected function casts(): array
    {
        return [
            'price'              => 'decimal:2',
            'is_enabled'         => 'boolean',
            'is_default'         => 'boolean',
            'sort_order'         => 'integer',
            'min_weight'         => 'integer',
            'max_weight'         => 'integer',
            'min_order_amount'   => 'decimal:2',
            'max_order_amount'   => 'decimal:2',
            'estimated_days_min' => 'integer',
            'estimated_days_max' => 'integer',
            'metadata'           => 'array',
            'shipping_matrix'    => 'array',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Restrict the query to enabled shipping options only.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Apply storefront ordering for shipping options.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}

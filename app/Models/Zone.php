<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\ZoneTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final /**
 * Zone
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Zone extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected string $translationModel = ZoneTranslation::class;

    protected $table = 'zones';

    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'is_enabled',
        'is_default',
        'currency_id',
        'tax_rate',
        'shipping_rate',
        'sort_order',
        'metadata',
        'type',
        'priority',
        'min_order_amount',
        'max_order_amount',
        'free_shipping_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'tax_rate' => 'decimal:4',
            'shipping_rate' => 'decimal:2',
            'sort_order' => 'integer',
            'priority' => 'integer',
            'min_order_amount' => 'decimal:2',
            'max_order_amount' => 'decimal:2',
            'free_shipping_threshold' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_zone', 'zone_id', 'country_id');
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class);
    }

    // Scopes
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByCurrency(Builder $query, int $currencyId): Builder
    {
        return $query->where('currency_id', $currencyId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('priority')->orderBy('name');
    }

    public function scopeWithCountries(Builder $query): Builder
    {
        return $query->withCount('countries');
    }

    // Business Logic Methods
    public function calculateTax(float $amount): float
    {
        return $amount * ($this->tax_rate / 100);
    }

    public function calculateShipping(float $weight = 0, float $orderAmount = 0): float
    {
        // Free shipping if order amount exceeds threshold
        if ($this->free_shipping_threshold && $orderAmount >= $this->free_shipping_threshold) {
            return 0;
        }

        // Check order amount limits
        if ($this->min_order_amount && $orderAmount < $this->min_order_amount) {
            return $this->shipping_rate;
        }

        if ($this->max_order_amount && $orderAmount > $this->max_order_amount) {
            return $this->shipping_rate;
        }

        return $this->shipping_rate * max(1, $weight);
    }

    public function isEligibleForOrder(float $orderAmount): bool
    {
        if ($this->min_order_amount && $orderAmount < $this->min_order_amount) {
            return false;
        }

        if ($this->max_order_amount && $orderAmount > $this->max_order_amount) {
            return false;
        }

        return true;
    }

    public function hasFreeShipping(float $orderAmount): bool
    {
        return $this->free_shipping_threshold && $orderAmount >= $this->free_shipping_threshold;
    }

    // Translation Methods
    public function getTranslatedNameAttribute(): string
    {
        return $this->trans('name') ?: $this->getOriginal('name') ?: 'Unknown';
    }

    public function getTranslatedDescriptionAttribute(): string
    {
        return $this->trans('description') ?: $this->getOriginal('description') ?: '';
    }

    // Accessor Methods
    public function getFormattedTaxRateAttribute(): string
    {
        return number_format($this->tax_rate, 2).'%';
    }

    public function getFormattedShippingRateAttribute(): string
    {
        return '€'.number_format($this->shipping_rate, 2);
    }

    public function getFormattedMinOrderAmountAttribute(): string
    {
        return $this->min_order_amount ? '€'.number_format($this->min_order_amount, 2) : 'N/A';
    }

    public function getFormattedMaxOrderAmountAttribute(): string
    {
        return $this->max_order_amount ? '€'.number_format($this->max_order_amount, 2) : 'N/A';
    }

    public function getFormattedFreeShippingThresholdAttribute(): string
    {
        return $this->free_shipping_threshold ? '€'.number_format($this->free_shipping_threshold, 2) : 'N/A';
    }

    // Static Methods
    public static function getDefaultZone(): ?self
    {
        return self::default()->first();
    }

    public static function getActiveZones(): Builder
    {
        return self::active()->enabled()->ordered();
    }

    public static function getZonesByCurrency(int $currencyId): Builder
    {
        return self::byCurrency($currencyId)->active()->enabled()->ordered();
    }
}

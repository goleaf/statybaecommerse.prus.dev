<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\StatusScope;
use App\Models\Translations\ZoneTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Zone
 * 
 * Eloquent model representing the Zone entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property string $translationModel
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|Zone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class, StatusScope::class])]
final class Zone extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;
    protected string $translationModel = ZoneTranslation::class;
    protected $table = 'zones';
    protected $fillable = ['name', 'slug', 'code', 'description', 'is_enabled', 'is_default', 'currency_id', 'tax_rate', 'shipping_rate', 'sort_order', 'metadata', 'type', 'priority', 'min_order_amount', 'max_order_amount', 'free_shipping_threshold', 'is_active'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'is_default' => 'boolean', 'is_active' => 'boolean', 'tax_rate' => 'decimal:4', 'shipping_rate' => 'decimal:2', 'sort_order' => 'integer', 'priority' => 'integer', 'min_order_amount' => 'decimal:2', 'max_order_amount' => 'decimal:2', 'free_shipping_threshold' => 'decimal:2', 'metadata' => 'array'];
    }
    /**
     * Handle discounts functionality with proper error handling.
     * @return HasMany
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }
    /**
     * Handle currency functionality with proper error handling.
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    /**
     * Handle countries functionality with proper error handling.
     * @return BelongsToMany
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_zone', 'zone_id', 'country_id');
    }
    /**
     * Handle regions functionality with proper error handling.
     * @return HasMany
     */
    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
    /**
     * Handle cities functionality with proper error handling.
     * @return HasMany
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
    /**
     * Handle orders functionality with proper error handling.
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    /**
     * Handle shippingOptions functionality with proper error handling.
     * @return HasMany
     */
    public function shippingOptions(): HasMany
    {
        return $this->hasMany(ShippingOption::class);
    }
    /**
     * Handle priceLists functionality with proper error handling.
     * @return HasMany
     */
    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class);
    }
    // Scopes
    /**
     * Handle scopeEnabled functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
    /**
     * Handle scopeDefault functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    /**
     * Handle scopeByType functionality with proper error handling.
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
    /**
     * Handle scopeByCurrency functionality with proper error handling.
     * @param Builder $query
     * @param int $currencyId
     * @return Builder
     */
    public function scopeByCurrency(Builder $query, int $currencyId): Builder
    {
        return $query->where('currency_id', $currencyId);
    }
    /**
     * Handle scopeOrdered functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('priority')->orderBy('name');
    }
    /**
     * Handle scopeWithCountries functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithCountries(Builder $query): Builder
    {
        return $query->withCount('countries');
    }
    // Business Logic Methods
    /**
     * Handle calculateTax functionality with proper error handling.
     * @param float $amount
     * @return float
     */
    public function calculateTax(float $amount): float
    {
        return $amount * ($this->tax_rate / 100);
    }
    /**
     * Handle calculateShipping functionality with proper error handling.
     * @param float $weight
     * @param float $orderAmount
     * @return float
     */
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
    /**
     * Handle isEligibleForOrder functionality with proper error handling.
     * @param float $orderAmount
     * @return bool
     */
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
    /**
     * Handle hasFreeShipping functionality with proper error handling.
     * @param float $orderAmount
     * @return bool
     */
    public function hasFreeShipping(float $orderAmount): bool
    {
        return $this->free_shipping_threshold && $orderAmount >= $this->free_shipping_threshold;
    }
    // Translation Methods
    /**
     * Handle getTranslatedNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getTranslatedNameAttribute(): string
    {
        return ($this->trans('name') ?: $this->getOriginal('name')) ?: 'Unknown';
    }
    /**
     * Handle getTranslatedDescriptionAttribute functionality with proper error handling.
     * @return string
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        return ($this->trans('description') ?: $this->getOriginal('description')) ?: '';
    }
    // Accessor Methods
    /**
     * Handle getFormattedTaxRateAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedTaxRateAttribute(): string
    {
        return number_format($this->tax_rate, 2) . '%';
    }
    /**
     * Handle getFormattedShippingRateAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedShippingRateAttribute(): string
    {
        return '€' . number_format($this->shipping_rate, 2);
    }
    /**
     * Handle getFormattedMinOrderAmountAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedMinOrderAmountAttribute(): string
    {
        return $this->min_order_amount ? '€' . number_format($this->min_order_amount, 2) : 'N/A';
    }
    /**
     * Handle getFormattedMaxOrderAmountAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedMaxOrderAmountAttribute(): string
    {
        return $this->max_order_amount ? '€' . number_format($this->max_order_amount, 2) : 'N/A';
    }
    /**
     * Handle getFormattedFreeShippingThresholdAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedFreeShippingThresholdAttribute(): string
    {
        return $this->free_shipping_threshold ? '€' . number_format($this->free_shipping_threshold, 2) : 'N/A';
    }
    // Static Methods
    /**
     * Handle getDefaultZone functionality with proper error handling.
     * @return self|null
     */
    public static function getDefaultZone(): ?self
    {
        return self::default()->first();
    }
    /**
     * Handle getActiveZones functionality with proper error handling.
     * @return Builder
     */
    public static function getActiveZones(): Builder
    {
        return self::active()->enabled()->ordered();
    }
    /**
     * Handle getZonesByCurrency functionality with proper error handling.
     * @param int $currencyId
     * @return Builder
     */
    public static function getZonesByCurrency(int $currencyId): Builder
    {
        return self::byCurrency($currencyId)->active()->enabled()->ordered();
    }
}
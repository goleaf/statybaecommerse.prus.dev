<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
/**
 * Currency
 * 
 * Eloquent model representing the Currency entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property array $translatable
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class Currency extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;
    protected $table = 'currencies';
    protected $fillable = ['name', 'code', 'symbol', 'exchange_rate', 'is_default', 'is_enabled', 'decimal_places'];
    public array $translatable = ['name'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['exchange_rate' => 'float', 'is_default' => 'boolean', 'is_enabled' => 'boolean', 'decimal_places' => 'integer'];
    }
    // Relationships
    /**
     * Handle zones functionality with proper error handling.
     * @return HasMany
     */
    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }
    /**
     * Handle prices functionality with proper error handling.
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
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
     * Handle countries functionality with proper error handling.
     * @return BelongsToMany
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_currencies');
    }
    /**
     * Handle priceLists functionality with proper error handling.
     * @return HasMany
     */
    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class);
    }
    /**
     * Handle campaigns functionality with proper error handling.
     * @return HasMany
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
    /**
     * Handle discounts functionality with proper error handling.
     * @return HasMany
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }
    // Scopes
    /**
     * Handle scopeEnabled functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
    /**
     * Handle scopeDefault functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
    // Accessors
    /**
     * Handle getFormattedSymbolAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedSymbolAttribute(): string
    {
        return $this->symbol ?? $this->code;
    }
    /**
     * Handle getFormattedExchangeRateAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedExchangeRateAttribute(): string
    {
        return number_format($this->exchange_rate, $this->decimal_places);
    }
    // Methods
    /**
     * Handle isDefault functionality with proper error handling.
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }
    /**
     * Handle isEnabled functionality with proper error handling.
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }
    /**
     * Handle formatAmount functionality with proper error handling.
     * @param float $amount
     * @return string
     */
    public function formatAmount(float $amount): string
    {
        $formattedAmount = number_format($amount, $this->decimal_places);
        if ($this->symbol) {
            return $this->symbol . ' ' . $formattedAmount;
        }
        return $formattedAmount . ' ' . $this->code;
    }
}
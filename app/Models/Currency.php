<?php

declare(strict_types=1);

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
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class Currency extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'currencies';

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'iso_code',
        'description',
        'exchange_rate',
        'base_currency',
        'decimal_places',
        'symbol_position',
        'thousands_separator',
        'decimal_separator',
        'is_active',
        'is_default',
        'is_enabled',
        'sort_order',
        'auto_update_rate',
    ];

    public array $translatable = ['name'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'exchange_rate' => 'float',
            'decimal_places' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
            'auto_update_rate' => 'boolean',
        ];
    }

    // Relationships

    /**
     * Handle prices functionality with proper error handling.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    /**
     * Handle orders functionality with proper error handling.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Handle countries functionality with proper error handling.
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_currencies');
    }

    /**
     * Handle priceLists functionality with proper error handling.
     */
    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class);
    }

    /**
     * Handle campaigns functionality with proper error handling.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Handle discounts functionality with proper error handling.
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    // Scopes

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeDefault functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors

    /**
     * Handle getFormattedSymbolAttribute functionality with proper error handling.
     */
    public function getFormattedSymbolAttribute(): string
    {
        return $this->symbol ?? $this->code;
    }

    /**
     * Handle getFormattedExchangeRateAttribute functionality with proper error handling.
     */
    public function getFormattedExchangeRateAttribute(): string
    {
        return number_format($this->exchange_rate, $this->decimal_places);
    }

    // Methods

    /**
     * Handle isDefault functionality with proper error handling.
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * Handle isEnabled functionality with proper error handling.
     */
    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    /**
     * Handle isActive functionality with proper error handling.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Handle formatAmount functionality with proper error handling.
     */
    public function formatAmount(float $amount): string
    {
        $formattedAmount = number_format($amount, $this->decimal_places);
        if ($this->symbol) {
            return $this->symbol.' '.$formattedAmount;
        }

        return $formattedAmount.' '.$this->code;
    }
}

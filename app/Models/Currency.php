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

#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final /**
 * Currency
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Currency extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'currencies';

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_enabled',
        'decimal_places',
    ];

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'float',
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
            'decimal_places' => 'integer',
        ];
    }

    // Relationships
    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_currencies');
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    // Scopes
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Accessors
    public function getFormattedSymbolAttribute(): string
    {
        return $this->symbol ?? $this->code;
    }

    public function getFormattedExchangeRateAttribute(): string
    {
        return number_format($this->exchange_rate, $this->decimal_places);
    }

    // Methods
    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    public function formatAmount(float $amount): string
    {
        $formattedAmount = number_format($amount, $this->decimal_places);

        if ($this->symbol) {
            return $this->symbol.' '.$formattedAmount;
        }

        return $formattedAmount.' '.$this->code;
    }
}

<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

final class Zone extends Model
{
    use HasFactory;

    protected $table = 'zones';

    protected $fillable = [
        'name',
        'slug',
        'code',
        'is_enabled',
        'metadata',
        'currency_id',
        'tax_rate',
        'shipping_rate',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'tax_rate' => 'decimal:4',
            'shipping_rate' => 'decimal:4',
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function calculateTax(float $amount): float
    {
        return $amount * ($this->tax_rate / 100);
    }

    public function calculateShipping(float $weight = 0): float
    {
        return $this->shipping_rate * max(1, $weight);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Zone extends Model
{
    /**
     * @use HasFactory<\Database\Factories\ZoneFactory>
     */
    /** @use HasFactory<\Database\Factories\ZoneFactory> */
    use HasFactory;

    use OrdersByName; // Allow zones to be listed alphabetically within admin filters.

    protected $table = 'zones';

    protected $fillable = [
        'name',
        'code',
        'is_enabled',
    ];

    /**
     * Provide the relationship between zones and their shipping options.
     *
     * @return HasMany<ShippingOption, static>
     *                                         Handle shippingOptions functionality with proper error handling.
     *
     * @phpstan-return HasMany<ShippingOption, Zone>
     */
    public function shippingOptions(): HasMany
    {
        // Enable convenient retrieval of shipping options tied to the current zone.
        /** @var HasMany<ShippingOption, Zone> $relation */
        $relation = $this->hasMany(ShippingOption::class);

        return $relation;

        // Provide convenient access to all shipping options configured for this zone.
        return $this->hasMany(ShippingOption::class);
    }

    /**
     * Column used by the OrdersByName scope.
     */
    protected string $nameColumn = 'name';
}

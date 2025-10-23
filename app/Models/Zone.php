<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Zone extends Model
{
    /** @use HasFactory<\Database\Factories\ZoneFactory> */
    use HasFactory;

    protected $table = 'zones';

    protected $fillable = [
        'name',
        'code',
        'is_enabled',
    ];

    /**
     * Handle shippingOptions functionality with proper error handling.
     *
     * @phpstan-return HasMany<ShippingOption, Zone>
     */
    public function shippingOptions(): HasMany
    {
        // Provide convenient access to all shipping options configured for this zone.
        return $this->hasMany(ShippingOption::class);
    }
}

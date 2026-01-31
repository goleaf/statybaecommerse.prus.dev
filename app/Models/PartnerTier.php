<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\PartnerTier
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $code
 * @property int                             $priority
 * @property string                          $default_discount_pct
 * @property bool                            $is_enabled
 * @property float|null                      $discount_rate
 * @property float|null                      $commission_rate
 * @property array|null                      $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class PartnerTier extends Model
{
    use HasFactory;

    protected $table = 'partner_tiers';

    protected $fillable = [
        'name',
        'code',
        'priority',
        'default_discount_pct',
        'is_enabled',
        'discount_rate',
        'commission_rate',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'priority'             => 'integer',
            'default_discount_pct' => 'decimal:2',
            'is_enabled'           => 'boolean',
            'discount_rate'        => 'float',
            'commission_rate'      => 'float',
            'metadata'             => 'array',
        ];
    }

    /**
     * @return HasMany<Partner, $this>
     */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'tier_id');
    }
}

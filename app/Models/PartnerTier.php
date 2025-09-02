<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class PartnerTier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sh_partner_tiers';

    protected $fillable = [
        'name',
        'code',
        'discount_rate',
        'commission_rate',
        'minimum_order_value',
        'is_enabled',
        'benefits',
    ];

    protected function casts(): array
    {
        return [
            'discount_rate' => 'decimal:4',
            'commission_rate' => 'decimal:4',
            'minimum_order_value' => 'decimal:2',
            'is_enabled' => 'boolean',
            'benefits' => 'array',
        ];
    }

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'tier_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeByDiscountRate($query, float $rate)
    {
        return $query->where('discount_rate', $rate);
    }
}

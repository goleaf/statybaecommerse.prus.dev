<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * PartnerTier
 * 
 * Eloquent model representing the PartnerTier entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerTier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerTier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PartnerTier query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class PartnerTier extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'partner_tiers';
    protected $fillable = ['name', 'code', 'discount_rate', 'commission_rate', 'minimum_order_value', 'is_enabled', 'benefits'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['discount_rate' => 'decimal:4', 'commission_rate' => 'decimal:4', 'minimum_order_value' => 'decimal:2', 'is_enabled' => 'boolean', 'benefits' => 'array'];
    }
    /**
     * Handle partners functionality with proper error handling.
     * @return HasMany
     */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'tier_id');
    }
    /**
     * Handle scopeEnabled functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
    /**
     * Handle scopeByDiscountRate functionality with proper error handling.
     * @param mixed $query
     * @param float $rate
     */
    public function scopeByDiscountRate($query, float $rate)
    {
        return $query->where('discount_rate', $rate);
    }
}
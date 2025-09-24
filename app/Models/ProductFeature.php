<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductFeature
 *
 * Eloquent model representing the ProductFeature entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFeature query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class ProductFeature extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'feature_type', 'feature_key', 'feature_value', 'weight'];

    protected $casts = ['feature_value' => 'decimal:6', 'weight' => 'decimal:4'];

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('feature_type', $type);
    }

    /**
     * Handle scopeByFeature functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByFeature($query, string $featureKey)
    {
        return $query->where('feature_key', $featureKey);
    }

    /**
     * Handle scopeWithMinValue functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithMinValue($query, float $minValue)
    {
        return $query->where('feature_value', '>=', $minValue);
    }

    /**
     * Handle scopeOrderedByValue functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrderedByValue($query)
    {
        return $query->orderByDesc('feature_value');
    }
}

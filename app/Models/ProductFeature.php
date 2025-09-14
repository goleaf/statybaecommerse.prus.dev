<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([ActiveScope::class])]
final /**
 * ProductFeature
 * 
 * Stores product feature vectors for content-based recommendations.
 */
class ProductFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'feature_type',
        'feature_key',
        'feature_value',
        'weight',
    ];

    protected $casts = [
        'feature_value' => 'decimal:6',
        'weight' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('feature_type', $type);
    }

    public function scopeByFeature($query, string $featureKey)
    {
        return $query->where('feature_key', $featureKey);
    }

    public function scopeWithMinValue($query, float $minValue)
    {
        return $query->where('feature_value', '>=', $minValue);
    }

    public function scopeOrderedByValue($query)
    {
        return $query->orderByDesc('feature_value');
    }
}

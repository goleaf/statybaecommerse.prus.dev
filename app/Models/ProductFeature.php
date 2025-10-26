<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
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
    use OrdersByName;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['product_id', 'feature_type', 'feature_key', 'feature_value', 'weight', 'is_active'];

    /**
     * Anchor alphabetical ordering to the feature_key so admin listings stay predictable.
     */
    protected string $nameColumn = 'feature_key';

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'feature_value' => 'string',
        'weight'        => 'decimal:4',
        'is_active'     => 'boolean',
    ];

    /**
     * Provide the relationship definition for the owning product so feature queries
     * can eagerly load the parent record when needed.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope features by their declared type (e.g. specification, benefit).
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('feature_type', $type);
    }

    /**
     * Scope features by their semantic key, such as "color" or "weight".
     */
    public function scopeByFeature(Builder $query, string $featureKey): Builder
    {
        return $query->where('feature_key', $featureKey);
    }

    /**
     * Restrict the query to features whose numeric value exceeds a threshold.
     */
    public function scopeWithMinValue(Builder $query, float $minValue): Builder
    {
        return $query->where('feature_value', '>=', $minValue);
    }

    /**
     * Order features by their stored value in descending order to surface the most
     * relevant or heavily weighted entries first.
     */
    public function scopeOrderedByValue(Builder $query): Builder
    {
        return $query->orderByDesc('feature_value');
    }
}

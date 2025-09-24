<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductSimilarity
 *
 * Eloquent model representing the ProductSimilarity entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSimilarity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSimilarity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSimilarity query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class ProductSimilarity extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'similar_product_id', 'algorithm_type', 'similarity_score', 'calculation_data', 'calculated_at'];

    protected $casts = ['similarity_score' => 'decimal:6', 'calculation_data' => 'array', 'calculated_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if ($model->calculated_at === null) {
                $model->calculated_at = now();
            }
        });
    }

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Handle similarProduct functionality with proper error handling.
     */
    public function similarProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'similar_product_id');
    }

    /**
     * Handle scopeByAlgorithm functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByAlgorithm($query, string $algorithmType)
    {
        return $query->where('algorithm_type', $algorithmType);
    }

    /**
     * Handle scopeWithMinScore functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithMinScore($query, float $minScore)
    {
        return $query->where('similarity_score', '>=', $minScore);
    }

    /**
     * Handle scopeOrderedBySimilarity functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrderedBySimilarity($query)
    {
        return $query->orderByDesc('similarity_score');
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('calculated_at', '>=', now()->subDays($days));
    }
}

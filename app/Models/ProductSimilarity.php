<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * ProductSimilarity
 * 
 * Stores calculated similarity scores between products for recommendation algorithms.
 */
class ProductSimilarity extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'similar_product_id',
        'algorithm_type',
        'similarity_score',
        'calculation_data',
        'calculated_at',
    ];

    protected $casts = [
        'similarity_score' => 'decimal:6',
        'calculation_data' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function similarProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'similar_product_id');
    }

    public function scopeByAlgorithm($query, string $algorithmType)
    {
        return $query->where('algorithm_type', $algorithmType);
    }

    public function scopeWithMinScore($query, float $minScore)
    {
        return $query->where('similarity_score', '>=', $minScore);
    }

    public function scopeOrderedBySimilarity($query)
    {
        return $query->orderByDesc('similarity_score');
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('calculated_at', '>=', now()->subDays($days));
    }
}

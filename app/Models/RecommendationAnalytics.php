<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * RecommendationAnalytics
 * 
 * Tracks performance metrics for recommendation system.
 */
class RecommendationAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'config_id',
        'user_id',
        'product_id',
        'action',
        'ctr',
        'conversion_rate',
        'metrics',
        'date',
    ];

    protected $casts = [
        'ctr' => 'decimal:4',
        'conversion_rate' => 'decimal:4',
        'metrics' => 'array',
        'date' => 'date',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(RecommendationBlock::class, 'block_id');
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(RecommendationConfig::class, 'config_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeByDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByBlock($query, int $blockId)
    {
        return $query->where('block_id', $blockId);
    }

    public function scopeByConfig($query, int $configId)
    {
        return $query->where('config_id', $configId);
    }
}

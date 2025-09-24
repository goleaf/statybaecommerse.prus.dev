<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RecommendationAnalytics
 *
 * Eloquent model representing the RecommendationAnalytics entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationAnalytics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationAnalytics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationAnalytics query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class RecommendationAnalytics extends Model
{
    use HasFactory;

    protected $fillable = ['block_id', 'config_id', 'user_id', 'product_id', 'action', 'ctr', 'conversion_rate', 'metrics', 'date'];

    protected $casts = ['ctr' => 'decimal:4', 'conversion_rate' => 'decimal:4', 'metrics' => 'array', 'date' => 'date'];

    /**
     * Handle block functionality with proper error handling.
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(RecommendationBlock::class, 'block_id');
    }

    /**
     * Handle config functionality with proper error handling.
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(RecommendationConfig::class, 'config_id');
    }

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle scopeByDate functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Handle scopeByDateRange functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Handle scopeByAction functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Handle scopeByBlock functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByBlock($query, int $blockId)
    {
        return $query->where('block_id', $blockId);
    }

    /**
     * Handle scopeByConfig functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByConfig($query, int $configId)
    {
        return $query->where('config_id', $configId);
    }
}

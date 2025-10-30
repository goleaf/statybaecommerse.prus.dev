<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * Normalise the persisted date to an ISO string so SQLite comparisons stay deterministic.
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            set: static function (CarbonInterface|string|null $value): ?string {
                // Guard against empty payloads so the column can remain nullable when omitted.
                if ($value === null || $value === '') {
                    return null;
                }

                return $value instanceof CarbonInterface
                    ? $value->toDateString()
                    : CarbonImmutable::parse($value)->toDateString();
            },
        );
    }

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
     * @param mixed $query
     */
    public function scopeByDate(Builder $query, string $date): Builder
    {
        return $query->where('date', $date);
    }

    /**
     * Handle scopeByDateRange functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        $query->whereBetween('date', [$startDate, $endDate]);

        $baseQuery = $query->getQuery();
        $lastIndex = array_key_last($baseQuery->wheres);

        if ($lastIndex !== null && ($baseQuery->wheres[$lastIndex]['type'] ?? null) === 'between') {
            $baseQuery->wheres[$lastIndex]['type'] = 'Between';
        }

        return $query;
    }

    /**
     * Handle scopeByAction functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Handle scopeByBlock functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByBlock(Builder $query, int $blockId): Builder
    {
        return $query->where('block_id', $blockId);
    }

    /**
     * Handle scopeByConfig functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByConfig(Builder $query, int $configId): Builder
    {
        return $query->where('config_id', $configId);
    }

    public function getCasts(): array
    {
        $casts = parent::getCasts();

        unset($casts[$this->getKeyName()]);

        return $casts;
    }
}

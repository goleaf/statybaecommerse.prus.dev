<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\UserOwnedScope;
use App\Models\Scopes\VisibleScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserBehavior
 *
 * Eloquent model representing the UserBehavior entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $timestamps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class UserBehavior extends Model
{
    /** @use HasFactory<\Database\Factories\UserBehaviorFactory> */
    use HasFactory;

    use OrdersByName;

    /**
     * Configure the OrdersByName trait to rely on the behaviour type column for ordering analytics records.
     * Configure the OrdersByName trait to rely on the behaviour type column when ordering analytics records.
     * Keeping this explicit prevents legacy `event` references from sneaking back in.
     */
    protected string $nameColumn = 'behavior_type';

    /**
     * Allow mass assignment of the storefront tracking columns required for capturing behaviour snapshots.
     * Allow mass assignment of the comprehensive tracking columns required for the analytics feature set.
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'product_id',
        'category_id',
        'behavior_type',
        'metadata',
        'referrer',
        'user_agent',
        'ip_address',
        'created_at',
    ];

    /**
     * Ensure JSON payloads become arrays automatically and that created_at is treated as a Carbon instance.
     */
    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Disable automatic timestamps so the ingest pipeline can control created_at and avoid unwanted updates.
     */
    public $timestamps = false;

    /**
     * Relationship: link each behaviour entry back to the originating user for downstream analytics queries.
     */
    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, self> $relation */
        $relation = $this->belongsTo(User::class);

        return $relation;
    }

    /**
     * Relationship: attach behaviours to their product while bypassing storefront visibility scopes.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        /** @var BelongsTo<Product, self> $relation */
        $relation = $this->belongsTo(Product::class)
            ->withoutGlobalScopes([
                ActiveScope::class,
                PublishedScope::class,
                VisibleScope::class,
            ]);

        return $relation;
    }

    /**
     * Relationship: bind behaviour insights to the related category without visibility restrictions.
     *
     * @return BelongsTo<Category, self>
     */
    public function category(): BelongsTo
    {
        /** @var BelongsTo<Category, self> $relation */
        $relation = $this->belongsTo(Category::class)
            ->withoutGlobalScopes([
                ActiveScope::class,
                EnabledScope::class,
                VisibleScope::class,
            ]);

        return $relation;
    }

    /**
     * Scope helper: constrain the query to behaviours recorded within the provided lookback window.
     *
     * @param  Builder<UserBehavior> $query
     * @return Builder<UserBehavior>
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope helper: narrow results to a specific behaviour classification (view, click, wishlist, etc.).
     *
     * @param  Builder<UserBehavior> $query
     * @return Builder<UserBehavior>
     */
    public function scopeByType(Builder $query, string $behaviorType): Builder
    {
        return $query->where('behavior_type', $behaviorType);
    }

    /**
     * Scope helper: fetch behaviours associated with a particular user identifier.
     *
     * @param  Builder<UserBehavior> $query
     * @return Builder<UserBehavior>
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope helper: collate behaviours captured under the same session fingerprint for journey analysis.
     *
     * @param  Builder<UserBehavior> $query
     * @return Builder<UserBehavior>
     */
    public function scopeBySession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
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
    use HasFactory;
    use OrdersByName;

    /**
     * Order analytics records by the behavior_type descriptor through the
     * shared OrdersByName scope.
     */
    protected string $nameColumn = 'behavior_type';

    protected $fillable = ['user_id', 'session_id', 'product_id', 'category_id', 'behavior_type', 'metadata', 'referrer', 'user_agent', 'ip_address', 'created_at'];

    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];

    public $timestamps = false;

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
        // Avoid filtering analytics associations by storefront visibility rules so tests and
        // reporting tools can always resolve the related product instance.
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    /**
     * Handle category functionality with proper error handling.
     */
    public function category(): BelongsTo
    {
        // Categories can be archived or hidden, yet their behavioral data remains valuable,
        // so bypass global scopes when hydrating this relationship.
        return $this->belongsTo(Category::class)->withoutGlobalScopes();
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('behavior_type', $type);
    }

    /**
     * Handle scopeByUser functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Handle scopeBySession functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}

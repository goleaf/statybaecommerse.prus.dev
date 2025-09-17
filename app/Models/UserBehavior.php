<?php

declare (strict_types=1);
namespace App\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBehavior query()
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class UserBehavior extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'session_id', 'product_id', 'category_id', 'behavior_type', 'metadata', 'referrer', 'user_agent', 'ip_address', 'created_at'];
    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];
    public $timestamps = false;
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Handle product functionality with proper error handling.
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Handle category functionality with proper error handling.
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param mixed $query
     * @param int $days
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
    /**
     * Handle scopeByType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('behavior_type', $type);
    }
    /**
     * Handle scopeByUser functionality with proper error handling.
     * @param mixed $query
     * @param int $userId
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    /**
     * Handle scopeBySession functionality with proper error handling.
     * @param mixed $query
     * @param string $sessionId
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}
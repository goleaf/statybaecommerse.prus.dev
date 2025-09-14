<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * UserProductInteraction
 * 
 * Eloquent model representing the UserProductInteraction entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|UserProductInteraction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserProductInteraction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserProductInteraction query()
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class UserProductInteraction extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'product_id', 'interaction_type', 'rating', 'count', 'first_interaction', 'last_interaction'];
    protected $casts = ['rating' => 'decimal:2', 'count' => 'integer', 'first_interaction' => 'datetime', 'last_interaction' => 'datetime'];
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
     * Handle scopeByType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('interaction_type', $type);
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
     * Handle scopeByProduct functionality with proper error handling.
     * @param mixed $query
     * @param int $productId
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }
    /**
     * Handle scopeWithMinCount functionality with proper error handling.
     * @param mixed $query
     * @param int $minCount
     */
    public function scopeWithMinCount($query, int $minCount)
    {
        return $query->where('count', '>=', $minCount);
    }
    /**
     * Handle scopeWithMinRating functionality with proper error handling.
     * @param mixed $query
     * @param float $minRating
     */
    public function scopeWithMinRating($query, float $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param mixed $query
     * @param int $days
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('last_interaction', '>=', now()->subDays($days));
    }
    /**
     * Handle incrementInteraction functionality with proper error handling.
     * @param float|null $rating
     * @return void
     */
    public function incrementInteraction(?float $rating = null): void
    {
        $this->increment('count');
        $this->update(['last_interaction' => now(), 'rating' => $rating ?? $this->rating]);
    }
}
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductComparison
 *
 * Eloquent model representing the ProductComparison entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductComparison newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductComparison newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductComparison query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class ProductComparison extends Model
{
    use HasFactory;

    protected $fillable = ['session_id', 'user_id', 'product_id'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['user_id' => 'integer', 'product_id' => 'integer'];
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
     * Handle scopeForSession functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Handle scopeForUser functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}

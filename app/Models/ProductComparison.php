<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\UserOwnedScope;
use Database\Factories\ProductComparisonFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductComparison
 *
 * Eloquent model representing the ProductComparison entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property int|null $user_id
 * @property int|null $product_id
 * @property string   $session_id
 * @property mixed    $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductComparison newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductComparison newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductComparison query()
 * @method static Builder|ProductComparison                               forSession(string $sessionId)
 * @method static Builder|ProductComparison                               forUser(int $userId)
 *
 * @mixin \Eloquent
 */
/**
 * @use HasFactory<ProductComparisonFactory>
 */
#[ScopedBy([UserOwnedScope::class])]
final class ProductComparison extends Model
{
    /** @use HasFactory<ProductComparisonFactory> */
    use HasFactory;

    use OrdersByName;

    /**
     * Ensure alphabetical ordering groups comparisons by their session
     * identifier whenever orderedByName is invoked.
     */
    protected string $nameColumn = 'session_id';

    /**
     * @var list<string>
     */
    protected $fillable = ['session_id', 'user_id', 'product_id'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        // Normalize foreign keys and session identifiers when hydrating the model.
        return ['session_id' => 'string', 'user_id' => 'integer', 'product_id' => 'integer'];
    }

    /**
     * Handle user functionality with proper error handling.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, $this> $relation */
        $relation = $this->belongsTo(User::class);

        // Link each comparison to the owning customer account when available.
        return $relation;
    }

    /**
     * Handle product functionality with proper error handling.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        /** @var BelongsTo<Product, $this> $relation */
        $relation = $this->belongsTo(Product::class);

        // Resolve the catalog product being compared.
        return $relation;
    }

    /**
     * Handle scopeForSession functionality with proper error handling.
     *
     * @param  Builder<ProductComparison> $query
     * @return Builder<ProductComparison>
     */
    public function scopeForSession(Builder $query, string $sessionId): Builder
    {
        // Filter comparisons that belong to the provided anonymous session identifier.
        return $query->where('session_id', $sessionId);
    }

    /**
     * Handle scopeForUser functionality with proper error handling.
     *
     * @param  Builder<ProductComparison> $query
     * @return Builder<ProductComparison>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        // Filter comparisons that are associated with the specified authenticated user.
        return $query->where('user_id', $userId);
    }
}

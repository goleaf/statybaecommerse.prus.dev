<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * UserProductInteraction
 * 
 * Stores user-item interaction matrix for collaborative filtering.
 */
class UserProductInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'interaction_type',
        'rating',
        'count',
        'first_interaction',
        'last_interaction',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'count' => 'integer',
        'first_interaction' => 'datetime',
        'last_interaction' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('interaction_type', $type);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeWithMinCount($query, int $minCount)
    {
        return $query->where('count', '>=', $minCount);
    }

    public function scopeWithMinRating($query, float $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('last_interaction', '>=', now()->subDays($days));
    }

    public function incrementInteraction(?float $rating = null): void
    {
        $this->increment('count');
        $this->update([
            'last_interaction' => now(),
            'rating' => $rating ?? $this->rating,
        ]);
    }
}

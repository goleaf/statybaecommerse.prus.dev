<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * UserPreference
 * 
 * Stores user preferences for personalized recommendations.
 */
class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preference_type',
        'preference_key',
        'preference_score',
        'metadata',
        'last_updated',
    ];

    protected $casts = [
        'preference_score' => 'decimal:6',
        'metadata' => 'array',
        'last_updated' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('preference_type', $type);
    }

    public function scopeWithMinScore($query, float $minScore)
    {
        return $query->where('preference_score', '>=', $minScore);
    }

    public function scopeOrderedByScore($query)
    {
        return $query->orderByDesc('preference_score');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('last_updated', '>=', now()->subDays($days));
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property string $decision
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $decided_at
 */
final class PostApproval extends Model
{
    use HasFactory;

    // Guarded attributes are explicitly enumerated to avoid accidental mass-assignment.
    protected $fillable = ['post_id', 'user_id', 'decision', 'notes', 'decided_at'];

    protected function casts(): array
    {
        // Ensure native PHP types are returned for common attributes when hydrating the model.
        return [
            'post_id' => 'integer',
            'user_id' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Post, PostApproval>
     */
    public function post(): BelongsTo
    {
        // The approval belongs to the marketing post it moderates.
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<User, PostApproval>
     */
    public function user(): BelongsTo
    {
        // Track the moderator that recorded the decision.
        return $this->belongsTo(User::class);
    }
}

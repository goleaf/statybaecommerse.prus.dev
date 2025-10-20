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

    protected $fillable = ['post_id', 'user_id', 'decision', 'notes', 'decided_at'];

    protected function casts(): array
    {
        return [
            'post_id' => 'integer',
            'user_id' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

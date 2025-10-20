<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $news_id
 * @property int $user_id
 * @property string $decision
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $decided_at
 */
final class NewsApproval extends Model
{
    use HasFactory;

    protected $fillable = ['news_id', 'user_id', 'decision', 'notes', 'decided_at'];

    protected function casts(): array
    {
        return [
            'news_id' => 'integer',
            'user_id' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Taggable
 *
 * @property int                             $id
 * @property int                             $tag_id
 * @property string                          $taggable_type
 * @property int                             $taggable_id
 * @property int|null                        $tagged_by
 * @property \Illuminate\Support\Carbon      $tagged_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class Taggable extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_id',
        'taggable_type',
        'taggable_id',
        'tagged_by',
        'tagged_at',
    ];

    protected $casts = [
        'tagged_at' => 'datetime',
    ];

    // Relationships

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function tagger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tagged_by');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class NewsComment extends Model
{
    use HasFactory;

    protected $table = 'news_comments';

    protected $fillable = [
        'news_id',
        'parent_id',
        'author_name',
        'author_email',
        'content',
        'is_approved',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'news_id' => 'integer',
            'parent_id' => 'integer',
            'is_approved' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NewsComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(NewsComment::class, 'parent_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function isReply(): bool
    {
        return ! is_null($this->parent_id);
    }

    public function hasReplies(): bool
    {
        return $this->replies()->count() > 0;
    }
}

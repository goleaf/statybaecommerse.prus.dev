<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\VisibleScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * NewsComment
 *
 * Eloquent model representing the NewsComment entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NewsComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsComment query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, ApprovedScope::class, VisibleScope::class])]
final class NewsComment extends Model
{
    use HasFactory;

    protected $table = 'news_comments';

    protected $fillable = ['news_id', 'parent_id', 'author_name', 'author_email', 'content', 'is_approved', 'is_visible', 'is_active'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['news_id' => 'integer', 'parent_id' => 'integer', 'is_approved' => 'boolean', 'is_visible' => 'boolean', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        self::addGlobalScope('active_flag', function (Builder $builder): void {
            $builder->where('is_active', true);
        });
        self::creating(function (self $comment): void {
            if (! array_key_exists('is_active', $comment->getAttributes())) {
                $comment->is_active = (bool) ($comment->is_visible ?? true);
            }
        });
    }

    /**
     * Handle news functionality with proper error handling.
     */
    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Handle parent functionality with proper error handling.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(NewsComment::class, 'parent_id');
    }

    /**
     * Handle replies functionality with proper error handling.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(NewsComment::class, 'parent_id');
    }

    /**
     * Handle scopeApproved functionality with proper error handling.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    /**
     * Handle scopeVisible functionality with proper error handling.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /**
     * Handle scopeTopLevel functionality with proper error handling.
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Handle isReply functionality with proper error handling.
     */
    public function isReply(): bool
    {
        return ! is_null($this->parent_id);
    }

    /**
     * Handle hasReplies functionality with proper error handling.
     */
    public function hasReplies(): bool
    {
        return $this->replies()->count() > 0;
    }
}

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
use function filter_var;

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

    /**
     * Provide scope hints so the shared Active and Visible global scopes avoid schema introspection during tests.
     *
     * @var array<string, bool>
     */
    public const SCOPE_COLUMN_HINTS = [
        'is_active'  => true,
        'is_visible' => true,
    ];

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
        static::creating(function (self $comment): void {
            if ($comment->isDirty('is_active') && $comment->getAttribute('is_active') !== null) {
                return;
            }

            $isVisible = $comment->getAttribute('is_visible');

            if ($isVisible === null) {
                $comment->is_active = true;

                return;
            }

            $normalizedVisibility = filter_var($isVisible, FILTER_VALIDATE_BOOL, ['flags' => FILTER_NULL_ON_FAILURE]);

            $comment->is_active = $normalizedVisibility ?? true;
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
     * Handle scopeOrderedByName functionality with proper error handling.
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Order the query results by the author name to provide consistent sorting for listings.
        return $query->orderBy('author_name');
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

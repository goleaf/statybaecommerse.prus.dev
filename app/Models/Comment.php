<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasHierarchy;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Comment
 *
 * @property int                             $id
 * @property string                          $content
 * @property int                             $user_id
 * @property string                          $commentable_type
 * @property int                             $commentable_id
 * @property int|null                        $parent_id
 * @property bool                            $is_approved
 * @property bool                            $is_pinned
 * @property int                             $likes_count
 * @property array|null                      $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static Builder|Comment approved()
 * @method static Builder|Comment pinned()
 * @method static Builder|Comment rootComments()
 */
#[ScopedBy([ApprovedScope::class])]
final class Comment extends Model
{
    use HasFactory, HasHierarchy, SoftDeletes;

    protected $fillable = [
        'content',
        'user_id',
        'commentable_type',
        'commentable_id',
        'parent_id',
        'is_approved',
        'is_pinned',
        'likes_count',
        'metadata',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_pinned'   => 'boolean',
        'likes_count' => 'integer',
        'metadata'    => 'array',
    ];

    // Relationships

    /**
     * User who wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The model this comment belongs to (polymorphic).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Parent comment (self-referencing for nested comments).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Child comments (replies).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * All descendants (recursive nested comments).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Files attached to this comment (polymorphic).
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    // Scopes

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeRootComments(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeByUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for efficient polymorphic queries with proper index usage.
     * Uses the composite index (commentable_type, commentable_id) for optimal performance.
     */
    public function scopeForEntity(Builder $query, Model $entity): Builder
    {
        return $query->where([
            ['commentable_type', '=', $entity->getMorphClass()],
            ['commentable_id', '=', $entity->getKey()],
        ]);
    }

    /**
     * Scope for paginated comments with optimized ordering and eager loading.
     * Uses composite indexes for efficient filtering and sorting.
     */
    public function scopePaginatedForEntity(Builder $query, Model $entity, int $perPage = 15): Builder
    {
        return $query->forEntity($entity)
            ->approved()
            ->with(['user:id,name,avatar_url', 'children' => function ($q) {
                $q->approved()
                    ->with('user:id,name,avatar_url')
                    ->orderBy('created_at', 'asc')
                    ->limit(3);
            }])
            ->rootComments()
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope for efficient approved comments with index optimization.
     */
    public function scopeApprovedForEntity(Builder $query, Model $entity): Builder
    {
        return $query->where([
            ['commentable_type', '=', $entity->getMorphClass()],
            ['commentable_id', '=', $entity->getKey()],
            ['is_approved', '=', true],
        ]);
    }

    // Helper methods

    /**
     * Check if comment is a root comment.
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Check if comment is a reply.
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Get comment thread depth.
     */
    public function getDepth(): int
    {
        $depth = 0;
        $current = $this;

        while ($current->parent) {
            $depth++;
            $current = $current->parent;
        }

        return $depth;
    }

    /**
     * Get root comment of this thread.
     */
    public function getRootComment(): Comment
    {
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
        }

        return $current;
    }

    /**
     * Get comment hierarchy path.
     */
    public function getHierarchyPath(): array
    {
        $path = [$this];
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            array_unshift($path, $current);
        }

        return $path;
    }

    /**
     * Get all replies count (including nested).
     */
    public function getTotalRepliesCount(): int
    {
        return $this->descendants()->count();
    }

    /**
     * Approve comment.
     */
    public function approve(): void
    {
        $this->update(['is_approved' => true]);
    }

    /**
     * Pin comment.
     */
    public function pin(): void
    {
        $this->update(['is_pinned' => true]);
    }

    /**
     * Unpin comment.
     */
    public function unpin(): void
    {
        $this->update(['is_pinned' => false]);
    }

    /**
     * Get parent key name for hierarchy.
     */
    protected function getParentKeyName(): string
    {
        return 'parent_id';
    }
}

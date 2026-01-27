<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Comment;
use App\Models\File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait for standardized polymorphic relationships.
 */
trait HasPolymorphicRelationships
{
    /**
     * Comments relationship (polymorphic) - optimized for composite index usage.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Approved comments only - uses composite index efficiently.
     */
    public function approvedComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->approved()
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Root comments (no parent) - optimized with composite index.
     */
    public function rootComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->rootComments()
            ->approved()
            ->with(['user:id,name'])
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Paginated comments with efficient loading.
     */
    public function paginatedComments(int $perPage = 15)
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->paginatedForEntity($this, $perPage)
            ->paginate($perPage);
    }

    /**
     * Files relationship (polymorphic).
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Image files only.
     */
    public function images(): MorphMany
    {
        return $this->files()->where('mime_type', 'like', 'image/%');
    }

    /**
     * Document files only.
     */
    public function documents(): MorphMany
    {
        return $this->files()->whereIn('mime_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    // Helper methods for polymorphic relationships

    /**
     * Add comment to model.
     */
    public function addComment(string $content, \App\Models\User $user, ?Comment $parent = null): Comment
    {
        return $this->comments()->create([
            'content'     => $content,
            'user_id'     => $user->id,
            'parent_id'   => $parent?->id,
            'is_approved' => true,
        ]);
    }

    /**
     * Attach file to model.
     */
    public function attachFile(array $fileData, \App\Models\User $uploader): File
    {
        return $this->files()->create(array_merge($fileData, [
            'uploaded_by' => $uploader->id,
        ]));
    }

    /**
     * Get comment count.
     */
    public function getCommentCount(): int
    {
        return $this->comments()->count();
    }

    /**
     * Get file count.
     */
    public function getFileCount(): int
    {
        return $this->files()->count();
    }

    // Scopes for polymorphic queries

    /**
     * Scope for models with comments.
     */
    public function scopeWithComments(Builder $query): Builder
    {
        return $query->whereHas('comments');
    }

    /**
     * Scope for models with files.
     */
    public function scopeWithFiles(Builder $query): Builder
    {
        return $query->whereHas('files');
    }
}

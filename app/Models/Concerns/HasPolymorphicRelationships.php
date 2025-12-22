<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Comment;
use App\Models\File;
use App\Models\Tag;
use App\Models\Taggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Trait for standardized polymorphic relationships.
 */
trait HasPolymorphicRelationships
{
    /**
     * Comments relationship (polymorphic).
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Approved comments only.
     */
    public function approvedComments(): MorphMany
    {
        return $this->comments()->where('is_approved', true);
    }

    /**
     * Root comments (no parent).
     */
    public function rootComments(): MorphMany
    {
        return $this->comments()->whereNull('parent_id');
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

    /**
     * Tags relationship (polymorphic many-to-many).
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables')
            ->withPivot(['tagged_by', 'tagged_at'])
            ->withTimestamps();
    }

    /**
     * Tags by type.
     */
    public function tagsByType(string $type): MorphToMany
    {
        return $this->tags()->where('type', $type);
    }

    /**
     * Activity logs (polymorphic).
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(\App\Models\ActivityLog::class, 'subject')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Recent activity logs.
     */
    public function recentActivityLogs(int $days = 30): MorphMany
    {
        return $this->activityLogs()
            ->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods for polymorphic relationships

    /**
     * Add comment to model.
     */
    public function addComment(string $content, \App\Models\User $user, ?Comment $parent = null): Comment
    {
        return $this->comments()->create([
            'content' => $content,
            'user_id' => $user->id,
            'parent_id' => $parent?->id,
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
     * Add tag to model.
     */
    public function addTag(Tag $tag, ?\App\Models\User $tagger = null): void
    {
        $this->tags()->attach($tag->id, [
            'tagged_by' => $tagger?->id,
            'tagged_at' => now(),
        ]);
    }

    /**
     * Remove tag from model.
     */
    public function removeTag(Tag $tag): void
    {
        $this->tags()->detach($tag->id);
    }

    /**
     * Sync tags for model.
     */
    public function syncTags(array $tagIds, ?\App\Models\User $tagger = null): void
    {
        $syncData = [];
        foreach ($tagIds as $tagId) {
            $syncData[$tagId] = [
                'tagged_by' => $tagger?->id,
                'tagged_at' => now(),
            ];
        }
        
        $this->tags()->sync($syncData);
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

    /**
     * Get tag names as array.
     */
    public function getTagNames(): array
    {
        return $this->tags()->pluck('name')->toArray();
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

    /**
     * Scope for models with specific tags.
     */
    public function scopeWithTags(Builder $query, array $tagNames): Builder
    {
        return $query->whereHas('tags', function (Builder $tagQuery) use ($tagNames) {
            $tagQuery->whereIn('name', $tagNames);
        });
    }

    /**
     * Scope for models without any tags.
     */
    public function scopeWithoutTags(Builder $query): Builder
    {
        return $query->whereDoesntHave('tags');
    }
}
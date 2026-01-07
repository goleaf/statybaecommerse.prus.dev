<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasHierarchy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Task
 *
 * @property int                             $id
 * @property string                          $title
 * @property string|null                     $description
 * @property string                          $status
 * @property string                          $priority
 * @property int                             $project_id
 * @property int                             $created_by
 * @property int|null                        $parent_task_id
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property array|null                      $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Task active()
 * @method static Builder|Task completed()
 * @method static Builder|Task overdue()
 * @method static Builder|Task byPriority(string $priority)
 */
final class Task extends Model
{
    use HasFactory, HasHierarchy;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'project_id',
        'created_by',
        'parent_task_id',
        'due_date',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'due_date'     => 'datetime',
        'completed_at' => 'datetime',
        'metadata'     => 'array',
    ];

    // Relationships

    /**
     * Project this task belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * User who created this task.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Parent task (self-referencing).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Child tasks (self-referencing).
     */
    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * All descendants (recursive).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Users assigned to this task with responsibilities.
     */
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot(['responsibility', 'assigned_at', 'completed_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * Primary assignees.
     */
    public function primaryAssignees(): BelongsToMany
    {
        return $this->assignees()->wherePivot('responsibility', 'assignee');
    }

    /**
     * Reviewers.
     */
    public function reviewers(): BelongsToMany
    {
        return $this->assignees()->wherePivot('responsibility', 'reviewer');
    }

    /**
     * Watchers.
     */
    public function watchers(): BelongsToMany
    {
        return $this->assignees()->wherePivot('responsibility', 'watcher');
    }

    /**
     * Comments on this task (polymorphic).
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Files attached to this task (polymorphic).
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Tags for this task (polymorphic many-to-many).
     */
    public function tags(): MorphMany
    {
        return $this->morphMany(Taggable::class, 'taggable');
    }

    /**
     * Organization through project (has-one-through).
     */
    public function organization(): BelongsTo
    {
        return $this->project()->getRelated()->organization();
    }

    /**
     * Comments on child tasks (has-many-through).
     */
    public function childComments(): HasManyThrough
    {
        return $this->hasManyThrough(Comment::class, Task::class, 'parent_task_id', 'commentable_id')
            ->where('commentable_type', Task::class);
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeRootTasks(Builder $query): Builder
    {
        return $query->whereNull('parent_task_id');
    }

    public function scopeSubtasks(Builder $query): Builder
    {
        return $query->whereNotNull('parent_task_id');
    }

    public function scopeAssignedTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('assignees', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    // Helper methods

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               ! in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get task hierarchy path.
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
     * Assign user to task.
     */
    public function assignUser(User $user, string $responsibility = 'assignee', ?string $notes = null): void
    {
        $this->assignees()->attach($user->id, [
            'responsibility' => $responsibility,
            'assigned_at'    => now(),
            'notes'          => $notes,
        ]);
    }

    /**
     * Mark task as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // Update pivot for assignees
        $this->assignees()->updateExistingPivot(
            $this->assignees()->pluck('user_id'),
            ['completed_at' => now()]
        );
    }

    /**
     * Get parent key name for hierarchy.
     */
    protected function getParentKeyName(): string
    {
        return 'parent_task_id';
    }
}

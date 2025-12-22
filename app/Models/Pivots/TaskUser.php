<?php

declare(strict_types=1);

namespace App\Models\Pivots;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * TaskUser Pivot
 *
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property string $responsibility
 * @property \Illuminate\Support\Carbon $assigned_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class TaskUser extends Pivot
{
    protected $table = 'task_user';

    protected $fillable = [
        'task_id',
        'user_id',
        'responsibility',
        'assigned_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods

    /**
     * Check if assignment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Mark assignment as completed.
     */
    public function markCompleted(?string $notes = null): void
    {
        $this->update([
            'completed_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Check if user is primary assignee.
     */
    public function isPrimaryAssignee(): bool
    {
        return $this->responsibility === 'assignee';
    }

    /**
     * Check if user is reviewer.
     */
    public function isReviewer(): bool
    {
        return $this->responsibility === 'reviewer';
    }

    /**
     * Check if user is watcher.
     */
    public function isWatcher(): bool
    {
        return $this->responsibility === 'watcher';
    }

    /**
     * Get assignment duration.
     */
    public function getDuration(): ?int
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->assigned_at->diffInHours($this->completed_at);
    }
}
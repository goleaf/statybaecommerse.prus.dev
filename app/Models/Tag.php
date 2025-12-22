<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tag
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $color
 * @property string|null $description
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Tag byType(string $type)
 */
final class Tag extends Model
{
    use HasFactory, OrdersByName;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'type',
    ];

    // Relationships

    /**
     * All taggable relationships.
     */
    public function taggables(): HasMany
    {
        return $this->hasMany(Taggable::class);
    }

    /**
     * Get all users tagged with this tag.
     */
    public function users()
    {
        return $this->morphedByMany(User::class, 'taggable', 'taggables');
    }

    /**
     * Get all organizations tagged with this tag.
     */
    public function organizations()
    {
        return $this->morphedByMany(Organization::class, 'taggable', 'taggables');
    }

    /**
     * Get all projects tagged with this tag.
     */
    public function projects()
    {
        return $this->morphedByMany(Project::class, 'taggable', 'taggables');
    }

    /**
     * Get all tasks tagged with this tag.
     */
    public function tasks()
    {
        return $this->morphedByMany(Task::class, 'taggable', 'taggables');
    }

    /**
     * Get all comments tagged with this tag.
     */
    public function comments()
    {
        return $this->morphedByMany(Comment::class, 'taggable', 'taggables');
    }

    /**
     * Get all files tagged with this tag.
     */
    public function files()
    {
        return $this->morphedByMany(File::class, 'taggable', 'taggables');
    }

    // Scopes

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopePopular(Builder $query, int $limit = 10): Builder
    {
        return $query->withCount('taggables')
            ->orderBy('taggables_count', 'desc')
            ->limit($limit);
    }

    // Helper methods

    /**
     * Get usage count for this tag.
     */
    public function getUsageCount(): int
    {
        return $this->taggables()->count();
    }

    /**
     * Get all models tagged with this tag grouped by type.
     */
    public function getTaggedModels(): array
    {
        $taggables = $this->taggables()->with('taggable')->get();
        
        return $taggables->groupBy('taggable_type')
            ->map(function ($group) {
                return $group->pluck('taggable');
            })
            ->toArray();
    }
}
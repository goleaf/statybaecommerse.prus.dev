<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait for hierarchical (self-referencing) relationships.
 */
trait HasHierarchy
{
    /**
     * Parent relationship.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, $this->getParentKeyName());
    }

    /**
     * Children relationship.
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, $this->getParentKeyName())
            ->orderBy($this->getHierarchyOrderColumn());
    }

    /**
     * All descendants (recursive).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * All ancestors (recursive).
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Get root node.
     */
    public function getRoot(): static
    {
        $current = $this;
        
        while ($current->parent) {
            $current = $current->parent;
        }
        
        return $current;
    }

    /**
     * Get siblings (same parent).
     */
    public function siblings(): HasMany
    {
        return $this->hasMany(static::class, $this->getParentKeyName())
            ->where($this->getKeyName(), '!=', $this->getKey())
            ->when($this->parent_id, function (Builder $query) {
                $query->where($this->getParentKeyName(), $this->parent_id);
            }, function (Builder $query) {
                $query->whereNull($this->getParentKeyName());
            });
    }

    /**
     * Get all leaf nodes (no children).
     */
    public function scopeLeaves(Builder $query): Builder
    {
        return $query->whereDoesntHave('children');
    }

    /**
     * Get root nodes (no parent).
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull($this->getParentKeyName());
    }

    /**
     * Get nodes at specific depth.
     */
    public function scopeAtDepth(Builder $query, int $depth): Builder
    {
        if ($depth === 0) {
            return $query->whereNull($this->getParentKeyName());
        }

        // This is a simplified version - for production, consider using a closure table
        // or materialized path pattern for better performance
        return $query->whereHas('parent', function (Builder $parentQuery) use ($depth) {
            if ($depth > 1) {
                $parentQuery->atDepth($depth - 1);
            } else {
                $parentQuery->whereNull($this->getParentKeyName());
            }
        });
    }

    /**
     * Get hierarchy path as array.
     */
    public function getPath(): array
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
     * Get hierarchy path as string.
     */
    public function getPathString(string $separator = ' > ', string $attribute = 'name'): string
    {
        return collect($this->getPath())
            ->pluck($attribute)
            ->implode($separator);
    }

    /**
     * Get depth level.
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
     * Check if node is root.
     */
    public function isRoot(): bool
    {
        return $this->{$this->getParentKeyName()} === null;
    }

    /**
     * Check if node is leaf.
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Check if node is ancestor of another node.
     */
    public function isAncestorOf(self $node): bool
    {
        return $node->ancestors()->contains($this->getKey());
    }

    /**
     * Check if node is descendant of another node.
     */
    public function isDescendantOf(self $node): bool
    {
        return $this->ancestors()->contains($node->getKey());
    }

    /**
     * Move node to new parent.
     */
    public function moveTo(?self $newParent): bool
    {
        // Prevent circular references
        if ($newParent && $this->isAncestorOf($newParent)) {
            return false;
        }

        return $this->update([
            $this->getParentKeyName() => $newParent?->getKey(),
        ]);
    }

    /**
     * Get all descendants as flat collection.
     */
    public function getAllDescendants(): Collection
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    /**
     * Get tree structure as nested array.
     */
    public function toTree(): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name ?? $this->title ?? (string) $this->getKey(),
            'children' => $this->children->map(fn($child) => $child->toTree())->toArray(),
        ];
    }

    /**
     * Get parent key name.
     */
    protected function getParentKeyName(): string
    {
        return property_exists($this, 'parentKey') ? $this->parentKey : 'parent_id';
    }

    /**
     * Get hierarchy order column.
     */
    protected function getHierarchyOrderColumn(): string
    {
        return property_exists($this, 'hierarchyOrderColumn') ? $this->hierarchyOrderColumn : 'created_at';
    }

    /**
     * Build tree from flat collection.
     */
    public static function buildTree(Collection $items, ?int $parentId = null): Collection
    {
        return $items
            ->where(static::make()->getParentKeyName(), $parentId)
            ->map(function ($item) use ($items) {
                $item->children = static::buildTree($items, $item->getKey());
                return $item;
            })
            ->values();
    }
}
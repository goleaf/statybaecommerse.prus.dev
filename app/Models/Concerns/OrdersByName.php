<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait OrdersByName
 *
 * Provides a reusable orderedByName scope that can be mixed into
 * models containing a name-like column so common lookups remain
 * consistent across the codebase.
 */
trait OrdersByName
{
    /**
     * Determine the column used for ordering operations.
     */
    protected function getNameColumn(): string
    {
        /** @phpstan-ignore-next-line function.alreadyNarrowedType */
        return property_exists($this, 'nameColumn') ? $this->nameColumn : 'name';
    }

    /**
     * Scope to order records by the configured name column ascending.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel> $query
     * @return Builder<TModel>
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        return $query->orderBy($this->getNameColumn());
    }
}

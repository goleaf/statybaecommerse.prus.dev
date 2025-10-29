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
     * Determine the column used for ordering operations while remaining
     * tolerant of legacy models that expose an overriding property dynamically.
     */
    protected function getNameColumn(): string
    {
        // Fall back to the default column name whenever the concrete model
        // does not declare a custom $nameColumn configuration explicitly.
        if (property_exists($this, 'nameColumn')) {
            // Casting to string ensures defensive safety even when the host
            // model accidentally exposes the property as a different scalar.
            return (string) $this->nameColumn;
        }

        // Provide a sensible default so models can opt-in without redeclaring
        // anything—this keeps legacy models working out of the box.
        return 'name';
    }

    /**
     * Scope to order records by the configured name column while guarding the
     * direction parameter to protect against unexpected SQL injections.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel> $query
     * @return Builder<TModel>
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        // Normalise the direction argument so callers cannot influence the
        // generated SQL beyond toggling ascending or descending order.
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        // Qualify the column with the table name to ensure explicit table qualification
        // in generated SQL queries, which is important for joins and test assertions.
        $qualifiedColumn = $query->qualifyColumn($this->getNameColumn());
        $column = $query->getQuery()->grammar->wrap($qualifiedColumn);

        return $query->orderByRaw(sprintf('%s %s', $column, $direction));
    }
}

<?php

declare(strict_types=1);

namespace App\Support\Cache;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Optimizes cache serialization by converting Eloquent models to lightweight arrays.
 * Reduces Livewire hydration overhead and cache storage size.
 */
final class SerializationOptimizer
{
    /**
     * Convert a paginated result to a cache-friendly format.
     */
    public static function optimizePaginator(LengthAwarePaginator $paginator): array
    {
        return [
            'data'         => self::optimizeCollection($paginator->getCollection()),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'from'         => $paginator->firstItem(),
            'to'           => $paginator->lastItem(),
            'path'         => $paginator->path(),
        ];
    }

    /**
     * Convert a collection to a cache-friendly format.
     */
    public static function optimizeCollection(Collection|EloquentCollection $collection): array
    {
        return $collection->map(function ($item) {
            if ($item instanceof Model) {
                return self::optimizeModel($item);
            }

            return $item;
        })->toArray();
    }

    /**
     * Convert a model to a lightweight array for caching.
     */
    public static function optimizeModel(Model $model): array
    {
        $attributes = $model->getAttributes();

        // Include loaded relations as optimized arrays
        $relations = [];
        foreach ($model->getRelations() as $key => $relation) {
            if ($relation instanceof Collection || $relation instanceof EloquentCollection) {
                $relations[$key] = self::optimizeCollection($relation);
            } elseif ($relation instanceof Model) {
                $relations[$key] = self::optimizeModel($relation);
            } else {
                $relations[$key] = $relation;
            }
        }

        return [
            'attributes'  => $attributes,
            'relations'   => $relations,
            'model_class' => get_class($model),
        ];
    }

    /**
     * Restore a paginator from cached data.
     */
    public static function restorePaginator(array $data, string $path = '', array $query = []): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            items: $data['data'],
            total: $data['total'],
            perPage: $data['per_page'],
            currentPage: $data['current_page'],
            options: [
                'path'  => $path ?: $data['path'],
                'query' => $query,
            ]
        );
    }

    /**
     * Check if data is in optimized format.
     */
    public static function isOptimized(mixed $data): bool
    {
        if (! is_array($data)) {
            return false;
        }

        // Check for paginator structure
        if (isset($data['data'], $data['current_page'], $data['total'])) {
            return true;
        }

        // Check for optimized model structure
        if (isset($data['attributes'], $data['model_class'])) {
            return true;
        }

        // Check for collection of optimized models
        if (is_array($data) && ! empty($data)) {
            $first = reset($data);

            return is_array($first) && isset($first['attributes'], $first['model_class']);
        }

        return false;
    }
}

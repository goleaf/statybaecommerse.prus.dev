<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class PaginationService
{
    /**
     * Apply pagination with onEachSide configuration
     */
    public static function paginateWithOnEachSide(
        Builder $query,
        int $perPage = 12,
        int $onEachSide = 2,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        return $query->paginate($perPage, ['*'], $pageName)->onEachSide($onEachSide);
    }

    /**
     * Get pagination configuration based on context
     */
    public static function getPaginationConfig(string $context = 'default'): array
    {
        return match ($context) {
            'news' => [
                'perPage' => 12,
                'onEachSide' => 2,
                'perPageOptions' => [12, 24, 48, 96],
            ],
            'posts' => [
                'perPage' => 12,
                'onEachSide' => 2,
                'perPageOptions' => [12, 24, 48, 96],
            ],
            'products' => [
                'perPage' => 12,
                'onEachSide' => 2,
                'perPageOptions' => [12, 24, 48, 96],
            ],
            'collections' => [
                'perPage' => 12,
                'onEachSide' => 2,
                'perPageOptions' => [12, 24, 48, 96],
            ],
            'admin' => [
                'perPage' => 20,
                'onEachSide' => 1,
                'perPageOptions' => [10, 20, 50, 100],
            ],
            'api' => [
                'perPage' => 15,
                'onEachSide' => 1,
                'perPageOptions' => [10, 15, 25, 50],
            ],
            default => [
                'perPage' => 12,
                'onEachSide' => 2,
                'perPageOptions' => [12, 24, 48, 96],
            ],
        };
    }

    /**
     * Apply pagination with context-based configuration
     */
    public static function paginateWithContext(
        Builder $query,
        string $context = 'default',
        ?int $perPage = null,
        ?int $onEachSide = null
    ): LengthAwarePaginator {
        $config = self::getPaginationConfig($context);
        
        $perPage = $perPage ?? $config['perPage'];
        $onEachSide = $onEachSide ?? $config['onEachSide'];
        
        return self::paginateWithOnEachSide($query, $perPage, $onEachSide);
    }

    /**
     * Get responsive onEachSide value based on screen size
     */
    public static function getResponsiveOnEachSide(): array
    {
        return [
            'mobile' => 1,
            'tablet' => 2,
            'desktop' => 3,
        ];
    }

    /**
     * Apply smart pagination that adjusts based on total pages
     */
    public static function smartPaginate(
        Builder $query,
        int $perPage = 12,
        int $maxOnEachSide = 3
    ): LengthAwarePaginator {
        $paginator = $query->paginate($perPage);
        $totalPages = $paginator->lastPage();
        
        // Adjust onEachSide based on total pages
        $onEachSide = match (true) {
            $totalPages <= 5 => 2,
            $totalPages <= 10 => 2,
            $totalPages <= 20 => 2,
            default => min($maxOnEachSide, 3),
        };
        
        return $paginator->onEachSide($onEachSide);
    }

    /**
     * Apply pagination with skipWhile filtering for collections
     */
    public static function paginateWithSkipWhile(
        Collection $collection,
        callable $skipWhileCallback,
        int $perPage = 12,
        int $onEachSide = 2,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        $filteredCollection = $collection->skipWhile($skipWhileCallback);
        
        $currentPage = request()->get($pageName, 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $filteredCollection->slice($offset, $perPage)->values();
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $filteredCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
        
        // Set the onEachSide property for the paginator
        $paginator->onEachSide = $onEachSide;
        
        return $paginator;
    }

    /**
     * Apply pagination with skipWhile filtering for query builder
     */
    public static function paginateQueryWithSkipWhile(
        Builder $query,
        callable $skipWhileCallback,
        int $perPage = 12,
        int $onEachSide = 2,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        $collection = $query->get();
        return self::paginateWithSkipWhile($collection, $skipWhileCallback, $perPage, $onEachSide, $pageName);
    }

    /**
     * Apply smart pagination with skipWhile filtering
     */
    public static function smartPaginateWithSkipWhile(
        Collection $collection,
        callable $skipWhileCallback,
        int $perPage = 12,
        int $maxOnEachSide = 3,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        $filteredCollection = $collection->skipWhile($skipWhileCallback);
        
        $currentPage = request()->get($pageName, 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $filteredCollection->slice($offset, $perPage)->values();
        
        $totalPages = (int) ceil($filteredCollection->count() / $perPage);
        
        // Adjust onEachSide based on total pages
        $onEachSide = match (true) {
            $totalPages <= 5 => 2,
            $totalPages <= 10 => 2,
            $totalPages <= 20 => 2,
            default => min($maxOnEachSide, 3),
        };
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $filteredCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
        
        // Set the onEachSide property for the paginator
        $paginator->onEachSide = $onEachSide;
        
        return $paginator;
    }
}

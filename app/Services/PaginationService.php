<?php

declare (strict_types=1);
namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
/**
 * PaginationService
 * 
 * Service class containing PaginationService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class PaginationService
{
    /**
     * Handle paginateWithOnEachSide functionality with proper error handling.
     * @param Builder $query
     * @param int $perPage
     * @param int $onEachSide
     * @param string $pageName
     * @return LengthAwarePaginator
     */
    public static function paginateWithOnEachSide(Builder $query, int $perPage = 12, int $onEachSide = 2, string $pageName = 'page'): LengthAwarePaginator
    {
        $paginator = $query->paginate($perPage, ['*'], $pageName);
        // Store onEachSide value for use in views
        $paginator->onEachSide = $onEachSide;
        return $paginator;
    }
    /**
     * Handle getPaginationConfig functionality with proper error handling.
     * @param string $context
     * @return array
     */
    public static function getPaginationConfig(string $context = 'default'): array
    {
        return match ($context) {
            'news' => ['perPage' => 12, 'onEachSide' => 2, 'perPageOptions' => [12, 24, 48, 96]],
            'posts' => ['perPage' => 12, 'onEachSide' => 2, 'perPageOptions' => [12, 24, 48, 96]],
            'products' => ['perPage' => 12, 'onEachSide' => 2, 'perPageOptions' => [12, 24, 48, 96]],
            'collections' => ['perPage' => 12, 'onEachSide' => 2, 'perPageOptions' => [12, 24, 48, 96]],
            'admin' => ['perPage' => 20, 'onEachSide' => 1, 'perPageOptions' => [10, 20, 50, 100]],
            'api' => ['perPage' => 15, 'onEachSide' => 1, 'perPageOptions' => [10, 15, 25, 50]],
            default => ['perPage' => 12, 'onEachSide' => 2, 'perPageOptions' => [12, 24, 48, 96]],
        };
    }
    /**
     * Handle paginateWithContext functionality with proper error handling.
     * @param Builder $query
     * @param string $context
     * @param int|null $perPage
     * @param int|null $onEachSide
     * @return LengthAwarePaginator
     */
    public static function paginateWithContext(Builder $query, string $context = 'default', ?int $perPage = null, ?int $onEachSide = null): LengthAwarePaginator
    {
        $config = self::getPaginationConfig($context);
        $perPage = $perPage ?? $config['perPage'];
        $onEachSide = $onEachSide ?? $config['onEachSide'];
        return self::paginateWithOnEachSide($query, $perPage, $onEachSide);
    }
    /**
     * Handle getResponsiveOnEachSide functionality with proper error handling.
     * @return array
     */
    public static function getResponsiveOnEachSide(): array
    {
        return ['mobile' => 1, 'tablet' => 2, 'desktop' => 3];
    }
    /**
     * Handle smartPaginate functionality with proper error handling.
     * @param Builder $query
     * @param int $perPage
     * @param int $maxOnEachSide
     * @return LengthAwarePaginator
     */
    public static function smartPaginate(Builder $query, int $perPage = 12, int $maxOnEachSide = 3): LengthAwarePaginator
    {
        // First get the total count to determine onEachSide
        $totalCount = $query->count();
        $totalPages = (int) ceil($totalCount / $perPage);
        // Adjust onEachSide based on total pages
        $onEachSide = match (true) {
            $totalPages <= 5 => 2,
            $totalPages <= 10 => 2,
            $totalPages <= 20 => 2,
            default => min($maxOnEachSide, 3),
        };
        $paginator = $query->paginate($perPage);
        // Store onEachSide value for use in views
        $paginator->onEachSide = $onEachSide;
        return $paginator;
    }
    /**
     * Handle paginateWithSkipWhile functionality with proper error handling.
     * @param Collection $collection
     * @param callable $skipWhileCallback
     * @param int $perPage
     * @param int $onEachSide
     * @param string $pageName
     * @return LengthAwarePaginator
     */
    public static function paginateWithSkipWhile(Collection $collection, callable $skipWhileCallback, int $perPage = 12, int $onEachSide = 2, string $pageName = 'page'): LengthAwarePaginator
    {
        $filteredCollection = $collection->skipWhile($skipWhileCallback);
        $currentPage = request()->get($pageName, 1);
        $offset = ($currentPage - 1) * $perPage;
        $items = $filteredCollection->slice($offset, $perPage)->values();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($items, $filteredCollection->count(), $perPage, $currentPage, ['path' => request()->url(), 'pageName' => $pageName]);
        // Set the onEachSide property for the paginator
        $paginator->onEachSide = $onEachSide;
        return $paginator;
    }
    /**
     * Handle paginateQueryWithSkipWhile functionality with proper error handling.
     * @param Builder $query
     * @param callable $skipWhileCallback
     * @param int $perPage
     * @param int $onEachSide
     * @param string $pageName
     * @return LengthAwarePaginator
     */
    public static function paginateQueryWithSkipWhile(Builder $query, callable $skipWhileCallback, int $perPage = 12, int $onEachSide = 2, string $pageName = 'page'): LengthAwarePaginator
    {
        $collection = $query->get();
        return self::paginateWithSkipWhile($collection, $skipWhileCallback, $perPage, $onEachSide, $pageName);
    }
    /**
     * Handle smartPaginateWithSkipWhile functionality with proper error handling.
     * @param Collection $collection
     * @param callable $skipWhileCallback
     * @param int $perPage
     * @param int $maxOnEachSide
     * @param string $pageName
     * @return LengthAwarePaginator
     */
    public static function smartPaginateWithSkipWhile(Collection $collection, callable $skipWhileCallback, int $perPage = 12, int $maxOnEachSide = 3, string $pageName = 'page'): LengthAwarePaginator
    {
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
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($items, $filteredCollection->count(), $perPage, $currentPage, ['path' => request()->url(), 'pageName' => $pageName]);
        // Set the onEachSide property for the paginator
        $paginator->onEachSide = $onEachSide;
        return $paginator;
    }
}
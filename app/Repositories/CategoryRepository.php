<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use App\UseCases\Category\InvalidateCategoryCache;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class CategoryRepository
{
    private const TREE_CACHE_TTL_MINUTES = 10;

    public function __construct(private readonly CacheRepository $cache)
    {
    }

    public function getVisibleTree(): Collection
    {
        $version = $this->cacheVersion(InvalidateCategoryCache::TREE_VERSION_KEY);
        $cacheKey = sprintf('categories:tree:%s', $version);

        return $this->cache->remember($cacheKey, now()->addMinutes(self::TREE_CACHE_TTL_MINUTES), static function () {
            return Category::query()
                ->visible()
                ->roots()
                ->ordered()
                ->with(['children' => static function ($query): void {
                    $query->visible()->ordered();
                }])
                ->get();
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateVisible(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Category::query()
            ->visible()
            ->ordered()
            ->withProductCounts();

        if (! empty($filters['search'])) {
            $query->where(function ($builder) use ($filters): void {
                $builder->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->paginate($perPage);
    }

    public function loadForShow(Category $category): Category
    {
        return $category->load(['children' => static function ($query): void {
            $query->ordered();
        }, 'parent']);
    }

    private function cacheVersion(string $key): string
    {
        return $this->cache->rememberForever($key, static fn (): string => Str::uuid()->toString());
    }
}

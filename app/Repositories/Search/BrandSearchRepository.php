<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class BrandSearchRepository extends AbstractSearchRepository
{
    protected function type(): string
    {
        return 'brand';
    }

    protected function searchStatement(int $limit): string
    {
        $limit = max(1, $limit);
        $brandFilters = $this->brandVisibilityFilter();
        $productFilters = $this->productPublicationFilter();

        return <<<SQL
SELECT
    b.id,
    b.name,
    b.slug,
    b.description,
    COUNT(p.id) AS products_count,
    COALESCE(bt.name, '') AS translated_name,
    COALESCE(bt.description, '') AS translated_description
FROM brands AS b
JOIN products AS p ON p.brand_id = b.id
LEFT JOIN brand_translations AS bt ON bt.brand_id = b.id AND bt.locale = ?
WHERE 1 = 1{$brandFilters}{$productFilters}
  AND b.slug IS NOT NULL
  AND (
        LOWER(b.name) LIKE ?
        OR LOWER(b.description) LIKE ?
        OR LOWER(bt.name) LIKE ?
        OR LOWER(bt.description) LIKE ?
    )
GROUP BY b.id, b.name, b.slug, b.description, translated_name, translated_description
HAVING products_count > 0
ORDER BY
    CASE
        WHEN LOWER(b.name) = ? THEN 0
        WHEN LOWER(b.name) LIKE ? THEN 1
        ELSE 2
    END,
    products_count DESC
LIMIT {$limit}
SQL;
    }

    protected function bindings(SearchQueryData $queryData, int $limit): array
    {
        $locale = app()->getLocale();
        $query = Str::lower($queryData->query());
        $wildcard = $this->wildcard($query);

        $bindings = [$locale];

        if ($this->shouldFilterByPublishedAt()) {
            $bindings[] = now()->toDateTimeString();
        }

        $bindings[] = $wildcard;
        $bindings[] = $wildcard;
        $bindings[] = $wildcard;
        $bindings[] = $wildcard;
        $bindings[] = $query;
        $bindings[] = $wildcard;

        return $bindings;
    }

    protected function mapRow(object $row, SearchQueryData $queryData): array
    {
        $productsCount = (int) $row->products_count;
        $description = $row->description ?: ($row->translated_description ?: null);

        return [
            'id' => (int) $row->id,
            'type' => 'brand',
            'title' => (string) $row->name,
            'subtitle' => __('frontend.search.brand_with_products', ['count' => $productsCount]),
            'description' => $description,
            'image' => null,
            'url' => route('brands.show', $row->slug),
            'products_count' => $productsCount,
            'relevance_score' => $this->calculateRelevanceScore($row, $queryData->query()),
        ];
    }

    private function calculateRelevanceScore(object $row, string $query): int
    {
        $score = 0;
        $normalizedQuery = Str::lower($query);
        $name = Str::lower((string) $row->name);
        $description = Str::lower((string) ($row->description ?? ''));
        $translatedDescription = Str::lower((string) ($row->translated_description ?? ''));

        if ($name === $normalizedQuery) {
            $score += 100;
        } elseif (str_contains($name, $normalizedQuery)) {
            $score += 50;
        }

        if ($description !== '' && str_contains($description, $normalizedQuery)) {
            $score += 20;
        }

        if ($translatedDescription !== '' && str_contains($translatedDescription, $normalizedQuery)) {
            $score += 10;
        }

        $score += min((int) $row->products_count, 20);

        return $score;
    }

    private function brandVisibilityFilter(): string
    {
        static $clauses;

        if ($clauses === null) {
            $parts = [];

            if (Schema::hasColumn('brands', 'is_enabled')) {
                $parts[] = '  AND b.is_enabled = 1';
            }

            if (Schema::hasColumn('brands', 'is_active')) {
                $parts[] = '  AND b.is_active = 1';
            }

            if (Schema::hasColumn('brands', 'is_visible')) {
                $parts[] = '  AND b.is_visible = 1';
            }

            $clauses = $parts ? "\n".implode("\n", $parts) : '';
        }

        return $clauses;
    }

    private function productPublicationFilter(): string
    {
        static $clauses;

        if ($clauses === null) {
            $parts = [];

            if (Schema::hasColumn('products', 'is_visible')) {
                $parts[] = '  AND p.is_visible = 1';
            }

            if (Schema::hasColumn('products', 'published_at')) {
                $parts[] = '  AND p.published_at IS NOT NULL';
                $parts[] = '  AND p.published_at <= ?';
            }

            if (Schema::hasColumn('products', 'status')) {
                $parts[] = "  AND p.status = 'published'";
            }

            $clauses = $parts ? "\n".implode("\n", $parts) : '';
        }

        return $clauses;
    }

    private function shouldFilterByPublishedAt(): bool
    {
        return Schema::hasColumn('products', 'published_at');
    }
}

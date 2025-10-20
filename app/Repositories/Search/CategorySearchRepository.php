<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
use Illuminate\Support\Str;

final class CategorySearchRepository extends AbstractSearchRepository
{
    protected function type(): string
    {
        return 'category';
    }

    protected function searchStatement(int $limit): string
    {
        $limit = max(1, $limit);

        return <<<SQL
SELECT
    c.id,
    c.name,
    c.slug,
    c.description,
    COUNT(DISTINCT cp.product_id) AS products_count,
    COALESCE(ct.name, '') AS translated_name,
    COALESCE(ct.description, '') AS translated_description
FROM categories AS c
JOIN category_product AS cp ON cp.category_id = c.id
LEFT JOIN category_translations AS ct ON ct.category_id = c.id AND ct.locale = ?
WHERE c.is_visible = 1
  AND c.slug IS NOT NULL
  AND (
        LOWER(c.name) LIKE ?
        OR LOWER(c.description) LIKE ?
        OR LOWER(ct.name) LIKE ?
        OR LOWER(ct.description) LIKE ?
    )
GROUP BY c.id, c.name, c.slug, c.description, translated_name, translated_description
HAVING products_count > 0
ORDER BY
    CASE
        WHEN LOWER(c.name) = ? THEN 0
        WHEN LOWER(c.name) LIKE ? THEN 1
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

        return [
            $locale,
            $wildcard,
            $wildcard,
            $wildcard,
            $wildcard,
            $query,
            $wildcard,
        ];
    }

    protected function mapRow(object $row, SearchQueryData $queryData): array
    {
        $productsCount = (int) $row->products_count;
        $description = $row->description ?: ($row->translated_description ?: null);

        return [
            'id' => (int) $row->id,
            'type' => 'category',
            'title' => (string) $row->name,
            'subtitle' => __('frontend.search.category_with_products', ['count' => $productsCount]),
            'description' => $description,
            'image' => null,
            'url' => route('categories.show', $row->slug),
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
}

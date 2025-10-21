<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
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
WHERE b.is_enabled = 1
  AND b.slug IS NOT NULL
  AND p.is_visible = 1
  AND p.published_at IS NOT NULL
  AND p.published_at <= ?
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

        return [
            $locale,
            now()->toDateTimeString(),
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
}

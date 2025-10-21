<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

final class ProductSearchRepository extends AbstractSearchRepository
{
    protected function type(): string
    {
        return 'product';
    }

    protected function searchStatement(int $limit): string
    {
        $limit = max(1, $limit);

        return <<<SQL
SELECT
    p.id,
    p.name,
    p.slug,
    p.short_description,
    p.description,
    p.price,
    p.is_featured,
    p.sales_count,
    p.reviews_count,
    p.average_rating,
    p.sku,
    b.name AS brand_name,
    COALESCE(pt.name, '') AS translated_name,
    COALESCE(pt.description, '') AS translated_description
FROM products AS p
LEFT JOIN brands AS b ON b.id = p.brand_id
LEFT JOIN product_translations AS pt ON pt.product_id = p.id AND pt.locale = ?
WHERE p.is_visible = 1
  AND p.published_at IS NOT NULL
  AND p.published_at <= ?
  AND p.slug IS NOT NULL
  AND p.price IS NOT NULL
  AND p.price > 0
  AND (
        LOWER(p.name) LIKE ?
        OR LOWER(p.description) LIKE ?
        OR LOWER(p.sku) LIKE ?
        OR LOWER(pt.name) LIKE ?
        OR LOWER(pt.description) LIKE ?
    )
ORDER BY
    CASE
        WHEN LOWER(p.name) = ? THEN 0
        WHEN LOWER(p.sku) = ? THEN 1
        WHEN LOWER(p.name) LIKE ? THEN 2
        ELSE 3
    END,
    p.updated_at DESC
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
            $wildcard,
            $query,
            $query,
            $wildcard,
        ];
    }

    protected function mapRow(object $row, SearchQueryData $queryData): array
    {
        $price = (float) ($row->price ?? 0);
        $subtitle = $row->brand_name ?? null;
        $description = $row->short_description ?: ($row->description ?: $row->translated_description ?: null);

        return [
            'id' => (int) $row->id,
            'type' => 'product',
            'title' => (string) $row->name,
            'subtitle' => $subtitle,
            'description' => $description,
            'price' => $price,
            'formatted_price' => Number::currency($price, 'EUR', app()->getLocale()),
            'image' => null,
            'url' => route('products.show', $row->slug),
            'relevance_score' => $this->calculateRelevanceScore($row, $queryData->query()),
            'sales_count' => (int) ($row->sales_count ?? 0),
            'reviews_count' => (int) ($row->reviews_count ?? 0),
            'average_rating' => (float) ($row->average_rating ?? 0),
            'is_featured' => (bool) $row->is_featured,
        ];
    }

    private function calculateRelevanceScore(object $row, string $query): int
    {
        $score = 0;
        $normalizedQuery = Str::lower($query);
        $name = Str::lower((string) $row->name);
        $sku = Str::lower((string) ($row->sku ?? ''));
        $description = Str::lower((string) ($row->description ?? ''));
        $translatedDescription = Str::lower((string) ($row->translated_description ?? ''));

        if ($name === $normalizedQuery) {
            $score += 100;
        } elseif (str_contains($name, $normalizedQuery)) {
            $score += 50;
        }

        if ($sku !== '' && str_contains($sku, $normalizedQuery)) {
            $score += 40;
        }

        if ($description !== '' && str_contains($description, $normalizedQuery)) {
            $score += 20;
        }

        if ($translatedDescription !== '' && str_contains($translatedDescription, $normalizedQuery)) {
            $score += 10;
        }

        if ((bool) $row->is_featured) {
            $score += 10;
        }

        return $score;
    }
}

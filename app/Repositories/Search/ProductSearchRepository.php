<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
use Illuminate\Support\Facades\Schema;
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
        $metricColumns = $this->metricProjection();
        $statusFilter = Schema::hasColumn('products', 'status') ? "  AND p.status = 'published'\n" : '';

        return <<<SQL
SELECT
    p.id,
    p.name,
    p.slug,
    p.short_description,
    p.description,
    p.price,
    p.is_featured,
    -- The historical programme exposed denormalised counters on the products table,
    -- however several of our lightweight environments (including sqlite-driven
    -- tests) never carried those nullable columns. The subqueries below keep the
    -- API contract intact without assuming the fields exist.
    (
        SELECT COALESCE(SUM(oi.quantity), 0)
        FROM order_items AS oi
        WHERE oi.product_id = p.id
    ) AS sales_count,
    (
        SELECT COUNT(*)
        FROM reviews AS r
        WHERE r.product_id = p.id
            AND r.is_approved = 1
    ) AS reviews_count,
    (
        SELECT COALESCE(AVG(r.rating), 0)
        FROM reviews AS r
        WHERE r.product_id = p.id
            AND r.is_approved = 1
    ) AS average_rating,
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
{$statusFilter}  AND p.slug IS NOT NULL
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

    /**
     * @return array<string, mixed>
     */
    protected function mapRow(object $row, SearchQueryData $queryData): array
    {
        /** @var array<string, mixed> $attributes */
        $attributes = get_object_vars($row);

        $rawPrice = $attributes['price'] ?? 0;
        $price = is_numeric($rawPrice) ? (float) $rawPrice : 0.0;
        $subtitle = isset($attributes['brand_name']) && is_string($attributes['brand_name']) && $attributes['brand_name'] !== ''
            ? $attributes['brand_name']
            : null;

        $shortDescription = $attributes['short_description'] ?? null;
        $fullDescription = $attributes['description'] ?? null;
        $translatedDescription = $attributes['translated_description'] ?? null;
        $description = is_string($shortDescription) && $shortDescription !== ''
            ? $shortDescription
            : (is_string($fullDescription) && $fullDescription !== ''
                ? $fullDescription
                : (is_string($translatedDescription) && $translatedDescription !== '' ? $translatedDescription : null));

        // Product names are stored as translated JSON payloads in the catalogue,
        // so we normalise the raw database projection to a human readable
        // string before exposing it through the API response.
        $title = $this->extractTranslatableString($attributes['name'] ?? null);
        $slug = isset($attributes['slug']) && is_string($attributes['slug']) ? $attributes['slug'] : '';
        $salesCount = $attributes['sales_count'] ?? 0;
        $reviewsCount = $attributes['reviews_count'] ?? 0;
        $averageRating = $attributes['average_rating'] ?? 0.0;

        return [
            'id'              => is_numeric($attributes['id'] ?? null) ? (int) $attributes['id'] : 0,
            'type'            => 'product',
            'title'           => $title,
            'subtitle'        => $subtitle,
            'description'     => $description,
            'price'           => $price,
            'formatted_price' => Number::currency($price, 'EUR', app()->getLocale()),
            'image'           => null,
            'url'             => route('products.show', $slug),
            'relevance_score' => $this->calculateRelevanceScore($attributes, $queryData->query()),
            'sales_count'     => is_numeric($salesCount) ? (int) $salesCount : 0,
            'reviews_count'   => is_numeric($reviewsCount) ? (int) $reviewsCount : 0,
            'average_rating'  => is_numeric($averageRating) ? (float) $averageRating : 0.0,
            'is_featured'     => (bool) ($attributes['is_featured'] ?? false),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function calculateRelevanceScore(array $attributes, string $query): int
    {
        $score = 0;
        $normalizedQuery = Str::lower($query);
        $name = isset($attributes['name']) && is_string($attributes['name'])
            ? Str::lower($attributes['name'])
            : '';
        $sku = isset($attributes['sku']) && is_string($attributes['sku'])
            ? Str::lower($attributes['sku'])
            : '';
        $description = isset($attributes['description']) && is_string($attributes['description'])
            ? Str::lower($attributes['description'])
            : '';
        $translatedDescription = isset($attributes['translated_description']) && is_string($attributes['translated_description'])
            ? Str::lower($attributes['translated_description'])
            : '';

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

        if ((bool) ($attributes['is_featured'] ?? false)) {
            $score += 10;
        }

        return $score;
    }

    private function extractTranslatableString(mixed $value): string
    {
        // The product table persists translated attributes as JSON, which means
        // raw SQL projections can surface the encoded payload. We attempt to
        // decode the value and fall back to the first non-empty string we find.
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_string($decoded) && $decoded !== '') {
                    return trim($decoded);
                }

                if (is_array($decoded)) {
                    foreach ($decoded as $candidate) {
                        if (is_string($candidate) && trim($candidate) !== '') {
                            return trim($candidate);
                        }
                    }
                }
            }

            return trim($value, " \t\n\r\0\x0B\"'");
        }

        if (is_array($value)) {
            foreach ($value as $candidate) {
                if (is_string($candidate) && trim($candidate) !== '') {
                    return trim($candidate);
                }
            }
        }

        return '';
    }

    private function metricProjection(): string
    {
        static $columns;

        if ($columns === null) {
            $columns = [
                'sales_count' => Schema::hasColumn('products', 'sales_count'),
                'reviews_count' => Schema::hasColumn('products', 'reviews_count'),
                'average_rating' => Schema::hasColumn('products', 'average_rating'),
            ];
        }

        $selects = [
            $columns['sales_count']
                ? '    p.sales_count as sales_count,'
                : '    0 as sales_count,',
            $columns['reviews_count']
                ? '    p.reviews_count as reviews_count,'
                : '    0 as reviews_count,',
            $columns['average_rating']
                ? '    p.average_rating as average_rating,'
                : '    0 as average_rating,',
        ];

        return implode("\n", $selects);
    }
}

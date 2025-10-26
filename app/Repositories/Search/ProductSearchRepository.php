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

        $shortDescription = $this->resolveTextValue($attributes['short_description'] ?? null);
        $fullDescription = $this->resolveTextValue($attributes['description'] ?? null);
        $translatedDescription = $this->resolveTextValue($attributes['translated_description'] ?? null);
        $description = $shortDescription ?? $fullDescription ?? $translatedDescription;

        $resolvedName = $this->resolveTextValue($attributes['translated_name'] ?? null)
            ?? $this->resolveTextValue($attributes['name'] ?? null)
            ?? '';
        $resolvedSlug = $this->resolveTextValue($attributes['slug'] ?? null) ?? '';
        $salesCount = $attributes['sales_count'] ?? 0;
        $reviewsCount = $attributes['reviews_count'] ?? 0;
        $averageRating = $attributes['average_rating'] ?? 0.0;
        $sku = $this->resolveTextValue($attributes['sku'] ?? null) ?? '';

        return [
            'id'              => is_numeric($attributes['id'] ?? null) ? (int) $attributes['id'] : 0,
            'type'            => 'product',
            'title'           => $resolvedName,
            'subtitle'        => $subtitle,
            'description'     => $description,
            'price'           => $price,
            'formatted_price' => Number::currency($price, 'EUR', app()->getLocale()),
            'image'           => null,
            'url'             => $resolvedSlug !== '' ? route('products.show', $resolvedSlug) : null,
            'relevance_score' => $this->calculateRelevanceScore(
                $resolvedName,
                $sku,
                $fullDescription,
                $translatedDescription,
                (bool) ($attributes['is_featured'] ?? false),
                $queryData->query()
            ),
            'sales_count'    => is_numeric($salesCount) ? (int) $salesCount : 0,
            'reviews_count'  => is_numeric($reviewsCount) ? (int) $reviewsCount : 0,
            'average_rating' => is_numeric($averageRating) ? (float) $averageRating : 0.0,
            'is_featured'    => (bool) ($attributes['is_featured'] ?? false),
        ];
    }

    private function calculateRelevanceScore(string $name, string $sku, ?string $description, ?string $translatedDescription, bool $isFeatured, string $query): int
    {
        $score = 0;
        $normalizedQuery = Str::lower($query);
        $lowerName = Str::lower($name);
        $lowerSku = Str::lower($sku);
        $lowerDescription = is_string($description) ? Str::lower($description) : '';
        $lowerTranslatedDescription = is_string($translatedDescription) ? Str::lower($translatedDescription) : '';

        if ($lowerName === $normalizedQuery) {
            $score += 100;
        } elseif (str_contains($lowerName, $normalizedQuery)) {
            $score += 50;
        }

        if ($lowerSku !== '' && str_contains($lowerSku, $normalizedQuery)) {
            $score += 40;
        }

        if ($lowerDescription !== '' && str_contains($lowerDescription, $normalizedQuery)) {
            $score += 20;
        }

        if ($lowerTranslatedDescription !== '' && str_contains($lowerTranslatedDescription, $normalizedQuery)) {
            $score += 10;
        }

        if ($isFeatured) {
            $score += 10;
        }

        return $score;
    }

    private function metricProjection(): string
    {
        static $columns;

        if ($columns === null) {
            $columns = [
                'sales_count'    => Schema::hasColumn('products', 'sales_count'),
                'reviews_count'  => Schema::hasColumn('products', 'reviews_count'),
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

    private function resolveTextValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_string($decoded) && $decoded !== '') {
                    return $this->stripWrappingQuotes($decoded);
                }

                if (is_array($decoded)) {
                    return $this->extractLocalizedValue($decoded);
                }
            }

            return $this->stripWrappingQuotes($trimmed);
        }

        if (is_array($value)) {
            return $this->extractLocalizedValue($value);
        }

        return null;
    }

    /**
     * @param array<mixed> $values
     */
    private function extractLocalizedValue(array $values): ?string
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale');

        foreach ([$locale, $fallback] as $preferredLocale) {
            if ($preferredLocale !== null && isset($values[$preferredLocale]) && is_string($values[$preferredLocale])) {
                $candidate = $this->stripWrappingQuotes($values[$preferredLocale]);

                if ($candidate !== '') {
                    return $candidate;
                }
            }
        }

        foreach ($values as $value) {
            if (is_string($value)) {
                $candidate = $this->stripWrappingQuotes($value);

                if ($candidate !== '') {
                    return $candidate;
                }
            }
        }

        return null;
    }

    private function stripWrappingQuotes(string $value): string
    {
        return trim($value, " \t\n\r\0\x0B\"'");
    }
}

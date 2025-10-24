<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Product;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use JsonException;

final class ProductSearch
{
    /**
     * @return array<int, string>
     */
    public static function byFreeText(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Product> $products */
        $products = self::baseQuery($term)
            ->limit($limit)
            ->get();

        return $products
            ->map(fn (Product $product): string => self::formatLabel($product))
            ->all();
    }

    public static function label(Product $product): string
    {
        return self::formatLabel($product);
    }

    /**
     * @return array<int, SearchResult>
     */
    public static function complex(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Product> $products */
        $products = self::baseQuery($term)
            ->limit($limit)
            ->get();

        return $products
            ->map(function (Product $product): SearchResult {
                /** @var int|string|null $identifier */
                $identifier = $product->getKey();
                $result = SearchResult::make((string) ($identifier ?? ''), self::formatLabel($product));

                /** @var string|null $rawSku */
                $rawSku = $product->getAttribute('sku');
                /** @var float|int|string|null $rawPrice */
                $rawPrice = $product->getAttribute('price');
                $price = is_numeric($rawPrice) ? (float) $rawPrice : 0.0;

                // Attach the full metadata payload so Livewire and PHP callbacks share the same structure.
                return SearchResultPayload::normalise($result, [
                    'product_id' => $product->getKey(),
                    'sku'        => $rawSku ?? '',
                    'name'       => self::resolveName($product),
                    'price'      => $price,
                ]);
            })
            ->all();
    }

    /**
     * @return Builder<Product>
     */
    private static function baseQuery(string $term): Builder
    {
        $search = trim($term);

        $builder = Product::query()
            ->withoutGlobalScopes()
            ->select(['id', 'sku', 'barcode', 'name', 'price', 'updated_at']);

        self::applyAvailabilityFilters($builder);

        if ($search !== '') {
            $builder->where(function (Builder $query) use ($search): void {
                $like = "%{$search}%";

                $query
                    ->where('sku', 'like', $like)
                    ->orWhere('barcode', 'like', $like);

                self::applyNameSearchConstraint($query, $search);
            });
        }

        $builder->orderByDesc('updated_at');

        $table = $builder->getModel()->getTable();

        if (Schema::hasColumn($table, 'deleted_at')) {
            // Respect soft delete semantics when the column exists so storefront queries stay aligned with production data.
            $builder->whereNull("{$table}.deleted_at");
        }

        return $builder;
    }

    private static function applyAvailabilityFilters(Builder $builder): void
    {
        $table = $builder->getModel()->getTable();

        if (Schema::hasColumn($table, 'is_active')) {
            $builder->where("{$table}.is_active", true);
        }

        if (Schema::hasColumn($table, 'is_visible')) {
            $builder->where("{$table}.is_visible", true);
        }

        if (Schema::hasColumn($table, 'is_enabled')) {
            $builder->where("{$table}.is_enabled", true);
        }

        if (Schema::hasColumn($table, 'status')) {
            $builder->where("{$table}.status", 'published');
        }

        if (Schema::hasColumn($table, 'published_at')) {
            $builder
                ->whereNotNull("{$table}.published_at")
                ->where("{$table}.published_at", '<=', now());
        }
    }

    private static function applyNameSearchConstraint(Builder $query, string $search): void
    {
        $like = "%{$search}%";
        $driver = $query->getConnection()->getDriverName();
        $table = $query->getModel()->getTable();
        $column = "{$table}.name";

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $query
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT({$column}, '$.en')) LIKE ?", [$like])
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT({$column}, '$.lt')) LIKE ?", [$like]);

            return;
        }

        if ($driver === 'pgsql') {
            $query
                ->orWhereRaw("({$column}->>'en') ILIKE ?", [$like])
                ->orWhereRaw("({$column}->>'lt') ILIKE ?", [$like]);

            return;
        }

        if ($driver === 'sqlite') {
            $query
                ->orWhereRaw("json_extract({$column}, '$.en') LIKE ?", [$like])
                ->orWhereRaw("json_extract({$column}, '$.lt') LIKE ?", [$like]);

            return;
        }

        $query->orWhere($column, 'like', $like);
    }

    private static function formatLabel(Product $product): string
    {
        /** @var string|null $rawSku */
        $rawSku = $product->getAttribute('sku');
        $sku = $rawSku ?? '';
        $name = self::resolveName($product);

        return trim(sprintf('[%s] %s', $sku !== '' ? $sku : '—', $name));
    }

    private static function resolveName(Product $product): string
    {
        $rawName = self::normaliseTranslatableValue($product->getAttribute('name'));

        if (is_array($rawName)) {
            $locale = app()->getLocale();
            $value = $rawName[$locale] ?? reset($rawName);

            return is_string($value) ? $value : '';
        }

        return is_string($rawName) ? $rawName : '';
    }

    private static function normaliseTranslatableValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        if ($trimmed === '' || ! str_starts_with($trimmed, '{')) {
            return $value;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $value;
        }

        return is_array($decoded) ? $decoded : $value;
    }
}

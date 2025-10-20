<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ProductSearchRepository extends AbstractSearchRepository
{
    /**
     * @return array{items: Collection<int, array<string, mixed>>, total: int}
     */
    public function search(SearchQueryData $query, int $limit): array
    {
        $base = Product::query()
            ->select(['products.*'])
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($query->brandIds() !== [], function (EloquentBuilder $builder) use ($query) {
                $builder->whereIn('brand_id', $query->brandIds());
            })
            ->when($query->categoryIds() !== [], function (EloquentBuilder $builder) use ($query) {
                $builder->whereExists(function (Builder $sub) use ($query) {
                    $sub->select(DB::raw('1'))
                        ->from('product_categories')
                        ->whereColumn('product_categories.product_id', 'products.id')
                        ->whereIn('product_categories.category_id', $query->categoryIds());
                });
            })
            ->when($query->price_min !== null, function (EloquentBuilder $builder) use ($query) {
                $builder->where('price', '>=', $query->price_min);
            })
            ->when($query->price_max !== null, function (EloquentBuilder $builder) use ($query) {
                $builder->where('price', '<=', $query->price_max);
            });

        $total = (clone $base)->count('products.id');

        $scored = $this->applyScoring(clone $base, $query);
        $scored = $this->applySort($scored, $query);
        $scored = $this->applyPagination($scored, $query, $limit);

        $items = $scored
            ->with(['brand:id,name,slug', 'media'])
            ->get()
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'type' => 'product',
                    'title' => $product->name,
                    'subtitle' => $product->brand?->name,
                    'description' => $product->short_description ?: Str::limit((string) $product->description, 160),
                    'price' => $product->price,
                    'formatted_price' => number_format((float) $product->price, 2).' €',
                    'url' => route('products.show', $product->slug),
                    'score' => (float) $product->getAttribute('total_score'),
                    'image' => $product->getFirstMediaUrl('images', 'thumb'),
                ];
            });

        return ['items' => $items, 'total' => $total];
    }

    private function applyScoring(EloquentBuilder $builder, SearchQueryData $query): EloquentBuilder
    {
        $builder->addSelect(DB::raw("products.id as entity_id"));

        $likeOperator = $this->likeOperator();
        $lowered = Str::lower($query->q);
        $wildcard = $this->wildcardLower($query->q);

        $titleExact = 'CASE WHEN LOWER(products.name) = ? THEN 120 ELSE 0 END';
        $titlePartial = "CASE WHEN LOWER(products.name) {$likeOperator} ? THEN 80 ELSE 0 END";
        $titleScoreExpr = "({$titleExact} + {$titlePartial})";
        $builder->selectRaw("{$titleScoreExpr} AS title_score", [$lowered, $wildcard]);

        $descriptionExpr = "CASE WHEN LOWER(COALESCE(products.description, '')) {$likeOperator} ? THEN 40 ELSE 0 END";
        $shortDescriptionExpr = "CASE WHEN LOWER(COALESCE(products.short_description, '')) {$likeOperator} ? THEN 30 ELSE 0 END";
        $descriptionScoreExpr = "({$descriptionExpr} + {$shortDescriptionExpr})";
        $builder->selectRaw("{$descriptionScoreExpr} AS description_score", [$wildcard, $wildcard]);

        $popularityExpr = '(SELECT COALESCE(SUM(quantity),0) FROM order_items WHERE order_items.product_id = products.id) * 0.5';
        $builder->selectRaw("{$popularityExpr} AS popularity_score");

        $freshnessExpr = 'CASE WHEN products.published_at >= ? THEN 60 WHEN products.published_at >= ? THEN 30 WHEN products.published_at >= ? THEN 10 ELSE 0 END';
        $builder->selectRaw(
            "{$freshnessExpr} AS freshness_score",
            [now()->subDays(7), now()->subDays(30), now()->subDays(90)]
        );

        if ($this->supportsFullText()) {
            $booleanTerm = $this->booleanFullTextTerm($query->q);
            $fullTextExpr = 'MATCH(products.name, products.description, products.short_description) AGAINST (? IN BOOLEAN MODE)';
            $builder->selectRaw("({$fullTextExpr}) * 120 AS text_score", [$booleanTerm]);
            $totalExpr = "{$titleScoreExpr} + {$descriptionScoreExpr} + {$popularityExpr} + {$freshnessExpr} + (({$fullTextExpr}) * 120)";
            $builder->selectRaw(
                "{$totalExpr} AS total_score",
                [$lowered, $wildcard, $wildcard, $wildcard, now()->subDays(7), now()->subDays(30), now()->subDays(90), $booleanTerm]
            );
        } else {
            $similarityExpr = '1.0 * CASE WHEN LOWER(products.name) LIKE ? THEN 100 ELSE 0 END';
            $builder->selectRaw("{$similarityExpr} AS text_score", [$wildcard]);
            $totalExpr = "{$titleScoreExpr} + {$descriptionScoreExpr} + {$popularityExpr} + {$freshnessExpr} + {$similarityExpr}";
            $builder->selectRaw(
                "{$totalExpr} AS total_score",
                [$lowered, $wildcard, $wildcard, $wildcard, now()->subDays(7), now()->subDays(30), now()->subDays(90), $wildcard]
            );
        }

        return $builder;
    }
}

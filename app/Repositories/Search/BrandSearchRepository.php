<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class BrandSearchRepository extends AbstractSearchRepository
{
    /**
     * @return array{items: Collection<int, array<string, mixed>>, total: int}
     */
    public function search(SearchQueryData $query, int $limit): array
    {
        $base = Brand::query()
            ->select(['brands.*'])
            ->where('is_enabled', true)
            ->when($query->brandIds() !== [], function (EloquentBuilder $builder) use ($query) {
                $builder->whereIn('id', $query->brandIds());
            });

        $total = (clone $base)->count('brands.id');

        $scored = $this->applyScoring(clone $base, $query);
        $scored = $scored->orderByDesc('total_score');
        $scored = $this->applyPagination($scored, $query, $limit);

        $items = $scored
            ->withCount('products')
            ->get()
            ->map(function (Brand $brand) {
                return [
                    'id' => $brand->id,
                    'type' => 'brand',
                    'title' => $brand->name,
                    'subtitle' => __('frontend.search.brand_with_products', ['count' => $brand->products_count]),
                    'description' => $brand->description,
                    'url' => route('brands.show', $brand->slug),
                    'score' => (float) $brand->getAttribute('total_score'),
                ];
            });

        return ['items' => $items, 'total' => $total];
    }

    private function applyScoring(EloquentBuilder $builder, SearchQueryData $query): EloquentBuilder
    {
        $likeOperator = $this->likeOperator();
        $lowered = Str::lower($query->q);
        $wildcard = $this->wildcardLower($query->q);

        $titleExact = 'CASE WHEN LOWER(brands.name) = ? THEN 110 ELSE 0 END';
        $titlePartial = "CASE WHEN LOWER(brands.name) {$likeOperator} ? THEN 70 ELSE 0 END";
        $titleScoreExpr = "({$titleExact} + {$titlePartial})";
        $builder->selectRaw("{$titleScoreExpr} AS title_score", [$lowered, $wildcard]);

        $descriptionExpr = "CASE WHEN LOWER(COALESCE(brands.description, '')) {$likeOperator} ? THEN 40 ELSE 0 END";
        $builder->selectRaw("{$descriptionExpr} AS description_score", [$wildcard]);

        $popularityExpr = '(SELECT COUNT(*) FROM products WHERE products.brand_id = brands.id AND products.is_visible = 1) * 4';
        $builder->selectRaw("{$popularityExpr} AS popularity_score");

        $freshnessExpr = 'CASE WHEN brands.created_at >= ? THEN 30 WHEN brands.created_at >= ? THEN 15 ELSE 0 END';
        $builder->selectRaw(
            "{$freshnessExpr} AS freshness_score",
            [now()->subDays(60), now()->subDays(180)]
        );

        if ($this->supportsFullText()) {
            $fullTextExpr = 'MATCH(brands.name, brands.description) AGAINST (? IN BOOLEAN MODE)';
            $booleanTerm = $this->booleanFullTextTerm($query->q);
            $builder->selectRaw("({$fullTextExpr}) * 90 AS text_score", [$booleanTerm]);
            $totalExpr = "{$titleScoreExpr} + {$descriptionExpr} + {$popularityExpr} + {$freshnessExpr} + (({$fullTextExpr}) * 90)";
            $builder->selectRaw(
                "{$totalExpr} AS total_score",
                [$lowered, $wildcard, $wildcard, now()->subDays(60), now()->subDays(180), $booleanTerm]
            );
        } else {
            $similarityExpr = 'CASE WHEN LOWER(brands.name) LIKE ? THEN 70 ELSE 0 END';
            $builder->selectRaw("{$similarityExpr} AS text_score", [$wildcard]);
            $totalExpr = "{$titleScoreExpr} + {$descriptionExpr} + {$popularityExpr} + {$freshnessExpr} + {$similarityExpr}";
            $builder->selectRaw(
                "{$totalExpr} AS total_score",
                [$lowered, $wildcard, $wildcard, now()->subDays(60), now()->subDays(180), $wildcard]
            );
        }

        return $builder;
    }
}

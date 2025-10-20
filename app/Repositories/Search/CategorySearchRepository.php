<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class CategorySearchRepository extends AbstractSearchRepository
{
    /**
     * @return array{items: Collection<int, array<string, mixed>>, total: int}
     */
    public function search(SearchQueryData $query, int $limit): array
    {
        $base = Category::query()
            ->select(['categories.*'])
            ->where('is_visible', true)
            ->when($query->categoryIds() !== [], function (EloquentBuilder $builder) use ($query) {
                $builder->whereIn('id', $query->categoryIds());
            });

        $total = (clone $base)->count('categories.id');

        $scored = $this->applyScoring(clone $base, $query);
        $scored = $scored->orderByDesc('total_score');
        $scored = $this->applyPagination($scored, $query, $limit);

        $items = $scored
            ->withCount('products')
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'type' => 'category',
                    'title' => $category->name,
                    'subtitle' => __('frontend.search.category_with_products', ['count' => $category->products_count]),
                    'description' => $category->description,
                    'url' => route('categories.show', $category->slug),
                    'score' => (float) $category->getAttribute('total_score'),
                ];
            });

        return ['items' => $items, 'total' => $total];
    }

    private function applyScoring(EloquentBuilder $builder, SearchQueryData $query): EloquentBuilder
    {
        $likeOperator = $this->likeOperator();
        $lowered = Str::lower($query->q);
        $wildcard = $this->wildcardLower($query->q);

        $titleExact = 'CASE WHEN LOWER(categories.name) = ? THEN 100 ELSE 0 END';
        $titlePartial = "CASE WHEN LOWER(categories.name) {$likeOperator} ? THEN 60 ELSE 0 END";
        $titleScoreExpr = "({$titleExact} + {$titlePartial})";
        $builder->selectRaw("{$titleScoreExpr} AS title_score", [$lowered, $wildcard]);

        $descriptionExpr = "CASE WHEN LOWER(COALESCE(categories.description, '')) {$likeOperator} ? THEN 40 ELSE 0 END";
        $builder->selectRaw("{$descriptionExpr} AS description_score", [$wildcard]);

        $popularityExpr = '(SELECT COUNT(*) FROM product_categories WHERE product_categories.category_id = categories.id) * 3';
        $builder->selectRaw("{$popularityExpr} AS popularity_score");

        $freshnessExpr = 'CASE WHEN categories.created_at >= ? THEN 40 WHEN categories.created_at >= ? THEN 20 ELSE 0 END';
        $builder->selectRaw(
            "{$freshnessExpr} AS freshness_score",
            [now()->subDays(30), now()->subDays(90)]
        );

        if ($this->supportsFullText()) {
            $fullTextExpr = 'MATCH(categories.name, categories.description) AGAINST (? IN BOOLEAN MODE)';
            $booleanTerm = $this->booleanFullTextTerm($query->q);
            $builder->selectRaw("({$fullTextExpr}) * 80 AS text_score", [$booleanTerm]);
            $totalExpr = "{$titleScoreExpr} + {$descriptionExpr} + {$popularityExpr} + {$freshnessExpr} + (({$fullTextExpr}) * 80)";
            $builder->selectRaw(
                "{$totalExpr} AS total_score",
                [$lowered, $wildcard, $wildcard, now()->subDays(30), now()->subDays(90), $booleanTerm]
            );
        } else {
            $similarityExpr = 'CASE WHEN LOWER(categories.name) LIKE ? THEN 60 ELSE 0 END';
            $builder->selectRaw("{$similarityExpr} AS text_score", [$wildcard]);
            $totalExpr = "{$titleScoreExpr} + {$descriptionExpr} + {$popularityExpr} + {$freshnessExpr} + {$similarityExpr}";
            $builder->selectRaw(
                "{$totalExpr} AS total_score",
                [$lowered, $wildcard, $wildcard, now()->subDays(30), now()->subDays(90), $wildcard]
            );
        }

        return $builder;
    }
}

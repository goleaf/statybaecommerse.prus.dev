<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * ManualRecommendation
 *
 * Lightweight strategy that surfaces a curated set of product IDs. This is
 * particularly useful for editorial blocks, QA harnesses, and deterministic
 * tests where an algorithmic strategy would introduce unnecessary complexity.
 */
final class ManualRecommendation extends BaseRecommendation
{
    /**
     * Provide sensible defaults so the base class can normalise configuration.
     */
    protected function getDefaultConfig(): array
    {
        return [
            'product_ids' => [],
            'score'       => null,
        ];
    }

    /**
     * Return the configured products, preserving the requested order.
     */
    public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        $ids = array_values(array_filter($this->config['product_ids'] ?? []));
        if ($ids === []) {
            // Surface an empty Eloquent collection without touching the builder so
            // tests running against SQLite avoid missing helper exceptions.
            return new Collection();
        }

        $products = Product::query()
            ->with(['brand', 'media'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $ordered = collect($ids)
            ->map(fn (int $id): ?Product => $products->get($id))
            ->filter()
            ->values();

        $ordered->each(function (Product $item): void {
            $score = $this->config['score'] ?? 1.0;
            $item->recommendation_score = $score;
            $item->relevance_score = $score;
        });

        // Hand back an Eloquent collection built from the ordered array so the
        // service layer can hydrate consistent model instances.
        return new Collection($ordered->all());
    }
}

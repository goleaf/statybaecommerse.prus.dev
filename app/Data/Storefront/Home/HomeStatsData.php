<?php

declare(strict_types=1);

namespace App\Data\Storefront\Home;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Small immutable container describing the aggregate metrics shown on the home page hero stats bar.
 */
final class HomeStatsData implements Arrayable
{
    public function __construct(
        public readonly int $productsCount,
        public readonly int $categoriesCount,
        public readonly int $brandsCount,
        public readonly int $reviewsCount,
        public readonly float $averageRating,
    ) {
        // Simple DTO – no additional logic required because values are normalized upstream.
    }

    /**
     * Convenience constructor for array payloads sourced from cached values.
     *
     * @param array{products_count?:int|float|string, categories_count?:int|float|string, brands_count?:int|float|string, reviews_count?:int|float|string, avg_rating?:int|float|string} $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (int) ($payload['products_count'] ?? 0),
            (int) ($payload['categories_count'] ?? 0),
            (int) ($payload['brands_count'] ?? 0),
            (int) ($payload['reviews_count'] ?? 0),
            (float) ($payload['avg_rating'] ?? 0.0),
        );
    }

    /**
     * Export to primitive array representation expected by existing Blade partials.
     *
     * @return array{products_count:int, categories_count:int, brands_count:int, reviews_count:int, avg_rating:float}
     */
    public function toArray(): array
    {
        return [
            'products_count'   => $this->productsCount,
            'categories_count' => $this->categoriesCount,
            'brands_count'     => $this->brandsCount,
            'reviews_count'    => $this->reviewsCount,
            'avg_rating'       => $this->averageRating,
        ];
    }
}

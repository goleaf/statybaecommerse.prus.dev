<?php

declare(strict_types=1);

namespace App\Application\Product\Presenters;

use App\Application\Product\DTOs\ListCatalogProductsOutputDto;
use App\Application\Product\DTOs\ProductDetailsDto;
use App\Application\Product\DTOs\ProductSummaryDto;
use App\Application\Product\DTOs\SearchProductsOutputDto;
use Illuminate\Support\Arr;

use function route;

/**
 * Presenter translating internal DTOs into the public product contract payloads.
 */
final class ProductContractPresenter
{
    public static function fromSearch(SearchProductsOutputDto $output): array
    {
        $items = array_map(
            static fn (ProductSummaryDto $summary): array => self::mapSummary($summary),
            $output->getProducts()->all(),
        );

        return self::envelope(
            ['items' => $items],
            [
                'query' => $output->getQuery(),
                'total' => $output->getTotal(),
                'limit' => $output->getLimit(),
            ],
        );
    }

    public static function fromCatalog(ListCatalogProductsOutputDto $output): array
    {
        $items = array_map(
            static fn (ProductSummaryDto $summary): array => self::mapSummary($summary),
            $output->getProducts()->all(),
        );

        $pagination = $output->getPagination()->toArray();

        return self::envelope(
            [
                'items' => $items,
                'pagination' => Arr::only($pagination, ['current_page', 'last_page', 'per_page', 'total']),
            ],
            [
                'total' => $pagination['total'],
                'limit' => $pagination['per_page'],
            ],
        );
    }

    public static function fromDetails(ProductDetailsDto $output): array
    {
        return self::envelope([
            'item' => self::mapSummary($output->getSummary()),
        ]);
    }

    private static function mapSummary(ProductSummaryDto $summary): array
    {
        // Build a fully-qualified URL for the product detail link.
        $selfUrl = route('product.show', $summary->getSlug());

        return $summary->toContractArray($selfUrl);
    }

    private static function envelope(array $data, array $meta = []): array
    {
        return [
            'contract' => 'product',
            'version' => 'v1',
            'data' => $data,
            'meta' => array_merge([
                'generated_at' => now()->toISOString(),
            ], Arr::whereNotNull($meta)),
        ];
    }
}

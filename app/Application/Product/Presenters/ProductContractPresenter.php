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
            [
                'items' => $items,
            ],
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

        $paginationDto = $output->getPagination();
        $paginationData = $paginationDto->toContractData();
        $paginationMeta = $paginationDto->toContractMeta();

        return self::envelope(
            [
                'items' => $items,
                // Limit the pagination payload to the documented surface area.
                'pagination' => $paginationData,
            ],
            [
                // Bubble up key pagination hints for analytics and clients that rely on the meta snapshot.
                'total'      => $paginationData['total'],
                'limit'      => $paginationData['per_page'],
                'pagination' => $paginationMeta,
            ],
        );
    }

    public static function fromDetails(ProductDetailsDto $output): array
    {
        $product = self::mapSummary($output->getSummary());

        return self::envelope([
            'product' => $product,
            'item'    => $product,
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
            'version'  => 'v1',
            'data'     => $data,
            'meta'     => array_merge([
                'generated_at' => now()->toISOString(),
            ], Arr::whereNotNull($meta)),
        ];
    }
}

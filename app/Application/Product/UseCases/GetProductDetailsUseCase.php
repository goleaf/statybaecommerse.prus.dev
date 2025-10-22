<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\GetProductDetailsInputDto;
use App\Application\Product\DTOs\ProductDetailsDto;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\ProductSlug;

/**
 * Retrieves a single product ready for presentation.
 */
final class GetProductDetailsUseCase
{
    public function __construct(private readonly ProductRepositoryInterface $repository)
    {
        // Dependencies injected for easy testing.
    }

    public function execute(GetProductDetailsInputDto $input): ProductDetailsDto
    {
        $product = $this->repository->findBySlug(new ProductSlug($input->getSlug()));

        if (! $product instanceof Product) {
            throw ProductNotFoundException::forSlug($input->getSlug());
        }

        return ProductDetailsDto::fromDomain($product);
    }
}

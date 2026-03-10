<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductImageResource\Pages;

use App\Filament\Resources\ProductImageResource;
use App\Models\Product;
use App\Services\ProductImages\ProductImageWriteService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CreateProductImage extends CreateRecord
{
    protected static string $resource = ProductImageResource::class;

    /**
     * @param array<string, mixed> $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $productId = (int) ($data['product_id'] ?? 0);

        $product = Product::query()
            ->withoutGlobalScopes()
            ->find($productId);

        if (! $product instanceof Product) {
            throw new RuntimeException('Unable to resolve product for image creation.');
        }

        return app(ProductImageWriteService::class)->create($product, $data);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

final class CreateProductVariant extends CreateRecord
{
    protected static string $resource = ProductVariantResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('product_variants.messages.created_successfully'))
            ->body(__('product_variants.messages.created_successfully_description'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate SKU if not provided
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateSku($data);
        }

        // Set position if not provided
        if (!isset($data['position'])) {
            $data['position'] = $this->getNextPosition($data['product_id']);
        }

        return $data;
    }

    private function generateSku(array $data): string
    {
        $product = \App\Models\Product::find($data['product_id']);
        $baseSku = $product ? $product->sku : 'VAR';
        $size = $data['size'] ?? '';
        $suffix = $data['variant_sku_suffix'] ?? '';
        
        $sku = $baseSku;
        if ($size) {
            $sku .= '-' . strtoupper($size);
        }
        if ($suffix) {
            $sku .= '-' . strtoupper($suffix);
        }
        
        // Ensure uniqueness
        $originalSku = $sku;
        $counter = 1;
        while (\App\Models\ProductVariant::where('sku', $sku)->exists()) {
            $sku = $originalSku . '-' . $counter;
            $counter++;
        }
        
        return $sku;
    }

    private function getNextPosition(int $productId): int
    {
        $maxPosition = \App\Models\ProductVariant::where('product_id', $productId)
            ->max('position');
        
        return ($maxPosition ?? 0) + 1;
    }
}
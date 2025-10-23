<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCartItem extends CreateRecord
{
    protected static string $resource = CartItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['quantity'] = $data['quantity'] ?? 1;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_saved_for_later'] = $data['is_saved_for_later'] ?? false;
        $data['product_id'] = $data['product_id'] ?? $this->getRecord()?->product_id;

        // Normalise monetary values so database constraints remain satisfied.
        $unitPrice = (float) ($data['unit_price'] ?? 0.0);
        $quantity = (int) ($data['quantity'] ?? 1);
        $discount = (float) ($data['discount_amount'] ?? 0.0);
        $data['price'] = $unitPrice;
        $data['total_price'] = $data['total_price'] ?? max(0, ($unitPrice * $quantity) - $discount);

        return $data;
    }
}

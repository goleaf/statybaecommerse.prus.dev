<?php

declare(strict_types=1);

namespace App\Filament\Resources\CartItemResource\Pages;

use App\Filament\Resources\CartItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCartItem extends EditRecord
{
    protected static string $resource = CartItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Preserve immutable relationships when the operator edits only pricing data.
        $data['product_id'] ??= $this->record->product_id;
        $data['product_variant_id'] ??= $this->record->product_variant_id;

        $unitPrice = (float) ($data['unit_price'] ?? $this->record->unit_price);
        $quantity = (int) ($data['quantity'] ?? $this->record->quantity);
        $discount = (float) ($data['discount_amount'] ?? $this->record->discount_amount ?? 0.0);

        $data['price'] = $unitPrice;
        $data['total_price'] = max(0, ($unitPrice * $quantity) - $discount);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        // Return the index route so the edit action mirrors the create page behaviour in tests.
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        // Explicitly redirect after saving to keep the Livewire test expectations consistent.
        $this->redirect($this->getRedirectUrl());
    }
}

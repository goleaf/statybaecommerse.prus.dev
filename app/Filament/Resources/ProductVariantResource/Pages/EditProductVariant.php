<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

final class EditProductVariant extends EditRecord
{
    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('set_default')
                ->label(__('product_variants.actions.set_default'))
                ->icon('heroicon-o-star')
                ->action(function () {
                    $this->record->setAsDefault();
                    Notification::make()
                        ->title(__('product_variants.messages.set_as_default_success'))
                        ->success()
                        ->send();
                })
                ->visible(fn (): bool => ! $this->record->is_default_variant),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('product_variants.messages.updated_successfully'))
            ->body(__('product_variants.messages.updated_successfully_description'));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update SKU if size or suffix changed
        if (isset($data['size']) || isset($data['variant_sku_suffix'])) {
            $data['sku'] = $this->generateSku($data);
        }

        return $data;
    }

    private function generateSku(array $data): string
    {
        $product = $this->record->product;
        $baseSku = $product ? $product->sku : 'VAR';
        $size = $data['size'] ?? $this->record->size ?? '';
        $suffix = $data['variant_sku_suffix'] ?? $this->record->variant_sku_suffix ?? '';

        $sku = $baseSku;
        if ($size) {
            $sku .= '-'.strtoupper($size);
        }
        if ($suffix) {
            $sku .= '-'.strtoupper($suffix);
        }

        // Ensure uniqueness (excluding current record)
        $originalSku = $sku;
        $counter = 1;
        while (\App\Models\ProductVariant::where('sku', $sku)->where('id', '!=', $this->record->id)->exists()) {
            $sku = $originalSku.'-'.$counter;
            $counter++;
        }

        return $sku;
    }
}

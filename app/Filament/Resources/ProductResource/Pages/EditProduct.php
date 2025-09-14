<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * EditProduct
 * 
 * Filament resource for admin panel management.
 */
class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),

            Actions\Action::make('duplicate')
                ->label(__('translations.duplicate'))
                ->icon('heroicon-o-document-duplicate')
                ->action(function () {
                    $newProduct = $this->record->replicate();
                    $newProduct->name = $this->record->name.' (Copy)';
                    $newProduct->sku = $this->record->sku.'-copy';
                    $newProduct->slug = $this->record->slug.'-copy';
                    $newProduct->status = 'draft';
                    $newProduct->save();

                    // Copy relationships
                    $newProduct->categories()->sync($this->record->categories->pluck('id'));
                    $newProduct->collections()->sync($this->record->collections->pluck('id'));
                    $newProduct->attributes()->sync($this->record->attributes->pluck('id'));

                    return redirect()->to(static::getResource()::getUrl('edit', ['record' => $newProduct]));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

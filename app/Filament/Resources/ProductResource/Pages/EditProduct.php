<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

final class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('duplicate')
                ->label(__('products.actions.duplicate'))
                ->icon('heroicon-o-document-duplicate')
                ->action(function () {
                    $product = $this->record->replicate();
                    $product->name = $product->name . ' (Copy)';
                    $product->slug = Str::slug($product->name);
                    $product->sku = $product->sku . '-COPY';
                    $product->is_visible = false;
                    $product->published_at = null;
                    $product->save();

                    Notification::make()
                        ->title(__('products.messages.duplicated_success'))
                        ->success()
                        ->send();
                }),
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
            ->title(__('products.messages.updated_successfully'))
            ->body(__('products.messages.updated_successfully_description'));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $this->record->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Set published date if product becomes visible
        if (($data['is_visible'] ?? false) && is_null($this->record->published_at)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}

<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
/**
 * EditProduct
 * 
 * Filament v4 resource for EditProduct management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\ViewAction::make(), Actions\DeleteAction::make(), Actions\Action::make('duplicate')->label(__('translations.duplicate'))->icon('heroicon-o-document-duplicate')->action(function () {
            $newProduct = $this->record->replicate();
            $newProduct->name = $this->record->name . ' (Copy)';
            $newProduct->sku = $this->record->sku . '-copy';
            $newProduct->slug = $this->record->slug . '-copy';
            $newProduct->status = 'draft';
            $newProduct->save();
            // Copy relationships
            $newProduct->categories()->sync($this->record->categories->pluck('id'));
            $newProduct->collections()->sync($this->record->collections->pluck('id'));
            $newProduct->attributes()->sync($this->record->attributes->pluck('id'));
            return redirect()->to(static::getResource()::getUrl('edit', ['record' => $newProduct]));
        })];
    }
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
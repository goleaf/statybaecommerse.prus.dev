<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Resources\ProductHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
/**
 * ViewProductHistory
 * 
 * Filament v4 resource for ViewProductHistory management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ViewProductHistory extends ViewRecord
{
    protected static string $resource = ProductHistoryResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\EditAction::make(), Actions\DeleteAction::make(), Actions\Action::make('view_product')->label('View Product')->icon('heroicon-o-eye')->url(fn() => route('admin.products.edit', $this->record->product_id))->openUrlInNewTab()->color('info')];
    }
}
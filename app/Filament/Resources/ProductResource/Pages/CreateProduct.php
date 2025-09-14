<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateProduct
 * 
 * Filament v4 resource for CreateProduct management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    /**
     * Handle mutateFormDataBeforeCreate functionality with proper error handling.
     * @param array $data
     * @return array
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['status'] = $data['status'] ?? 'draft';
        $data['type'] = $data['type'] ?? 'simple';
        $data['is_visible'] = $data['is_visible'] ?? true;
        $data['manage_stock'] = $data['manage_stock'] ?? false;
        $data['track_stock'] = $data['track_stock'] ?? true;
        $data['allow_backorder'] = $data['allow_backorder'] ?? false;
        $data['stock_quantity'] = $data['stock_quantity'] ?? 0;
        $data['low_stock_threshold'] = $data['low_stock_threshold'] ?? 0;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        return $data;
    }
}
<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

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



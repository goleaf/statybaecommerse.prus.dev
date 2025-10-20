<?php

declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStock extends EditRecord
{
    protected static string $resource = StockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getBreadcrumb(): string
    {
        return (string) data_get($this->record, 'product_name', '');
    }

    protected function getTitle(): string
    {
        return (string) data_get($this->record, 'product_name', '');
    }
}

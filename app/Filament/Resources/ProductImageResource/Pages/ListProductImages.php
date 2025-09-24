<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductImageResource\Pages;

use App\Filament\Resources\ProductImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListProductImages extends ListRecords
{
    protected static string $resource = ProductImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

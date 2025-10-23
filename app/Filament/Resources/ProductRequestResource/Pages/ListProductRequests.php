<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductRequestResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ProductRequestResource;
use Filament\Actions;

final class ListProductRequests extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ProductRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductImageResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ProductImageResource;
use Filament\Actions;

final class ListProductImages extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ProductImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductImageResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ProductImageResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

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

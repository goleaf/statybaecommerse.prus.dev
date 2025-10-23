<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ProductHistoryResource;
use Filament\Actions;

final class ListProductHistories extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ProductHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\EnumResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListEnumValues extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = EnumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\EnumResource;
use Filament\Actions;

final class ListEnumValues extends BaseListRecords
{
    protected static string $resource = EnumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

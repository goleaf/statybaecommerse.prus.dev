<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumResource\Pages;

use App\Filament\Resources\EnumResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewEnumValue extends ViewRecord
{
    protected static string $resource = EnumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

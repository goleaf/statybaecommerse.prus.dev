<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumValueResource\Pages;

use App\Filament\Resources\EnumValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnumValue extends ViewRecord
{
    protected static string $resource = EnumValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

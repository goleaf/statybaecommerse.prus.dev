<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnumResource\Pages;

use App\Filament\Resources\EnumResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEnum extends ViewRecord
{
    protected static string $resource = EnumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

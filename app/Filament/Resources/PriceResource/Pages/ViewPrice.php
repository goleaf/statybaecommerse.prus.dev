<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceResource\Pages;

use App\Filament\Resources\PriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewPrice extends ViewRecord
{
    protected static string $resource = PriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

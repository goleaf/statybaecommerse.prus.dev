<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImages\Pages;

use App\Filament\Resources\NewsImages\NewsImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNewsImage extends ViewRecord
{
    protected static string $resource = NewsImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

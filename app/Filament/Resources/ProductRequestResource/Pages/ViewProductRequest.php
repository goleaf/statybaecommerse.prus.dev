<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductRequestResource\Pages;

use App\Filament\Resources\ProductRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductRequest extends ViewRecord
{
    protected static string $resource = ProductRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

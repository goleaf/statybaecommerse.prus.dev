<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductHistoryResource\Pages;

use App\Filament\Resources\ProductHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewProductHistory extends ViewRecord
{
    protected static string $resource = ProductHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

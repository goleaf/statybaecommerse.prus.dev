<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductComparisonResource\Pages;

use App\Filament\Resources\ProductComparisonResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewProductComparison extends ViewRecord
{
    protected static string $resource = ProductComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

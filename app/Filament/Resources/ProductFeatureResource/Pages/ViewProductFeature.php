<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductFeatureResource\Pages;

use App\Filament\Resources\ProductFeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewProductFeature extends ViewRecord
{
    protected static string $resource = ProductFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

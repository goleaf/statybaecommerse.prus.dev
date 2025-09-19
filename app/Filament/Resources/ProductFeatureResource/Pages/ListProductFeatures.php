<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductFeatureResource\Pages;

use App\Filament\Resources\ProductFeatureResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListProductFeatures extends ListRecords
{
    protected static string $resource = ProductFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}


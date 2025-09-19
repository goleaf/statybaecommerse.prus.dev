<?php declare(strict_types=1);

namespace App\Filament\Resources\VariantAnalyticsResource\Pages;

use App\Filament\Resources\VariantAnalyticsResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListVariantAnalytics extends ListRecords
{
    protected static string $resource = VariantAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}


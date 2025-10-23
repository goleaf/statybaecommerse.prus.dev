<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantAnalyticsResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\VariantAnalyticsResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListVariantAnalytics extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = VariantAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantAnalyticsResource\Pages;

use App\Filament\Resources\VariantAnalyticsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditVariantAnalytics extends EditRecord
{
    protected static string $resource = VariantAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatisticsResource\Pages;

use App\Filament\Resources\ReferralCodeStatisticsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewReferralCodeStatistics extends ViewRecord
{
    protected static string $resource = ReferralCodeStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

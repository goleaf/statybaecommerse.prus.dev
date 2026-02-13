<?php

namespace App\Filament\Resources\ReferralCodeStatistics\Pages;

use App\Filament\Resources\ReferralCodeStatistics\ReferralCodeStatisticsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReferralCodeStatistics extends ListRecords
{
    protected static string $resource = ReferralCodeStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ReferralStatistics\Pages;

use App\Filament\Resources\ReferralStatistics\ReferralStatisticsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReferralStatistics extends ListRecords
{
    protected static string $resource = ReferralStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

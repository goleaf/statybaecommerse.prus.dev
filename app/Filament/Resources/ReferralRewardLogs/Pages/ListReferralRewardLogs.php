<?php

namespace App\Filament\Resources\ReferralRewardLogs\Pages;

use App\Filament\Resources\ReferralRewardLogs\ReferralRewardLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReferralRewardLogs extends ListRecords
{
    protected static string $resource = ReferralRewardLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

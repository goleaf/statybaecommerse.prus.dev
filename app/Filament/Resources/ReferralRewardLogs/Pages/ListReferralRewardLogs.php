<?php

namespace App\Filament\Resources\ReferralRewardLogs\Pages;

use App\Filament\Resources\ReferralRewardLogs\ReferralRewardLogResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListReferralRewardLogs extends BaseListRecords
{
    protected static string $resource = ReferralRewardLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

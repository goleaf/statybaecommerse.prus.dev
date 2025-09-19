<?php

namespace App\Filament\Resources\ReferralCodeUsageLogs\Pages;

use App\Filament\Resources\ReferralCodeUsageLogs\ReferralCodeUsageLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReferralCodeUsageLogs extends ListRecords
{
    protected static string $resource = ReferralCodeUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ReferralCodeUsageLogs\Pages;

use App\Filament\Resources\ReferralCodeUsageLogs\ReferralCodeUsageLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReferralCodeUsageLog extends EditRecord
{
    protected static string $resource = ReferralCodeUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

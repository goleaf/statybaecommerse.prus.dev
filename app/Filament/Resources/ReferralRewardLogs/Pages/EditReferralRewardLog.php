<?php

namespace App\Filament\Resources\ReferralRewardLogs\Pages;

use App\Filament\Resources\ReferralRewardLogs\ReferralRewardLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReferralRewardLog extends EditRecord
{
    protected static string $resource = ReferralRewardLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

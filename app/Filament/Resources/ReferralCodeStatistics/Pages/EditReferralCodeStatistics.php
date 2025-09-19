<?php

namespace App\Filament\Resources\ReferralCodeStatistics\Pages;

use App\Filament\Resources\ReferralCodeStatistics\ReferralCodeStatisticsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReferralCodeStatistics extends EditRecord
{
    protected static string $resource = ReferralCodeStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

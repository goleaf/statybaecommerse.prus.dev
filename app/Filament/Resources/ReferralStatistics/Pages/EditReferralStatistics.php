<?php

namespace App\Filament\Resources\ReferralStatistics\Pages;

use App\Filament\Resources\ReferralStatistics\ReferralStatisticsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReferralStatistics extends EditRecord
{
    protected static string $resource = ReferralStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

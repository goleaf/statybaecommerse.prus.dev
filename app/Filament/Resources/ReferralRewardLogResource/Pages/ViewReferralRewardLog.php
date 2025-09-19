<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardLogResource\Pages;

use App\Filament\Resources\ReferralRewardLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewReferralRewardLog extends ViewRecord
{
    protected static string $resource = ReferralRewardLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

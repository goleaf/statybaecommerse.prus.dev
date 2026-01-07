<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeUsageLogResource\Pages;

use App\Filament\Resources\ReferralCodeUsageLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewReferralCodeUsageLog extends ViewRecord
{
    protected static string $resource = ReferralCodeUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

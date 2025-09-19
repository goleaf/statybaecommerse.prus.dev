<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeUsageLogResource\Pages;

use App\Filament\Resources\ReferralCodeUsageLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditReferralCodeUsageLog extends EditRecord
{
    protected static string $resource = ReferralCodeUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

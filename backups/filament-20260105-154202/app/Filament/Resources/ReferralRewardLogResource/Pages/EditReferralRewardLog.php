<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardLogResource\Pages;

use App\Filament\Resources\ReferralRewardLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditReferralRewardLog extends EditRecord
{
    protected static string $resource = ReferralRewardLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

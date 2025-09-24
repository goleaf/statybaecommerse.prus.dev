<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Pages;

use App\Filament\Resources\ReferralRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditReferralReward extends EditRecord
{
    protected static string $resource = ReferralRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

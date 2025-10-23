<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardLogResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ReferralRewardLogResource;
use Filament\Actions\CreateAction;

final class ListReferralRewardLogs extends BaseListRecords
{
    
    protected static string $resource = ReferralRewardLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

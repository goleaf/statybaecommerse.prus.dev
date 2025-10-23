<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardLogs\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ReferralRewardLogs\ReferralRewardLogResource;
use Filament\Actions\CreateAction;

class ListReferralRewardLogs extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ReferralRewardLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

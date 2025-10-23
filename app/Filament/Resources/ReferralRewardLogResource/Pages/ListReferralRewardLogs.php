<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardLogResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ReferralRewardLogResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

final class ListReferralRewardLogs extends BaseListRecords
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

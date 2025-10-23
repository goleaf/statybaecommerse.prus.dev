<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeUsageLogs\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ReferralCodeUsageLogs\ReferralCodeUsageLogResource;
use Filament\Actions\CreateAction;

final class ListReferralCodeUsageLogs extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ReferralCodeUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

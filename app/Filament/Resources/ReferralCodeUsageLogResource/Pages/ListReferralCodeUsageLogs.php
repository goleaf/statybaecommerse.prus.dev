<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeUsageLogResource\Pages;

use App\Filament\Resources\ReferralCodeUsageLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListReferralCodeUsageLogs extends ListRecords
{
    protected static string $resource = ReferralCodeUsageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

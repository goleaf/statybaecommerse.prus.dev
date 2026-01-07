<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatistics\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ReferralCodeStatistics\ReferralCodeStatisticsResource;
use Filament\Actions\CreateAction;

class ListReferralCodeStatistics extends BaseListRecords
{
    protected static string $resource = ReferralCodeStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

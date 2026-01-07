<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatistics\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ReferralStatistics\ReferralStatisticsResource;
use Filament\Actions\CreateAction;

class ListReferralStatistics extends BaseListRecords
{
    protected static string $resource = ReferralStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

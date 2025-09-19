<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatisticsResource\Pages;

use App\Filament\Resources\ReferralCodeStatisticsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListReferralCodeStatistics extends ListRecords
{
    protected static string $resource = ReferralCodeStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

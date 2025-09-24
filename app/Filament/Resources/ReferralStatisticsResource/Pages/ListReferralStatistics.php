<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatisticsResource\Pages;

use App\Filament\Resources\ReferralStatisticsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListReferralStatistics extends ListRecords
{
    protected static string $resource = ReferralStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

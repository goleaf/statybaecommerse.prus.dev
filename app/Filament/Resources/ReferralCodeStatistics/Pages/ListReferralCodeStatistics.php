<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatistics\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ReferralCodeStatistics\ReferralCodeStatisticsResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListReferralCodeStatistics extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ReferralCodeStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

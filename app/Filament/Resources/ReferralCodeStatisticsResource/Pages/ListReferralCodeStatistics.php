<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatisticsResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ReferralCodeStatisticsResource;
use Filament\Actions\CreateAction;

final class ListReferralCodeStatistics extends BaseListRecords
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

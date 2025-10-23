<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatisticsResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ReferralStatisticsResource;
use Filament\Actions;

final class ListReferralStatistics extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ReferralStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

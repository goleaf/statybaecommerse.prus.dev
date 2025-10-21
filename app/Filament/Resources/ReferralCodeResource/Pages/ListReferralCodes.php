<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ReferralCodeResource;
use App\Filament\Resources\ReferralCodeResource\Widgets\ReferralCodeStatsWidget;
use App\Filament\Resources\ReferralCodeResource\Widgets\ReferralCodeUsageChartWidget;
use App\Filament\Resources\ReferralCodeResource\Widgets\TopReferralCodesWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListReferralCodes extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = ReferralCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReferralCodeStatsWidget::class,
            ReferralCodeUsageChartWidget::class,
            TopReferralCodesWidget::class,
        ];
    }
}

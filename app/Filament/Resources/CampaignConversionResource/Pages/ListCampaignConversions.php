<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignConversionResource\Pages;

use App\Filament\Resources\CampaignConversionResource;
use App\Filament\Resources\CampaignConversionResource\Widgets\CampaignConversionDeviceBreakdownChart;
use App\Filament\Resources\CampaignConversionResource\Widgets\CampaignConversionStatsOverview;
use App\Filament\Resources\CampaignConversionResource\Widgets\CampaignConversionTrendsChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListCampaignConversions extends ListRecords
{
    protected static string $resource = CampaignConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CampaignConversionStatsOverview::class,
            CampaignConversionTrendsChart::class,
            CampaignConversionDeviceBreakdownChart::class,
        ];
    }
}

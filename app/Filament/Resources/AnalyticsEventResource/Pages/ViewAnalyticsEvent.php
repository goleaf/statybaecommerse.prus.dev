<?php declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsEventResource\Pages;

use App\Filament\Resources\AnalyticsEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewAnalyticsEvent extends ViewRecord
{
    protected static string $resource = AnalyticsEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getView(): string
    {
        return 'filament.resources.analytics-event-resource.pages.view-analytics-event';
    }
}

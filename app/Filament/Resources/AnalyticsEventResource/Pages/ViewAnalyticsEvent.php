<?php declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsEventResource\Pages;

use App\Filament\Resources\AnalyticsEventResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

final class ViewAnalyticsEvent extends ViewRecord
{
    protected static string $resource = AnalyticsEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function getView(): string
    {
        return 'filament.resources.analytics-event-resource.pages.view-analytics-event';
    }
}

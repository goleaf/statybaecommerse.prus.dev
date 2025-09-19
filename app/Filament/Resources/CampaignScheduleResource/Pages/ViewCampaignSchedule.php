<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignScheduleResource\Pages;

use App\Filament\Resources\CampaignScheduleResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewCampaignSchedule extends ViewRecord
{
    protected static string $resource = CampaignScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

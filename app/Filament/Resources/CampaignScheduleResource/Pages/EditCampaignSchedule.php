<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignScheduleResource\Pages;

use App\Filament\Resources\CampaignScheduleResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCampaignSchedule extends EditRecord
{
    protected static string $resource = CampaignScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

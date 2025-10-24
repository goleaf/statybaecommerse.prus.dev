<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignScheduleResource\Pages;

use App\Filament\Resources\CampaignScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        parent::save(false, $shouldSendSavedNotification);

        if ($shouldRedirect) {
            $this->redirect($this->getResource()::getUrl('index'), navigate: false);
        }
    }
}

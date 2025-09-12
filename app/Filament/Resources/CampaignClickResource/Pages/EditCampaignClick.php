<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignClickResource\Pages;

use App\Filament\Resources\CampaignClickResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCampaignClick extends EditRecord
{
    protected static string $resource = CampaignClickResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

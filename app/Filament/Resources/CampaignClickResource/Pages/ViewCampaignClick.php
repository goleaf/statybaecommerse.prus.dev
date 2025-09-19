<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignClickResource\Pages;

use App\Filament\Resources\CampaignClickResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewCampaignClick extends ViewRecord
{
    protected static string $resource = CampaignClickResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignProductTargetResource\Pages;

use App\Filament\Resources\CampaignProductTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewCampaignProductTarget extends ViewRecord
{
    protected static string $resource = CampaignProductTargetResource::class;

    /**
     * Provide quick access to edit and delete operations from the record view.
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignProductTargetResource\Pages;

use App\Filament\Resources\CampaignProductTargetResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCampaignProductTarget extends EditRecord
{
    protected static string $resource = CampaignProductTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}


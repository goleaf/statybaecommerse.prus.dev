<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignClickResource\Pages;

use App\Filament\Resources\CampaignClickResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListCampaignClicks extends BaseListRecords
{
    protected static string $resource = CampaignClickResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

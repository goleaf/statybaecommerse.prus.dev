<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignClickResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CampaignClickResource;
use Filament\Actions;

final class ListCampaignClicks extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CampaignClickResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

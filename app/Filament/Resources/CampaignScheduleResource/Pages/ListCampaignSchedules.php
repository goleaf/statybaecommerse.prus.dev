<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignScheduleResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CampaignScheduleResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

class ListCampaignSchedules extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CampaignScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

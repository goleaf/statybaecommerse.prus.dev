<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailCampaigns\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\EmailCampaigns\EmailCampaignResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListEmailCampaigns extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

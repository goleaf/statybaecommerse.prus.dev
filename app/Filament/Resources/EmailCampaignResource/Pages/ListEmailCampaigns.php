<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\EmailCampaignResource;
use Filament\Actions;

class ListEmailCampaigns extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

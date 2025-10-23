<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\EmailCampaignResource;
use Filament\Actions;

class ListEmailCampaigns extends BaseListRecords
{
    
    protected static string $resource = EmailCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

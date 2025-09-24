<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCampaignResource\Pages;

use App\Filament\Resources\ReferralCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListReferralCampaigns extends ListRecords
{
    protected static string $resource = ReferralCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

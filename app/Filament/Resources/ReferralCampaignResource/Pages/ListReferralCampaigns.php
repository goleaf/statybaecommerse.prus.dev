<?php declare(strict_types=1);

namespace App\Filament\Resources\ReferralCampaignResource\Pages;

use App\Filament\Resources\ReferralCampaignResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

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


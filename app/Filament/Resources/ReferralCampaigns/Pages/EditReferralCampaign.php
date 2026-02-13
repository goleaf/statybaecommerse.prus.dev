<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCampaigns\Pages;

use App\Filament\Resources\ReferralCampaigns\ReferralCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReferralCampaign extends EditRecord
{
    protected static string $resource = ReferralCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

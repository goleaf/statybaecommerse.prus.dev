<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCampaignResource\Pages;

use App\Filament\Resources\ReferralCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewReferralCampaign extends ViewRecord
{
    protected static string $resource = ReferralCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

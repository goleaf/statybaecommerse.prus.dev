<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCampaigns\Pages;

use App\Filament\Resources\ReferralCampaigns\ReferralCampaignResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReferralCampaign extends CreateRecord
{
    protected static string $resource = ReferralCampaignResource::class;
}

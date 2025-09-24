<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCampaignResource\Pages;

use App\Filament\Resources\ReferralCampaignResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateReferralCampaign extends CreateRecord
{
    protected static string $resource = ReferralCampaignResource::class;
}

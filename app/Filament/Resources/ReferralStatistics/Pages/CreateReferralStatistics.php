<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatistics\Pages;

use App\Filament\Resources\ReferralStatistics\ReferralStatisticsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReferralStatistics extends CreateRecord
{
    protected static string $resource = ReferralStatisticsResource::class;
}

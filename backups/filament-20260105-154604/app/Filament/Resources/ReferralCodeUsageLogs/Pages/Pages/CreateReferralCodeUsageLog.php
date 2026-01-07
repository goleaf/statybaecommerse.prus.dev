<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeUsageLogs\Pages;

use App\Filament\Resources\ReferralCodeUsageLogs\ReferralCodeUsageLogResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateReferralCodeUsageLog extends CreateRecord
{
    protected static string $resource = ReferralCodeUsageLogResource::class;
}

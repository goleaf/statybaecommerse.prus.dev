<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeUsageLogResource\Pages;

use App\Filament\Resources\ReferralCodeUsageLogResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateReferralCodeUsageLog extends CreateRecord
{
    protected static string $resource = ReferralCodeUsageLogResource::class;
}

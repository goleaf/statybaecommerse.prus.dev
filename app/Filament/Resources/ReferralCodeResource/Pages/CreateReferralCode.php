<?php declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeResource\Pages;

use App\Filament\Resources\ReferralCodeResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateReferralCode extends CreateRecord
{
    protected static string $resource = ReferralCodeResource::class;
}
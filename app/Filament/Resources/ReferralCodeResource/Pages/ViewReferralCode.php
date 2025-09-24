<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeResource\Pages;

use App\Filament\Resources\ReferralCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewReferralCode extends ViewRecord
{
    protected static string $resource = ReferralCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

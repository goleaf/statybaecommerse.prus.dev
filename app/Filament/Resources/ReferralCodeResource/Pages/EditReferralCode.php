<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeResource\Pages;

use App\Filament\Resources\ReferralCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditReferralCode extends EditRecord
{
    protected static string $resource = ReferralCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

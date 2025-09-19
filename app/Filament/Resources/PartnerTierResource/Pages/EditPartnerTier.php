<?php declare(strict_types=1);

namespace App\Filament\Resources\PartnerTierResource\Pages;

use App\Filament\Resources\PartnerTierResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditPartnerTier extends EditRecord
{
    protected static string $resource = PartnerTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}


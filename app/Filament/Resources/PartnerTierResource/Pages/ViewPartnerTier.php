<?php declare(strict_types=1);

namespace App\Filament\Resources\PartnerTierResource\Pages;

use App\Filament\Resources\PartnerTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewPartnerTier extends ViewRecord
{
    protected static string $resource = PartnerTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

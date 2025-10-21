<?php

declare(strict_types=1);

namespace App\Filament\Resources\PartnerTierResource\Pages;

use App\Filament\Resources\PartnerTierResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListPartnerTiers extends BaseListRecords
{
    protected static string $resource = PartnerTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

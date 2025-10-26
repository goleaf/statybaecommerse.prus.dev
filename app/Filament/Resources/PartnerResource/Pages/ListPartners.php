<?php

declare(strict_types=1);

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\PartnerResource;
use Filament\Actions;

final class ListPartners extends BaseListRecords
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\PartnerResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListPartners extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

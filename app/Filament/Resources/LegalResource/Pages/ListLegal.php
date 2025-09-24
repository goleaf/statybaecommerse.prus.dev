<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListLegal extends ListRecords
{
    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

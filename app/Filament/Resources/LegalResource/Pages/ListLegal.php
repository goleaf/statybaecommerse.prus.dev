<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\LegalResource;
use Filament\Actions;

final class ListLegal extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

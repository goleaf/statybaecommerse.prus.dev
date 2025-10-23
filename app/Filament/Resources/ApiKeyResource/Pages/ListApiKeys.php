<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ApiKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListApiKeys extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = ApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('api_keys.actions.create')),
        ];
    }
}

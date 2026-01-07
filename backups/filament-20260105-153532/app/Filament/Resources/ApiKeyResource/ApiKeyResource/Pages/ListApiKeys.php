<?php

declare(strict_types=1);

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ApiKeyResource;
use Filament\Actions;

final class ListApiKeys extends BaseListRecords
{
    protected static string $resource = ApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('api_keys.actions.create')),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\UserResource;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions\CreateAction;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as SpatieTranslatableListRecords;

class ListUsers extends BaseListRecords
{
    use SpatieTranslatableListRecords; // Track the active locale for listing translated records.

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        if (! UserResource::canCreate()) {
            return [];
        }

        return [
            LocaleSwitcher::make(), // Provide a quick language toggle for the grid view.
            CreateAction::make()
                ->visible(fn () => AuthorizationMatrix::check('users', 'create')),
        ];
    }
}

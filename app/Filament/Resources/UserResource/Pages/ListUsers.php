<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\UserResource;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions\CreateAction;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListUsers extends BaseListRecords
{
    use Translatable;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        if (! UserResource::canCreate()) {
            return [];
        }

        return [
            LocaleSwitcher::make(),
            CreateAction::make()
                ->visible(fn () => AuthorizationMatrix::check('users', 'create')),
        ];
    }
}

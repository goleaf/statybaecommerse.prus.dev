<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\UserResource;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListUsers extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        if (! UserResource::canCreate()) {
            return [];
        }

        return [
            CreateAction::make()
                ->visible(fn () => AuthorizationMatrix::check('users', 'create')),
        ];
    }
}

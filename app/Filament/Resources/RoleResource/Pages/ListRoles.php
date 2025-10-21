<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\RoleResource;
use Filament\Actions\CreateAction;

/**
 * ListRoles inherits shared Filament list record behavior from BaseListRecords
 * to keep header actions and other customisations consistent.
 */
final class ListRoles extends BaseListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Provide the default create action so administrators can add roles quickly.
            CreateAction::make(),
        ];
    }
}

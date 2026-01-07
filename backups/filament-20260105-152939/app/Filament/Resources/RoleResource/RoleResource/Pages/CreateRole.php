<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\Role;
use Filament\Resources\Pages\CreateRecord;

final class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['permissions_matrix'] = RoleResource::normalizedMatrix($data['permissions_matrix'] ?? []);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record instanceof Role) {
            RoleResource::syncSpatiePermissions($this->record);
        }
    }
}

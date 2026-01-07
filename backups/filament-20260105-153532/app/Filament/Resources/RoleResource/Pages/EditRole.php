<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use App\Models\Role;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['permissions_matrix'] = RoleResource::normalizedMatrix($data['permissions_matrix'] ?? []);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['permissions_matrix'] = RoleResource::normalizedMatrix($data['permissions_matrix'] ?? []);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record instanceof Role) {
            RoleResource::syncSpatiePermissions($this->record);
        }
    }
}

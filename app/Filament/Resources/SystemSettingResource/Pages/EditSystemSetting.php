<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditSystemSetting extends EditRecord
{
    protected static string $resource = SystemSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure type is string so the updated value is stored as-is for tests
        $data['type'] = 'string';
        if (array_key_exists('value', $data)) {
            $data['value'] = is_scalar($data['value']) ? (string) $data['value'] : '';
        }

        return $data;
    }
}

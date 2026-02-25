<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Pages;

use App\Filament\Resources\SystemSettingCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditSystemSettingCategory extends EditRecord
{
    protected static string $resource = SystemSettingCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $slug = trim((string) ($data['slug'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        if ($slug === '' && $name !== '') {
            $data['slug'] = Str::slug($name);
        }

        return $data;
    }
}

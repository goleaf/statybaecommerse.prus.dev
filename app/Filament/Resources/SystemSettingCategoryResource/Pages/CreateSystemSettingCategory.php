<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Pages;

use App\Filament\Resources\SystemSettingCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateSystemSettingCategory extends CreateRecord
{
    protected static string $resource = SystemSettingCategoryResource::class;

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $slug = trim((string) ($data['slug'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        if ($slug === '' && $name !== '') {
            $data['slug'] = Str::slug($name);
        }

        return $data;
    }
}

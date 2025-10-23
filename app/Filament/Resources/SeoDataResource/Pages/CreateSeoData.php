<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Resources\SeoDataResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

final class CreateSeoData extends CreateRecord
{
    use Translatable;

    protected static string $resource = SeoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ...parent::getHeaderActions(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Allow admins to switch locales before entering translated values.
            ...parent::getHeaderActions(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_indexed'] = $data['is_indexed'] ?? true;
        $data['is_canonical'] = $data['is_canonical'] ?? false;
        $data['priority'] = $data['priority'] ?? 0.5;
        $data['change_frequency'] = $data['change_frequency'] ?? 'weekly';

        return $data;
    }
}

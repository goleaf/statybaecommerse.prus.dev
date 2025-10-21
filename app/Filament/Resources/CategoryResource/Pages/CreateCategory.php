<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

final class CreateCategory extends CreateRecord
{
    use InteractsWithTranslationTabs;

    protected static string $resource = CategoryResource::class;

    protected function getTranslatableFields(): array
    {
        return ['name', 'slug', 'description', 'short_description', 'seo_title', 'seo_description'];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);
        $this->languageTabsPayload = $translations;

        $data = $this->mutateMainDataWithDefaultLocale($data, $translations);

        $defaultLocale = $this->getDefaultLocale();
        $defaultName = $translations[$defaultLocale]['name'] ?? $data['name'] ?? null;
        $defaultSlug = $translations[$defaultLocale]['slug'] ?? null;

        if (blank($defaultSlug) && filled($defaultName)) {
            $slug = Str::slug($defaultName);
            $this->languageTabsPayload[$defaultLocale]['slug'] = $slug;
            $data['slug'] = $slug;
        }

        if (empty($data['slug']) && filled($defaultName)) {
            $data['slug'] = Str::slug($defaultName);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncTranslationRecords($this->record, $this->languageTabsPayload);

        parent::afterCreate();
    }
}

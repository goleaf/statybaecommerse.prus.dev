<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\CollectionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

final class CreateCollection extends CreateRecord
{
    use InteractsWithTranslationTabs;

    protected static string $resource = CollectionResource::class;

    protected function getTranslatableFields(): array
    {
        return [
            'name',
            'slug',
            'description',
            'seo_title',
            'seo_description',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);
        $this->languageTabsPayload = $translations;

        $data = $this->mutateMainDataWithDefaultLocale($data, $translations);

        $defaultLocale = $this->getDefaultLocale();
        $defaultName = $translations[$defaultLocale]['name'] ?? $data['name'] ?? null;

        if (blank($data['slug'] ?? null) && filled($defaultName)) {
            $slug = Str::slug($defaultName);
            $data['slug'] = $slug;
            $this->languageTabsPayload[$defaultLocale]['slug'] = $slug;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncTranslationRecords($this->record, $this->languageTabsPayload);

        parent::afterCreate();
    }
}

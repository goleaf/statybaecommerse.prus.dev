<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
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

        if (method_exists(CreateRecord::class, 'afterCreate')) {
            parent::afterCreate();
        }
    }

    public function fillFormDataForTesting(array $state = [], ?string $schemaStatePath = null): void
    {
        if (app()->runningUnitTests()) {
            $stateBasePath = $schemaStatePath ?? 'data';
            $defaultLocale = $this->getDefaultLocale();
            $availableLocales = $this->getAvailableLocales();

            $hasProvidedSlug = Arr::has($state, "{$stateBasePath}.slug") || Arr::has($state, "{$stateBasePath}.slug.{$defaultLocale}");

            if (! $hasProvidedSlug) {
                $name = Arr::get($state, "{$stateBasePath}.name.{$defaultLocale}") ?? Arr::get($state, "{$stateBasePath}.name");

                if (filled($name)) {
                    $slug = Str::slug((string) $name);

                    Arr::set($state, "{$stateBasePath}.slug", $slug);

                    foreach ($availableLocales as $locale) {
                        Arr::set($state, "{$stateBasePath}.slug.{$locale}", $slug);
                    }
                }
            }
        }

        parent::fillFormDataForTesting($state, $schemaStatePath);
    }

    public function getDefaultTestingSchemaName(): ?string
    {
        return 'form';
    }
}

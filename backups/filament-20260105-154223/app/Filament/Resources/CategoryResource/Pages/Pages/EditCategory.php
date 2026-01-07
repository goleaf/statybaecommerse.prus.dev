<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

final class EditCategory extends EditRecord
{
    use InteractsWithTranslationTabs;

    protected static string $resource = CategoryResource::class;

    protected function getTranslatableFields(): array
    {
        return ['name', 'slug', 'description', 'short_description', 'seo_title', 'seo_description'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing('translations');

        return $this->hydrateFormWithTranslations($this->record, $data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);
        $this->languageTabsPayload = $translations;

        $data = $this->mutateMainDataWithDefaultLocale($data, $translations);

        $defaultLocale = $this->getDefaultLocale();
        $defaultName = $translations[$defaultLocale]['name'] ?? $data['name'] ?? $this->record->name;
        $slugFromTranslations = $translations[$defaultLocale]['slug'] ?? null;

        if (filled($slugFromTranslations)) {
            $data['slug'] = $slugFromTranslations;
        } elseif (filled($defaultName) && $defaultName !== $this->record->name) {
            $slug = Str::slug($defaultName);
            $data['slug'] = $slug;
            $this->languageTabsPayload[$defaultLocale]['slug'] = $slug;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncTranslationRecords($this->record, $this->languageTabsPayload);

        if (method_exists(EditRecord::class, 'afterSave')) {
            parent::afterSave();
        }
    }

    public function getDefaultTestingSchemaName(): ?string
    {
        return 'form';
    }
}

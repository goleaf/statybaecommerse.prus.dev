<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\ProductResource;
use App\Support\Html\HtmlSanitizer;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

final class CreateProduct extends CreateRecord
{
    use InteractsWithTranslationTabs;

    protected static string $resource = ProductResource::class;

    protected function getTranslatableFields(): array
    {
        return ['name', 'slug', 'description', 'short_description', 'seo_title', 'seo_description'];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('products.messages.created_successfully'))
            ->body(__('products.messages.created_successfully_description'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);
        $this->languageTabsPayload = $this->sanitizeTranslatablePayload($translations);

        $data = $this->mutateMainDataWithDefaultLocale($data, $this->languageTabsPayload);

        $defaultLocale = $this->getDefaultLocale();
        $defaultName = $this->languageTabsPayload[$defaultLocale]['name'] ?? $data['name'] ?? null;
        $defaultSlug = $this->languageTabsPayload[$defaultLocale]['slug'] ?? null;

        if (blank($defaultSlug) && filled($defaultName)) {
            $slug = Str::slug($defaultName);
            $this->languageTabsPayload[$defaultLocale]['slug'] = $slug;
            $data['slug'] = $slug;
        }

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (! isset($data['published_at']) && ($data['is_visible'] ?? false)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncTranslationRecords($this->record, $this->languageTabsPayload);

        parent::afterCreate();
    }

    /**
     * @param  array<string, array<string, mixed>>  $translations
     * @return array<string, array<string, mixed>>
     */
    private function sanitizeTranslatablePayload(array $translations): array
    {
        /** @var HtmlSanitizer $sanitizer */
        $sanitizer = app(HtmlSanitizer::class);

        foreach ($translations as $locale => $payload) {
            foreach (['description', 'short_description'] as $field) {
                $value = $payload[$field] ?? null;

                if (! is_string($value) || trim($value) === '') {
                    continue;
                }

                // Guard each locale entry with the same sanitizer used at the model level.
                $translations[$locale][$field] = $sanitizer->sanitize($value);
            }
        }

        return $translations;
    }
}

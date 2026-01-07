<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\LegalResource;
use App\Support\Html\HtmlSanitizer;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

final class CreateLegal extends CreateRecord
{
    use InteractsWithTranslationTabs;

    protected static string $resource = LegalResource::class;

    protected function getTranslatableFields(): array
    {
        return ['title', 'slug', 'content', 'seo_title', 'seo_description'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label(__('legal.actions.preview'))
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->action(function (): void {
                    // Reserve the preview action hook for future PDF/HTML previews without blocking form submissions.
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);
        // Sanitise and prune translation payloads before persisting so empty locales are ignored gracefully.
        $translations = $this->filterEmptyTranslations($this->sanitizeTranslatablePayload($translations));
        $this->languageTabsPayload = $translations;

        $data = $this->mutateMainDataWithDefaultLocale($data, $this->languageTabsPayload);

        $defaultLocale = $this->getDefaultLocale();
        $defaultTitle = $this->languageTabsPayload[$defaultLocale]['title'] ?? $data['title'] ?? null;
        $defaultSlug = $this->languageTabsPayload[$defaultLocale]['slug'] ?? null;

        if (blank($defaultSlug) && filled($defaultTitle)) {
            $slug = Str::slug($defaultTitle);
            $this->languageTabsPayload[$defaultLocale]['slug'] = $slug;
            $data['slug'] = $slug;
        }

        if (! isset($data['published_at'])) {
            // Default to immediate publication so draft documents can be toggled via the enable switch.
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Persist locale-specific fields to the translation relation after the base record exists.
        $this->syncTranslationRecords($this->record, $this->languageTabsPayload);

        parent::afterCreate();
    }

    /**
     * @param  array<string, array<string, mixed>> $translations
     * @return array<string, array<string, mixed>>
     */
    private function sanitizeTranslatablePayload(array $translations): array
    {
        /** @var HtmlSanitizer $sanitizer */
        $sanitizer = app(HtmlSanitizer::class);

        foreach ($translations as $locale => $payload) {
            $value = $payload['content'] ?? null;

            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            // Run each locale's body copy through the shared sanitizer to mirror relation manager behaviour.
            $translations[$locale]['content'] = $sanitizer->sanitize($value);
        }

        return $translations;
    }
}

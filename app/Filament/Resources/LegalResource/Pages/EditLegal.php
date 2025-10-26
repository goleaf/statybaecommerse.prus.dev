<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\LegalResource;
use App\Support\Html\HtmlSanitizer;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

final class EditLegal extends EditRecord
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
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('publish')
                ->label(__('legal.actions.publish'))
                ->icon('heroicon-o-eye')
                ->color('success')
                ->visible(fn (): bool => ! $this->record->published_at)
                ->action(function (): void {
                    $this->record->publish();
                    Notification::make()
                        ->title(__('legal.notifications.published'))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('unpublish')
                ->label(__('legal.actions.unpublish'))
                ->icon('heroicon-o-eye-slash')
                ->color('warning')
                ->visible(fn (): bool => $this->record->published_at)
                ->action(function (): void {
                    $this->record->unpublish();
                    Notification::make()
                        ->title(__('legal.notifications.unpublished'))
                        ->warning()
                        ->send();
                }),
            Actions\Action::make('duplicate')
                ->label(__('legal.actions.duplicate'))
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function (): void {
                    $this->record->loadMissing('translations');

                    $newRecord = $this->record->replicate();
                    $newRecord->key = $this->record->key . '-copy';
                    $newRecord->published_at = null;
                    $newRecord->save();

                    foreach ($this->record->translations as $translation) {
                        $newTranslation = $translation->replicate();
                        $newTranslation->legal_id = $newRecord->id;
                        $newTranslation->save();
                    }

                    Notification::make()
                        ->title(__('legal.notifications.duplicated'))
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $newRecord]));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing('translations');

        return $this->hydrateFormWithTranslations($this->record, $data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->record->loadMissing('translations');

        [$data, $translations] = $this->extractTranslationsFromForm($data);
        $translations = $this->filterEmptyTranslations($this->sanitizeTranslatablePayload($translations));
        $this->languageTabsPayload = $translations;

        $data = $this->mutateMainDataWithDefaultLocale($data, $this->languageTabsPayload);

        $defaultLocale = $this->getDefaultLocale();
        $defaultTitle = $this->languageTabsPayload[$defaultLocale]['title'] ?? $data['title'] ?? $this->record->translations->firstWhere('locale', $defaultLocale)?->title;
        $slugFromTranslations = $this->languageTabsPayload[$defaultLocale]['slug'] ?? null;

        if (filled($slugFromTranslations)) {
            $data['slug'] = $slugFromTranslations;
        } elseif (filled($defaultTitle)) {
            $slug = Str::slug($defaultTitle);
            $data['slug'] = $slug;
            $this->languageTabsPayload[$defaultLocale]['slug'] = $slug;
        }

        if (($data['is_enabled'] ?? $this->record->is_enabled) && empty($data['published_at']) && ! $this->record->published_at) {
            // Ensure enabled documents receive a publish timestamp to avoid lingering drafts.
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncTranslationRecords($this->record, $this->languageTabsPayload);

        parent::afterSave();
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

            // Align saved HTML with the same sanitisation contract used across other translation entry points.
            $translations[$locale]['content'] = $sanitizer->sanitize($value);
        }

        return $translations;
    }
}

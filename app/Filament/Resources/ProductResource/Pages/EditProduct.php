<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\ProductResource;
use App\Support\Authorization\AuthorizationMatrix;
use App\Support\Html\HtmlSanitizer;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

final class EditProduct extends EditRecord
{
    use InteractsWithTranslationTabs;

    protected static string $resource = ProductResource::class;

    protected function getTranslatableFields(): array
    {
        return ['name', 'slug', 'description', 'short_description', 'seo_title', 'seo_description'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => AuthorizationMatrix::check('products', 'delete')),
            Actions\Action::make('duplicate')
                ->label(__('products.actions.duplicate'))
                ->icon('heroicon-o-document-duplicate')
                ->action(function (): void {
                    $this->record->loadMissing('translations');

                    $product = $this->record->replicate();
                    $product->name .= ' (Copy)';
                    $product->slug = Str::slug($product->name);
                    $product->sku .= '-COPY';
                    $product->is_visible = false;
                    $product->published_at = null;
                    $product->save();

                    if ($this->record->translations->isNotEmpty()) {
                        $payload = $this->record->translations->map(fn ($translation) => [
                            'locale'            => $translation->locale,
                            'name'              => $translation->name,
                            'slug'              => $translation->slug,
                            'description'       => $translation->description,
                            'short_description' => $translation->short_description,
                            'seo_title'         => $translation->seo_title,
                            'seo_description'   => $translation->seo_description,
                            'meta_keywords'     => $translation->meta_keywords,
                            'summary'           => $translation->summary,
                            'alt_text'          => $translation->alt_text,
                        ])->toArray();

                        $product->translations()->createMany($payload);
                    }

                    Notification::make()
                        ->title(__('products.messages.duplicated_success'))
                        ->success()
                        ->send();
                })
                ->visible(fn () => AuthorizationMatrix::check('products', 'create')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('products.messages.updated_successfully'))
            ->body(__('products.messages.updated_successfully_description'));
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing('translations');

        return $this->hydrateFormWithTranslations($this->record, $data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        [$data, $translations] = $this->extractTranslationsFromForm($data);
        $this->languageTabsPayload = $this->sanitizeTranslatablePayload($translations);

        $data = $this->mutateMainDataWithDefaultLocale($data, $this->languageTabsPayload);

        $defaultLocale = $this->getDefaultLocale();
        $defaultName = $this->languageTabsPayload[$defaultLocale]['name'] ?? $data['name'] ?? $this->record->name;
        $slugFromTranslations = $this->languageTabsPayload[$defaultLocale]['slug'] ?? null;

        if (filled($slugFromTranslations)) {
            $data['slug'] = $slugFromTranslations;
        } elseif (filled($defaultName) && $defaultName !== $this->record->name) {
            $slug = Str::slug($defaultName);
            $data['slug'] = $slug;
            $this->languageTabsPayload[$defaultLocale]['slug'] = $slug;
        }

        if (($data['is_visible'] ?? $this->record->is_visible) && is_null($this->record->published_at)) {
            $data['published_at'] = $data['published_at'] ?? now();
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
            foreach (['description', 'short_description'] as $field) {
                $value = $payload[$field] ?? null;

                if (! is_string($value) || trim($value) === '') {
                    continue;
                }

                // Keep editor input aligned with the sanitizer before persisting updates.
                $translations[$locale][$field] = $sanitizer->sanitize($value);
            }
        }

        return $translations;
    }
}

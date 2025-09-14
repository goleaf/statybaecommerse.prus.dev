<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use App\Models\Legal;
use App\Models\Translations\LegalTranslation;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

/**
 * CreateLegal
 * 
 * Filament resource for admin panel management.
 */
class CreateLegal extends CreateRecord
{
    protected static string $resource = LegalResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle translations data
        $translations = $data['translations'] ?? [];
        unset($data['translations']);

        // Set default published_at if not set
        if (empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $translations = $this->data['translations'] ?? [];

        // Create translations
        foreach ($translations as $locale => $translationData) {
            if (!empty($translationData['title'])) {
                LegalTranslation::create([
                    'legal_id' => $record->id,
                    'locale' => $locale,
                    'title' => $translationData['title'],
                    'slug' => $translationData['slug'],
                    'content' => $translationData['content'] ?? '',
                    'seo_title' => $translationData['seo_title'] ?? '',
                    'seo_description' => $translationData['seo_description'] ?? '',
                ]);
            }
        }

        Notification::make()
            ->title(__('admin.legal.created_successfully'))
            ->success()
            ->send();
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;

final /**
 * CreateNews
 * 
 * Filament resource for admin panel management.
 */
class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default values
        $data['is_visible'] = $data['is_visible'] ?? true;
        $data['published_at'] = $data['published_at'] ?? now();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Create translation records if provided
        if (isset($this->data['translations'])) {
            $translations = $this->data['translations'];
            $news = $this->record;

            foreach ($translations as $locale => $translationData) {
                if (! empty($translationData['title'])) {
                    $news->translations()->create([
                        'locale' => $locale,
                        'title' => $translationData['title'],
                        'slug' => $translationData['slug'] ?? \Str::slug($translationData['title']),
                        'summary' => $translationData['summary'] ?? null,
                        'content' => $translationData['content'] ?? null,
                        'seo_title' => $translationData['seo_title'] ?? null,
                        'seo_description' => $translationData['seo_description'] ?? null,
                    ]);
                }
            }
        }
    }
}

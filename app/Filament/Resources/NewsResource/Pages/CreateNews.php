<?php declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\Pages;

use App\Filament\Resources\NewsResource;
use App\Services\MultiLanguageTabService;
use Filament\Resources\Pages\CreateRecord;

final class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $prepared = MultiLanguageTabService::prepareTranslationData($data, ['title', 'slug', 'summary', 'content', 'seo_title', 'seo_description']);
        $this->data['translations'] = $prepared['translations'];
        return $prepared['main_data'];
    }

    protected function afterCreate(): void
    {
        $translations = $this->data['translations'] ?? [];
        foreach ($translations as $locale => $fields) {
            $this->record->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $fields['title'] ?? null,
                    'slug' => $fields['slug'] ?? null,
                    'summary' => $fields['summary'] ?? null,
                    'content' => $fields['content'] ?? null,
                    'seo_title' => $fields['seo_title'] ?? null,
                    'seo_description' => $fields['seo_description'] ?? null,
                ]
            );
        }
    }
}

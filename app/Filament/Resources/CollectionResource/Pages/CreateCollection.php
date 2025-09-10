<?php declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Resources\CollectionResource;
use App\Services\MultiLanguageTabService;
use Filament\Resources\Pages\CreateRecord;

final class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $prepared = MultiLanguageTabService::prepareTranslationData($data, ['name', 'slug', 'description', 'seo_title', 'seo_description']);
        $this->data['translations'] = $prepared['translations'];
        $main = $prepared['main_data'];
        $defaultLocale = config('app.locale', 'lt');
        $tr = $prepared['translations'][$defaultLocale] ?? [];
        $main['name'] = $main['name'] ?? ($tr['name'] ?? null);
        $main['slug'] = $main['slug'] ?? ($tr['slug'] ?? null);
        $main['description'] = $main['description'] ?? ($tr['description'] ?? null);
        $main['seo_title'] = $main['seo_title'] ?? ($tr['seo_title'] ?? null);
        $main['seo_description'] = $main['seo_description'] ?? ($tr['seo_description'] ?? null);

        if (!array_key_exists('sort_order', $main) || $main['sort_order'] === null) {
            $main['sort_order'] = 0;
        }
        if (!array_key_exists('is_visible', $main) || $main['is_visible'] === null) {
            $main['is_visible'] = true;
        }
        if (!array_key_exists('is_automatic', $main) || $main['is_automatic'] === null) {
            $main['is_automatic'] = false;
        }

        return $main;
    }

    protected function afterCreate(): void
    {
        $translations = $this->data['translations'] ?? [];
        if (!empty($translations)) {
            foreach ($translations as $locale => $fields) {
                $this->record->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name' => $fields['name'] ?? $this->record->name,
                        'slug' => $fields['slug'] ?? $this->record->slug,
                        'description' => $fields['description'] ?? null,
                        'seo_title' => $fields['seo_title'] ?? null,
                        'seo_description' => $fields['seo_description'] ?? null,
                    ]
                );
            }
        }
    }
}

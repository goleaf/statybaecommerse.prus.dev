<?php declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\Pages;

use App\Filament\Resources\CollectionResource;
use App\Services\MultiLanguageTabService;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditCollection extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $prepared = MultiLanguageTabService::prepareTranslationData($data, ['name', 'slug', 'description', 'seo_title', 'seo_description']);
        $this->data['translations'] = $prepared['translations'];
        $main = $prepared['main_data'];
        $defaultLocale = config('app.locale', 'lt');
        $tr = $prepared['translations'][$defaultLocale] ?? [];
        $main['name'] = $main['name'] ?? ($tr['name'] ?? $this->record->name);
        $main['slug'] = $main['slug'] ?? ($tr['slug'] ?? $this->record->slug);
        $main['description'] = $main['description'] ?? ($tr['description'] ?? null);
        $main['seo_title'] = $main['seo_title'] ?? ($tr['seo_title'] ?? null);
        $main['seo_description'] = $main['seo_description'] ?? ($tr['seo_description'] ?? null);
        return $main;
    }

    protected function afterSave(): void
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

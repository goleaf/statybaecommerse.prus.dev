<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use App\Services\MultiLanguageTabService;
use Filament\Resources\Pages\CreateRecord;

final class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $prepared = MultiLanguageTabService::prepareTranslationData($data, ['name', 'slug', 'description']);
        // Store translations temporarily in $this->data for afterCreate
        $this->data['translations'] = $prepared['translations'];
        $main = $prepared['main_data'];
        $defaultLocale = config('app.locale', 'lt');
        $tr = $prepared['translations'][$defaultLocale] ?? [];
        $main['name'] = $main['name'] ?? ($tr['name'] ?? null);
        $main['slug'] = $main['slug'] ?? ($tr['slug'] ?? null);
        $main['description'] = $main['description'] ?? ($tr['description'] ?? null);

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
                    ]
                );
            }
        }
    }
}

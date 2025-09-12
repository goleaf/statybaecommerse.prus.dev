<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use App\Services\MultiLanguageTabService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditCampaign extends EditRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $prepared = MultiLanguageTabService::prepareTranslationData($data, ['name', 'slug', 'description']);
        $this->data['translations'] = $prepared['translations'];
        $main = $prepared['main_data'];
        $defaultLocale = config('app.locale', 'lt');
        $tr = $prepared['translations'][$defaultLocale] ?? [];
        $main['name'] = $main['name'] ?? ($tr['name'] ?? $this->record->name);
        $main['slug'] = $main['slug'] ?? ($tr['slug'] ?? $this->record->slug);
        $main['description'] = $main['description'] ?? ($tr['description'] ?? null);
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
                    ]
                );
            }
        }
    }
}

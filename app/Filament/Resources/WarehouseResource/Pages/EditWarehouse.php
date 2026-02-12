<?php

declare(strict_types=1);

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\WarehouseResource;
use App\Models\Location;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditWarehouse extends EditRecord
{
    use InteractsWithTranslationTabs;

    protected static string $resource = WarehouseResource::class;

    /**
     * @return array<int, string>
     */
    protected function getTranslatableFields(): array
    {
        return ['name', 'slug', 'description'];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;

        if (! $record instanceof Location) {
            return $data;
        }

        return $this->hydrateFormWithTranslations($record, $data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['type'] = 'warehouse';

        [$data, $translations] = $this->extractTranslationsFromForm($data);

        $this->languageTabsPayload = $translations;

        return $this->mutateMainDataWithDefaultLocale($data, $translations);
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if (! $record instanceof Location) {
            return;
        }

        $this->syncTranslationRecords($record, $this->languageTabsPayload);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

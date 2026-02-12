<?php

declare(strict_types=1);

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use App\Filament\Resources\WarehouseResource;
use App\Models\Location;
use Filament\Resources\Pages\CreateRecord;

final class CreateWarehouse extends CreateRecord
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = 'warehouse';

        [$data, $translations] = $this->extractTranslationsFromForm($data);

        $this->languageTabsPayload = $translations;

        return $this->mutateMainDataWithDefaultLocale($data, $translations);
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        if (! $record instanceof Location) {
            return;
        }

        $this->syncTranslationRecords($record, $this->languageTabsPayload);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceResource\Pages;

use App\Filament\Resources\PriceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePrice extends CreateRecord
{
    protected static string $resource = PriceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle translations
        if (isset($data['translations'])) {
            $translations = $data['translations'];
            unset($data['translations']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Save translations after creating the price
        if (isset($this->data['translations'])) {
            $translations = $this->data['translations'];

            foreach ($translations as $locale => $translationData) {
                if (! empty(array_filter($translationData))) {
                    $this->record->updateTranslation($locale, $translationData);
                }
            }
        }
    }
}

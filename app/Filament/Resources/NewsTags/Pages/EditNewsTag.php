<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTags\Pages;

use App\Filament\Resources\NewsTags\NewsTagResource;
use App\Filament\Resources\NewsTags\Pages\Concerns\HandlesNewsTagTranslations;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsTag extends EditRecord
{
    use HandlesNewsTagTranslations;

    protected static string $resource = NewsTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->prepareNewsTagFormData($data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->loadMissing('translations');

        $translations = $this->record->translations
            ->map(fn ($translation) => [
                'id'          => $translation->getKey(),
                'locale'      => $translation->locale,
                'name'        => $translation->name,
                'slug'        => $translation->slug,
                'description' => $translation->description,
            ])
            ->values()
            ->all();

        $defaultLocale = config('app.locale');
        $defaultTranslation = collect($translations)->firstWhere('locale', $defaultLocale);

        $data['translations'] = $translations;
        $data['name'] = $defaultTranslation['name'] ?? $data['name'] ?? null;
        $data['slug'] = $defaultTranslation['slug'] ?? $data['slug'] ?? null;
        $data['description'] = $defaultTranslation['description'] ?? $data['description'] ?? null;

        return $data;
    }
}

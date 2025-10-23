<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTags\Pages\Concerns;

use Illuminate\Support\Arr;

trait HandlesNewsTagTranslations
{
    protected function prepareNewsTagFormData(array $data): array
    {
        $defaultLocale = config('app.locale');

        $translations = collect($data['translations'] ?? [])
            ->mapWithKeys(function (array $translation): array {
                $locale = $translation['locale'] ?? null;

                if (! $locale) {
                    return [];
                }

                return [
                    $locale => [
                        'id'          => $translation['id'] ?? null,
                        'locale'      => $locale,
                        'name'        => $translation['name'] ?? null,
                        'slug'        => $translation['slug'] ?? null,
                        'description' => $translation['description'] ?? null,
                    ],
                ];
            });

        $translations[$defaultLocale] = [
            'id'          => $translations[$defaultLocale]['id'] ?? null,
            'locale'      => $defaultLocale,
            'name'        => $data['name'] ?? $translations[$defaultLocale]['name'] ?? null,
            'slug'        => $data['slug'] ?? $translations[$defaultLocale]['slug'] ?? null,
            'description' => $data['description'] ?? $translations[$defaultLocale]['description'] ?? null,
        ];

        $data['translations'] = $translations
            ->map(function (array $translation): array {
                $prepared = [
                    'locale'      => $translation['locale'],
                    'name'        => $translation['name'],
                    'slug'        => $translation['slug'],
                    'description' => $translation['description'] ?? null,
                ];

                if (! empty($translation['id'])) {
                    $prepared['id'] = $translation['id'];
                }

                return $prepared;
            })
            ->values()
            ->all();

        return Arr::except($data, ['name', 'slug', 'description']);
    }
}

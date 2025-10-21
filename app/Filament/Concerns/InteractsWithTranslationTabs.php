<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Contracts\TranslatableRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait InteractsWithTranslationTabs
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $languageTabsPayload = [];

    /**
     * @return array<int, string>
     */
    protected function getTranslatableFields(): array
    {
        return [];
    }

    /**
     * @return array<int, string>
     */
    protected function getAvailableLocales(): array
    {
        $rawLocales = config('filament-language-tabs.default_locales', []);
        if (! is_array($rawLocales)) {
            $rawLocales = explode(',', (string) $rawLocales);
        }

        $normalized = array_map(
            static fn ($locale): string => trim((string) $locale),
            $rawLocales,
        );

        return array_values(array_filter($normalized, static fn (string $locale): bool => $locale !== ''));
    }

    protected function getDefaultLocale(): string
    {
        return config('app.locale', 'en');
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, array<string, mixed>>}
     */
    /**
     * @param  array<string, mixed>                                                   $data
     * @return array{0: array<string, mixed>, 1: array<string, array<string, mixed>>}
     */
    protected function extractTranslationsFromForm(array $data): array
    {
        $fields = $this->getTranslatableFields();
        if ($fields === []) {
            return [$data, []];
        }

        $locales = $this->getAvailableLocales();
        $translations = [];

        foreach ($fields as $field) {
            $fieldValue = $data[$field] ?? null;
            unset($data[$field]);

            if (! is_array($fieldValue)) {
                continue;
            }

            foreach ($fieldValue as $locale => $value) {
                if (! in_array($locale, $locales, true)) {
                    continue;
                }
                $translations[$locale][$field] = $value;
            }
        }

        return [$data, $translations];
    }

    /**
     * @param  array<string, mixed>                $data
     * @param  array<string, array<string, mixed>> $translations
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>                $data
     * @param  array<string, array<string, mixed>> $translations
     * @return array<string, mixed>
     */
    protected function mutateMainDataWithDefaultLocale(array $data, array $translations): array
    {
        $defaultLocale = $this->getDefaultLocale();
        foreach ($this->getTranslatableFields() as $field) {
            if (isset($translations[$defaultLocale][$field])) {
                $data[$field] = $translations[$defaultLocale][$field];
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    /**
     * @param  TranslatableRecord&Model $record
     * @param  array<string, mixed>     $data
     * @return array<string, mixed>
     */
    protected function hydrateFormWithTranslations(Model $record, array $data): array
    {
        $fields = $this->getTranslatableFields();
        if ($fields === []) {
            return $data;
        }

        $locales = $this->getAvailableLocales();
        $translations = $record->translations()->get()->groupBy('locale');

        foreach ($fields as $field) {
            $state = [];
            foreach ($locales as $locale) {
                $state[$locale] = $translations->get($locale)?->first()?->{$field};
            }

            $defaultLocale = $this->getDefaultLocale();
            if (! array_key_exists($defaultLocale, $state)) {
                $state[$defaultLocale] = $record->{$field};
            } elseif (empty($state[$defaultLocale])) {
                $state[$defaultLocale] = $record->{$field};
            }

            $data[$field] = $state;
        }

        return $data;
    }

    /**
     * @param array<string, array<string, mixed>> $translations
     */
    /**
     * @param TranslatableRecord&Model            $record
     * @param array<string, array<string, mixed>> $translations
     */
    protected function syncTranslationRecords(Model $record, array $translations): void
    {
        if ($translations === []) {
            return;
        }

        $fields = $this->getTranslatableFields();
        $defaultLocale = $this->getDefaultLocale();

        foreach ($translations as $locale => $payload) {
            $values = Arr::only($payload, $fields);
            $hasContent = Collection::make($values)
                ->filter(static fn ($value) => filled($value))
                ->isNotEmpty();

            if (! $hasContent) {
                $record->translations()->where('locale', $locale)->delete();

                continue;
            }

            $record->translations()->updateOrCreate(
                ['locale' => $locale],
                array_merge($values, ['locale' => $locale])
            );

            if ($locale === $defaultLocale) {
                foreach ($fields as $field) {
                    if (filled($values[$field] ?? null)) {
                        $record->{$field} = $values[$field];
                    }
                }
            }
        }

        $record->saveQuietly();
    }

    /**
     * @param  array<string, array<string, mixed>> $translations
     * @return array<string, array<string, mixed>>
     */
    protected function filterEmptyTranslations(array $translations): array
    {
        return Collection::make($translations)
            ->map(
                static fn (array $payload): array => Collection::make($payload)
                    ->filter(static fn ($value) => filled($value))
                    ->all()
            )
            ->filter(static fn (array $payload): bool => $payload !== [])
            ->all();
    }
}

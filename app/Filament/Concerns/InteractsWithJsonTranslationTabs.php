<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait InteractsWithJsonTranslationTabs
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
        $defaultLocale = $this->getDefaultLocale();
        /** @var array<string, array<string, mixed>> $translations */
        $translations = [];

        foreach ($fields as $field) {
            $fieldValue = $data[$field] ?? null;

            // Retain scalar values in the base data so callers without translation tabs remain intact.
            if (! is_array($fieldValue)) {
                if (filled($fieldValue)) {
                    // Mirror scalar submissions into the default locale bucket to keep translation columns up to date.
                    $translations[$defaultLocale][$field] = $fieldValue;
                }

                continue;
            }

            // Only remove the field when we have locale-indexed input to avoid erasing scalar submissions.
            unset($data[$field]);

            // Track whether the default locale has an explicit value so we can fallback when it is omitted.
            $defaultLocaleProvided = array_key_exists($defaultLocale, $fieldValue);

            // Remember the first filled value across the allowed locales to use as a sensible fallback for the default locale.
            $firstFilledLocaleValue = null;

            foreach ($fieldValue as $locale => $value) {
                if (! is_string($locale)) {
                    continue;
                }

                if (! in_array($locale, $locales, true)) {
                    continue;
                }

                if ($firstFilledLocaleValue === null && filled($value)) {
                    $firstFilledLocaleValue = $value;
                }

                $translations[$locale][$field] = $value;
            }

            // Seed the default locale with the first populated value when the submission omitted that locale entirely.
            if ((! $defaultLocaleProvided || blank($fieldValue[$defaultLocale] ?? null)) && $firstFilledLocaleValue !== null) {
                $translations[$defaultLocale][$field] = $firstFilledLocaleValue;
            }
        }

        return [$data, $translations];
    }

    /**
     * @param  array<string, mixed>                $data
     * @param  array<string, array<string, mixed>> $translations
     * @return array<string, mixed>
     */
    protected function mutateMainDataWithDefaultLocale(array $data, array $translations): array
    {
        $defaultLocale = $this->getDefaultLocale();

        foreach ($this->getTranslatableFields() as $field) {
            $value = $translations[$defaultLocale][$field] ?? null;

            if ($value === null) {
                continue;
            }

            $data[$field] = $value;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function hydrateFormWithTranslations(Model $record, array $data): array
    {
        $fields = $this->getTranslatableFields();
        if ($fields === []) {
            return $data;
        }

        $locales = $this->getAvailableLocales();

        foreach ($fields as $field) {
            $translationColumn = sprintf('%s_translations', $field);
            $existing = $record->{$translationColumn} ?? [];
            if (! is_array($existing)) {
                $existing = [];
            }

            /** @var array<string, mixed|null> $state */
            $state = [];
            foreach ($locales as $locale) {
                $state[$locale] = $existing[$locale] ?? null;
            }

            $defaultLocale = $this->getDefaultLocale();
            if (! array_key_exists($defaultLocale, $state) || $state[$defaultLocale] === null) {
                $state[$defaultLocale] = $record->{$field};
            }

            $data[$field] = $state;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>                $data
     * @param  array<string, array<string, mixed>> $translations
     * @return array<string, mixed>
     */
    protected function mergeTranslationsIntoData(array $data, array $translations, ?Model $record = null): array
    {
        if ($translations === []) {
            return $data;
        }

        foreach ($this->getTranslatableFields() as $field) {
            $translationColumn = sprintf('%s_translations', $field);
            /** @var array<string, mixed> $existing */
            $existing = [];

            if ($record !== null) {
                $current = $record->{$translationColumn} ?? [];
                if (is_array($current)) {
                    $existing = $current;
                }
            }

            foreach ($translations as $locale => $payload) {
                $value = Arr::get($payload, $field);

                if (! filled($value)) {
                    unset($existing[$locale]);

                    continue;
                }

                if (is_string($value)) {
                    $value = trim($value);
                }

                $existing[$locale] = $value;
            }

            $data[$translationColumn] = $existing;
        }

        return $data;
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

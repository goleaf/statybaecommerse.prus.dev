<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Contracts\TranslatableRecord;
use App\Models\Translations\NewsTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ManagesNewsTranslationTabs
{
    /**
     * @return array<int, string>
     */
    protected function getTranslatableFields(): array
    {
        return ['title', 'slug', 'summary', 'content', 'meta_title', 'meta_description'];
    }

    /**
     * @param  array<string, mixed>                $data
     * @param  array<string, array<string, mixed>> $translations
     * @return array<string, mixed>
     */
    protected function mutateMainDataWithDefaultLocale(array $data, array $translations): array
    {
        return $data;
    }

    /**
     * @param  array<string, array<string, mixed>> $translations
     * @return array<string, array<string, mixed>>
     */
    protected function ensureDefaultLocaleSlug(array $translations, ?string $fallbackSlug = null): array
    {
        $defaultLocale = $this->getDefaultLocale();
        $defaultSlug = $translations[$defaultLocale]['slug'] ?? null;

        if (blank($defaultSlug)) {
            $source = $translations[$defaultLocale]['title'] ?? $fallbackSlug;
            if (is_string($source) && $source !== '') {
                $translations[$defaultLocale]['slug'] = Str::slug($source);
            }
        }

        return $translations;
    }

    /**
     * @param array<string, array<string, mixed>> $translations
     */
    protected function assertUniqueSlugs(array $translations, ?int $ignoreNewsId = null): void
    {
        foreach ($translations as $locale => $payload) {
            if (! is_array($payload)) {
                continue;
            }

            $slug = $payload['slug'] ?? null;

            if (! filled($slug)) {
                continue;
            }

            $query = NewsTranslation::query()
                ->where('locale', $locale)
                ->where('slug', $slug);

            // Respect the optional ignore identifier even when it is falsy (e.g. zero) by using a strict null check.
            if ($ignoreNewsId !== null) {
                $query->where('news_id', '!=', $ignoreNewsId);
            }

            if ($query->exists()) {
                throw ValidationException::withMessages([
                    "slug.$locale" => __('validation.unique', ['attribute' => __('news.fields.slug') . ' (' . $locale . ')']),
                ]);
            }
        }
    }

    /**
     * @param array<string, array<string, mixed>> $translations
     */
    protected function syncTranslationRecords(Model&TranslatableRecord $record, array $translations): void
    {
        if ($translations === []) {
            return;
        }

        $fields = $this->getTranslatableFields();

        foreach ($translations as $locale => $payload) {
            if (! is_array($payload)) {
                continue;
            }

            $values = Arr::only($payload, $fields);
            $hasContent = Collection::make($values)
                ->filter(static fn ($value): bool => filled($value))
                ->isNotEmpty();

            if (! $hasContent) {
                $record->translations()->where('locale', $locale)->delete();

                continue;
            }

            $record->translations()->updateOrCreate(
                ['locale' => $locale],
                array_merge($values, ['locale' => $locale])
            );
        }
    }
}

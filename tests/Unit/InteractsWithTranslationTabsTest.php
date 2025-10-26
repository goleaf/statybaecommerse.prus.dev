<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Filament\Concerns\InteractsWithTranslationTabs;
use Illuminate\Support\Arr;
use PHPUnit\Framework\TestCase;

final class InteractsWithTranslationTabsTest extends TestCase
{
    public function test_extracts_translations_and_preserves_scalar_data(): void
    {
        $handler = new class
        {
            use InteractsWithTranslationTabs {
                extractTranslationsFromForm as public traitExtractTranslationsFromForm;
                mutateMainDataWithDefaultLocale as public traitMutateMainDataWithDefaultLocale;
            }

            protected function getTranslatableFields(): array
            {
                return ['name', 'slug'];
            }

            protected function getAvailableLocales(): array
            {
                return ['lt', 'en'];
            }

            protected function getDefaultLocale(): string
            {
                return 'lt';
            }
        };

        [$data, $translations] = $handler->traitExtractTranslationsFromForm([
            'name'      => ['lt' => 'Vardas', 'en' => 'Name'],
            'slug'      => ['lt' => 'vardas', 'en' => 'name'],
            'is_active' => true,
        ]);

        self::assertSame('Vardas', Arr::get($translations, 'lt.name'));
        self::assertSame('name', Arr::get($translations, 'en.slug'));

        $mutated = $handler->traitMutateMainDataWithDefaultLocale($data, $translations);

        self::assertSame('Vardas', $mutated['name']);
        self::assertSame('vardas', $mutated['slug']);
        self::assertTrue($mutated['is_active']);
    }

    public function test_backfills_default_locale_when_missing_from_payload(): void
    {
        $handler = new class
        {
            use InteractsWithTranslationTabs {
                extractTranslationsFromForm as public traitExtractTranslationsFromForm;
                mutateMainDataWithDefaultLocale as public traitMutateMainDataWithDefaultLocale;
            }

            protected function getTranslatableFields(): array
            {
                return ['name'];
            }

            protected function getAvailableLocales(): array
            {
                return ['lt', 'en'];
            }

            protected function getDefaultLocale(): string
            {
                return 'lt';
            }
        };

        [$data, $translations] = $handler->traitExtractTranslationsFromForm([
            'name' => ['en' => 'Translated Name'],
        ]);

        // Make sure the translation array mirrors the provided value into the default locale to keep the model column updated.
        self::assertSame('Translated Name', $translations['lt']['name']);

        // Confirm the originally provided locale entry stays untouched after the fallback runs.
        self::assertSame('Translated Name', $translations['en']['name']);

        $mutated = $handler->traitMutateMainDataWithDefaultLocale($data, $translations);

        // Verify that the base dataset regains the translated value for validation and fillable synchronisation.
        self::assertSame('Translated Name', $mutated['name']);
    }
}

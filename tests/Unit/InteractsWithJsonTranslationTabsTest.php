<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Filament\Concerns\InteractsWithJsonTranslationTabs;
use PHPUnit\Framework\TestCase;

final class InteractsWithJsonTranslationTabsTest extends TestCase
{
    public function test_extracts_and_merges_translations(): void
    {
        $handler = new class
        {
            use InteractsWithJsonTranslationTabs {
                extractTranslationsFromForm as public traitExtractTranslationsFromForm;
                mutateMainDataWithDefaultLocale as public traitMutateMainDataWithDefaultLocale;
                mergeTranslationsIntoData as public traitMergeTranslationsIntoData;
            }

            /**
             * @return array<int, string>
             */
            protected function getTranslatableFields(): array
            {
                return ['title', 'content'];
            }

            /**
             * @return array<int, string>
             */
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
            'title'   => ['lt' => 'Pavadinimas', 'en' => 'Title'],
            'content' => ['lt' => 'Turinys', 'en' => 'Content'],
            'status'  => 'draft',
        ]);

        self::assertSame('Pavadinimas', $translations['lt']['title']);
        self::assertArrayNotHasKey('title', $data);

        $data = $handler->traitMutateMainDataWithDefaultLocale($data, $translations);
        self::assertSame('Pavadinimas', $data['title']);

        $merged = $handler->traitMergeTranslationsIntoData($data, $translations);
        self::assertSame(
            ['lt' => 'Pavadinimas', 'en' => 'Title'],
            $merged['title_translations']
        );
    }

    public function test_retains_scalar_payload_and_seeds_default_translation(): void
    {
        $handler = new class
        {
            use InteractsWithJsonTranslationTabs {
                extractTranslationsFromForm as public traitExtractTranslationsFromForm;
            }

            /**
             * @return array<int, string>
             */
            protected function getTranslatableFields(): array
            {
                return ['title'];
            }

            /**
             * @return array<int, string>
             */
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
            'title'  => 'Pavadinimas',
            'status' => 'draft',
        ]);

        // Ensure the original scalar field remains in the root data payload.
        self::assertSame('Pavadinimas', $data['title']);

        // Confirm the scalar submission is duplicated into the default locale translation array.
        self::assertSame('Pavadinimas', $translations['lt']['title']);

        // Verify no unexpected locales were introduced when only scalar input is provided.
        self::assertArrayNotHasKey('en', $translations);
    }

    public function test_missing_default_locale_falls_back_to_first_available_translation(): void
    {
        $handler = new class
        {
            use InteractsWithJsonTranslationTabs {
                extractTranslationsFromForm as public traitExtractTranslationsFromForm;
                mutateMainDataWithDefaultLocale as public traitMutateMainDataWithDefaultLocale;
            }

            /**
             * @return array<int, string>
             */
            protected function getTranslatableFields(): array
            {
                return ['title'];
            }

            /**
             * @return array<int, string>
             */
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
            'title' => ['en' => 'Fallback Title'],
        ]);

        // Ensure the fallback copies the first available locale into the default slot when the locale is missing entirely.
        self::assertSame('Fallback Title', $translations['lt']['title']);

        // Confirm the explicitly provided locale is preserved alongside the fallback default locale.
        self::assertSame('Fallback Title', $translations['en']['title']);

        $mutated = $handler->traitMutateMainDataWithDefaultLocale($data, $translations);

        // Validate that the base payload regains the translated value for downstream consumers like validation rules.
        self::assertSame('Fallback Title', $mutated['title']);
    }
}

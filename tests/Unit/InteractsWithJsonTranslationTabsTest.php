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
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Filament;

use App\Filament\Concerns\InteractsWithJsonTranslationTabs;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;

final class JsonTranslationTabsHydrationTest extends TestCase
{
    public function test_hydrates_default_locale_when_missing(): void
    {
        $handler = new class {
            use InteractsWithJsonTranslationTabs { hydrateFormWithTranslations as public traitHydrate; }

            protected function getTranslatableFields(): array { return ['title']; }
            protected function getAvailableLocales(): array { return ['lt', 'en']; }
            protected function getDefaultLocale(): string { return 'lt'; }
        };

        $record = new class extends Model {
            public $title = 'DefaultTitle';
            public $title_translations = ['en' => 'TitleEn'];
        };

        $data = $handler->traitHydrate($record, []);

        self::assertArrayHasKey('title', $data);
        self::assertSame('DefaultTitle', $data['title']['lt']);
        self::assertSame('TitleEn', $data['title']['en']);
    }
}


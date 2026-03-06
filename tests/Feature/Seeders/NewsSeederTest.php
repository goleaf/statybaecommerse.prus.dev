<?php

declare(strict_types=1);

use App\Enums\ModerationState;
use App\Models\News;
use App\Models\Translations\NewsTranslation;
use Database\Seeders\NewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers news seeder in the standard seeder profile', function (): void {
    $standardSeeders = config('seeds.standard_seeders', []);

    expect($standardSeeders)->toContain(NewsSeeder::class);
});

it('seeds lorem ipsum news for admin and frontend contexts', function (): void {
    $this->seed(NewsSeeder::class);

    $seededNews = News::withoutGlobalScopes()
        ->where('author_email', 'like', 'info@egisstatyba.lt')
        ->orderBy('author_email')
        ->get();

    expect($seededNews)->toHaveCount(24);

    $frontendReadyCount = News::withoutGlobalScopes()
        ->where('author_email', 'like', 'info@egisstatyba.lt')
        ->where('moderation_state', ModerationState::Published->value)
        ->where('is_visible', true)
        ->whereNotNull('published_at')
        ->whereHas('images')
        ->count();

    expect($frontendReadyCount)->toBe(16);

    $sampleNews = $seededNews->first();
    expect($sampleNews)->not->toBeNull();

    $locales = seededNewsLocales();
    $translationCount = NewsTranslation::query()
        ->where('news_id', $sampleNews->id)
        ->count();

    expect($translationCount)->toBe(count($locales));

    $sampleSummary = NewsTranslation::query()
        ->where('news_id', $sampleNews->id)
        ->where('locale', $locales[0])
        ->value('summary');

    expect((string) $sampleSummary)->toContain('Lorem ipsum');
});

it('is idempotent when run multiple times', function (): void {
    $this->seed(NewsSeeder::class);
    $this->seed(NewsSeeder::class);

    $seededNewsCount = News::withoutGlobalScopes()
        ->where('author_email', 'like', 'info@egisstatyba.lt')
        ->count();

    expect($seededNewsCount)->toBe(24);
});

/**
 * @return array<int, string>
 */
function seededNewsLocales(): array
{
    $configuredLocales = config('app.supported_locales', 'lt,en,ru,de');
    $locales = is_array($configuredLocales)
        ? $configuredLocales
        : explode(',', (string) $configuredLocales);

    $locales = collect($locales)
        ->map(static fn (mixed $locale): string => strtolower(trim((string) $locale)))
        ->filter()
        ->unique()
        ->values()
        ->all();

    if ($locales === []) {
        return ['lt', 'en', 'ru', 'de'];
    }

    return $locales;
}

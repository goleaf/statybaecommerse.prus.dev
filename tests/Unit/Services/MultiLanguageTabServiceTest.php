<?php

use App\Services\MultiLanguageTabService;
use Illuminate\Support\Collection;

it('returns available languages with expected structure', function (): void {
    config()->set('app-features.supported_locales', ['lt', 'en']);

    $languages = MultiLanguageTabService::getAvailableLanguages();

    expect($languages)
        ->toBeArray()
        ->and(count($languages))
        ->toBe(2)
        ->and($languages[0])
        ->toHaveKeys(['code', 'name', 'flag'])
        ->and($languages[0]['code'])
        ->toBe('lt')
        ->and($languages[1]['code'])
        ->toBe('en');
});

it('computes default active tab index with lt prioritized', function (): void {
    // lt first
    config()->set('app-features.supported_locales', ['lt', 'en']);
    expect(MultiLanguageTabService::getDefaultActiveTab())->toBe(0);

    // en first, lt second
    config()->set('app-features.supported_locales', ['en', 'lt']);
    expect(MultiLanguageTabService::getDefaultActiveTab())->toBe(1);
});

it('prepares translation data by extracting translatable fields', function (): void {
    config()->set('app-features.supported_locales', ['lt', 'en']);

    $formData = [
        'title_lt' => 'Pavadinimas LT',
        'title_en' => 'Title EN',
        'description_lt' => 'Aprašymas LT',
        'status' => 'published',
    ];

    $result = MultiLanguageTabService::prepareTranslationData($formData, ['title', 'description']);

    expect($result)
        ->toHaveKeys(['main_data', 'translations'])
        ->and($result['main_data'])
        ->toHaveKey('status', 'published')
        ->and($result['main_data'])
        ->not
        ->toHaveKey('title_lt')
        ->not
        ->toHaveKey('description_lt')
        ->and($result['translations'])
        ->toHaveKey('lt')
        ->toHaveKey('en')
        ->and($result['translations']['lt']['title'])
        ->toBe('Pavadinimas LT')
        ->and($result['translations']['en']['title'])
        ->toBe('Title EN');
});

it('populates form with translations from a record-like object', function (): void {
    config()->set('app-features.supported_locales', ['lt', 'en']);

    $translationLt = (object) ['locale' => 'lt', 'title' => 'Pavadinimas LT', 'description' => 'Aprašymas LT'];
    $translationEn = (object) ['locale' => 'en', 'title' => 'Title EN', 'description' => 'Description EN'];

    // Minimal stub that satisfies method_exists($record, 'translations') and exposes $record->translations collection
    $record = new class($translationLt, $translationEn) {
        public Collection $translations;

        public function __construct($lt, $en)
        {
            $this->translations = collect([$lt, $en]);
        }

        public function translations(): void {}
    };

    $form = MultiLanguageTabService::populateFormWithTranslations($record, ['title', 'description']);

    expect($form)
        ->toHaveKey('title_lt', 'Pavadinimas LT')
        ->toHaveKey('description_lt', 'Aprašymas LT')
        ->toHaveKey('title_en', 'Title EN')
        ->toHaveKey('description_en', 'Description EN');
});

it('creates simple tabs for each language when TabLayout plugin is available', function (): void {
    if (!class_exists(\SolutionForest\TabLayoutPlugin\Components\Tabs\Tab::class)) {
        $this->markTestSkipped('TabLayout plugin not installed.');
    }

    config()->set('app-features.supported_locales', ['lt', 'en']);

    $tabs = MultiLanguageTabService::createSimpleTabs([
        'name' => ['type' => 'text', 'required' => true],
        'description' => ['type' => 'textarea'],
    ]);

    expect($tabs)
        ->toBeArray()
        ->and(count($tabs))
        ->toBe(2)
        ->and($tabs[0])
        ->toBeInstanceOf(\SolutionForest\TabLayoutPlugin\Components\Tabs\Tab::class);
});

it('creates advanced tabs with a schema builder per language', function (): void {
    if (!class_exists(\SolutionForest\TabLayoutPlugin\Components\Tabs\Tab::class)) {
        $this->markTestSkipped('TabLayout plugin not installed.');
    }

    config()->set('app-features.supported_locales', ['lt', 'en']);

    $tabs = MultiLanguageTabService::createAdvancedTabs(function (string $locale) {
        return [\Filament\Forms\Components\TextInput::make("title_{$locale}")];
    });

    expect($tabs)
        ->toBeArray()
        ->and(count($tabs))
        ->toBe(2)
        ->and($tabs[1])
        ->toBeInstanceOf(\SolutionForest\TabLayoutPlugin\Components\Tabs\Tab::class);
});

it('creates simple tab schemas for a Livewire component', function (): void {
    if (!class_exists(\SolutionForest\TabLayoutPlugin\Schemas\SimpleTabSchema::class)) {
        $this->markTestSkipped('TabLayout plugin not installed.');
    }

    config()->set('app-features.supported_locales', ['lt', 'en']);

    $schemas = MultiLanguageTabService::createSimpleTabSchemas('App\Http\Livewire\Dummy', ['foo' => 'bar']);

    expect($schemas)
        ->toBeArray()
        ->and(count($schemas))
        ->toBe(2)
        ->and($schemas[0])
        ->toBeInstanceOf(\SolutionForest\TabLayoutPlugin\Schemas\SimpleTabSchema::class);
});

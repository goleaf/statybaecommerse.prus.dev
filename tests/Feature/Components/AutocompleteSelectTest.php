<?php

declare(strict_types=1);

use App\Filament\Components\AutocompleteSelect;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('feature: renders autocomplete select component', function (): void {
    $component = AutocompleteSelect::make('test_field');

    expect($component)->toBeInstanceOf(AutocompleteSelect::class);
});

it('feature: has correct default configuration', function (): void {
    $component = AutocompleteSelect::make('test_field');

    expect($component->getSearchable())->toBeTrue();
    expect($component->getMultiple())->toBeFalse();
    expect($component->getMinSearchLength())->toBe(2);
    expect($component->getMaxSearchResults())->toBe(10);
    expect($component->getValueField())->toBe('id');
    expect($component->getLabelField())->toBe('name');
});

it('feature: can configure searchable property', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->searchable(false);

    expect($component->getSearchable())->toBeFalse();

    $component->searchable(true);
    expect($component->getSearchable())->toBeTrue();
});

it('feature: can configure multiple selection', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->multiple(true);

    expect($component->getMultiple())->toBeTrue();

    $component->multiple(false);
    expect($component->getMultiple())->toBeFalse();
});

it('feature: can configure search parameters', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->minSearchLength(3)
        ->maxSearchResults(20);

    expect($component->getMinSearchLength())->toBe(3);
    expect($component->getMaxSearchResults())->toBe(20);
});

it('feature: can configure field mappings', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->searchField('title')
        ->valueField('uuid')
        ->labelField('display_name');

    expect($component->getSearchField())->toBe('title');
    expect($component->getValueField())->toBe('uuid');
    expect($component->getLabelField())->toBe('display_name');
});

it('feature: can set model class', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    expect($component->getModelClass())->toBe(Product::class);
});

it('feature: can set search query', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->setSearchQuery('test');

    expect($component->getSearchQuery())->toBe('test');
});

it('feature: performs search when query is set', function (): void {
    $firstProduct = Product::factory()->create(['name' => 'Test Product 1']);
    $secondProduct = Product::factory()->create(['name' => 'Test Product 2']);
    Product::factory()->create(['name' => 'Another Item']);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    $searchResults = $component->getViewData()['searchResults'];

    expect($searchResults)->toHaveCount(2);
    expect(array_values($searchResults))->toContain('Test Product 1');
    expect(array_values($searchResults))->toContain('Test Product 2');
});

it('feature: respects minimum search length', function (): void {
    Product::factory()->create([
        'name' => 'Test Product',
        'is_visible' => true,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->minSearchLength(5);

    $searchResults = $component->getViewData()['searchResults'];

    expect($searchResults)->toBeEmpty();
});

it('feature: limits search results', function (): void {
    Product::factory()->count(15)->create([
        'name' => 'Test Product',
        'is_visible' => true,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->maxSearchResults(5);

    $searchResults = $component->getViewData()['searchResults'];

    expect($searchResults)->toHaveCount(5);
});

it('feature: uses custom search field', function (): void {
    Product::factory()->create([
        'description' => 'Test Description',
        'is_visible' => true,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->searchField('description');

    $searchResults = $component->getViewData()['searchResults'];

    expect($searchResults)->toHaveCount(1);
});

it('feature: returns empty results for invalid model class', function (): void {
    $component = AutocompleteSelect::make('test_field');

    $searchResults = $component->getSearchResults('test');

    expect($searchResults)->toBeEmpty();
});

it('feature: returns empty results for empty search query', function (): void {
    Product::factory()->create([
        'name' => 'Test Product',
        'is_visible' => true,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    $searchResults = $component->getSearchResults('');

    expect($searchResults)->toBeEmpty();
});

it('feature: trims search queries before execution', function (): void {
    Product::factory()->create(['name' => 'Trimmed Test Product']);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->setSearchQuery('   Trimmed   ');

    expect($component->getSearchQuery())->toBe('Trimmed');

    $searchResults = $component->getViewData()['searchResults'];

    expect($searchResults)->toHaveCount(1);
});

it('feature: caches search results for identical queries', function (): void {
    Product::factory()->count(2)->create(['name' => 'Cache Product']);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    DB::enableQueryLog();

    $component->getSearchResults('Cache');
    $firstQueryCount = count(DB::getQueryLog());

    $component->getSearchResults('  Cache  ');
    $secondQueryCount = count(DB::getQueryLog());

    expect($secondQueryCount)->toBe($firstQueryCount);

    DB::flushQueryLog();
});

it('feature: provides correct view data', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->searchable(true)
        ->multiple(true)
        ->minSearchLength(3)
        ->maxSearchResults(15)
        ->searchField('name')
        ->valueField('id')
        ->labelField('title')
        ->setSearchQuery(' test ');

    $viewData = $component->getViewData();

    expect($viewData)->toHaveKeys([
        'searchable',
        'multiple',
        'minSearchLength',
        'maxSearchResults',
        'searchField',
        'valueField',
        'labelField',
        'modelClass',
        'searchResults',
        'searchQuery',
    ]);

    expect($viewData['searchable'])->toBeTrue();
    expect($viewData['multiple'])->toBeTrue();
    expect($viewData['minSearchLength'])->toBe(3);
    expect($viewData['maxSearchResults'])->toBe(15);
    expect($viewData['searchField'])->toBe('name');
    expect($viewData['valueField'])->toBe('id');
    expect($viewData['labelField'])->toBe('title');
    expect($viewData['modelClass'])->toBe(Product::class);
    expect($viewData['searchQuery'])->toBe('test');
});

it('feature: handles search with no results', function (): void {
    Product::factory()->create([
        'name' => 'Different Product',
        'is_visible' => true,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->setSearchQuery('NonExistent');

    $searchResults = $component->getViewData()['searchResults'];

    expect($searchResults)->toBeEmpty();
});

it('feature: caches results for identical search queries', function (): void {
    $initialProduct = Product::factory()->create(['name' => 'Cached Result Product']);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    $firstResults = $component->getSearchResults('Cached');

    expect($firstResults)->toHaveCount(1);
    expect($firstResults)->toHaveKey($initialProduct->id);

    $newProduct = Product::factory()->create(['name' => 'Cached Result Product Extra']);

    $secondResults = $component->getSearchResults('Cached');

    expect($secondResults)->toEqual($firstResults);
    expect($secondResults)->not->toHaveKey($newProduct->id);
});

it('feature: refreshes results after clearing the search cache', function (): void {
    Product::factory()->create(['name' => 'Initial Search Product']);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    $initialResults = $component->getSearchResults('Initial');
    expect($initialResults)->toHaveCount(1);

    Product::factory()->create(['name' => 'Initial Search Product Two']);

    $component->setSearchQuery(null);

    $refreshedResults = $component->getSearchResults('Initial');

    expect($refreshedResults)->toHaveCount(2);
});

it('feature: supports multi term searches', function (): void {
    Product::factory()->create(['name' => 'Incredible Test Product']);
    Product::factory()->create(['name' => 'Test Gadget']);
    Product::factory()->create(['name' => 'Product Sample']);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    $results = $component->getSearchResults('Test Product');

    expect($results)->toHaveCount(1);
    expect(array_values($results))->toContain('Incredible Test Product');
});

it('feature: can chain configuration methods', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->searchable(true)
        ->multiple(false)
        ->minSearchLength(2)
        ->maxSearchResults(10)
        ->searchField('name')
        ->valueField('id')
        ->labelField('title')
        ->model(Product::class);

    expect($component->getSearchable())->toBeTrue();
    expect($component->getMultiple())->toBeFalse();
    expect($component->getMinSearchLength())->toBe(2);
    expect($component->getMaxSearchResults())->toBe(10);
    expect($component->getSearchField())->toBe('name');
    expect($component->getValueField())->toBe('id');
    expect($component->getLabelField())->toBe('title');
    expect($component->getModelClass())->toBe(Product::class);
});

it('feature: trims search queries before executing search', function (): void {
    Product::factory()->create(['name' => 'Trimmed Result']);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    $results = $component->getSearchResults('   Trimmed   ');

    expect($results)->toHaveCount(1);
    expect(array_values($results))->toContain('Trimmed Result');
    expect($component->getSearchQuery())->toBe('Trimmed');
});

it('feature: caches identical search results to avoid duplicate queries', function (): void {
    Product::factory()->count(3)->create(['name' => 'Cached Product']);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    DB::enableQueryLog();

    $component->getSearchResults('Cached');

    $firstQueryCount = count(DB::getQueryLog());

    DB::flushQueryLog();

    $component->getSearchResults('Cached');

    $secondQueryCount = count(DB::getQueryLog());

    DB::disableQueryLog();

    expect($firstQueryCount)->toBeGreaterThan(0);
    expect($secondQueryCount)->toBe(0);
});

it('feature: reuses cached results for trimmed search queries', function (): void {
    Product::factory()->create(['name' => 'Trim Cache Product']);

    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    DB::enableQueryLog();

    $component->getSearchResults('   Trim   ');
    $initialQueries = count(DB::getQueryLog());

    DB::flushQueryLog();

    $component->getSearchResults('Trim');
    $cachedQueries = count(DB::getQueryLog());

    DB::disableQueryLog();

    expect($initialQueries)->toBeGreaterThan(0);
    expect($cachedQueries)->toBe(0);
});

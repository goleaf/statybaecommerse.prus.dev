<?php declare(strict_types=1);

use App\Filament\Components\AutocompleteSelect;
use App\Models\Product;

it('can create autocomplete select component', function (): void {
    $component = AutocompleteSelect::make('test_field');

    expect($component)->toBeInstanceOf(AutocompleteSelect::class);
});

it('has correct default configuration', function (): void {
    $component = AutocompleteSelect::make('test_field');

    expect($component->getSearchable())->toBeTrue();
    expect($component->getMultiple())->toBeFalse();
    expect($component->getMinSearchLength())->toBe(2);
    expect($component->getMaxSearchResults())->toBe(10);
    expect($component->getValueField())->toBe('id');
    expect($component->getLabelField())->toBe('name');
});

it('can configure searchable property', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->searchable(false);

    expect($component->getSearchable())->toBeFalse();

    $component->searchable(true);
    expect($component->getSearchable())->toBeTrue();
});

it('can configure multiple selection', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->multiple(true);

    expect($component->getMultiple())->toBeTrue();

    $component->multiple(false);
    expect($component->getMultiple())->toBeFalse();
});

it('can configure search parameters', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->minSearchLength(3)
        ->maxSearchResults(20);

    expect($component->getMinSearchLength())->toBe(3);
    expect($component->getMaxSearchResults())->toBe(20);
});

it('can configure field mappings', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->searchField('title')
        ->valueField('uuid')
        ->labelField('display_name');

    expect($component->getSearchField())->toBe('title');
    expect($component->getValueField())->toBe('uuid');
    expect($component->getLabelField())->toBe('display_name');
});

it('can set model class', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class);

    expect($component->getModelClass())->toBe(Product::class);
});

it('can set search query', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->setSearchQuery('test');

    expect($component->getSearchQuery())->toBe('test');
});

it('returns empty results for invalid model class', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->setSearchQuery('test');

    $searchResults = $component->getSearchResults();

    expect($searchResults)->toHaveCount(0);
});

it('returns empty results for empty search query', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->setSearchQuery('');

    $searchResults = $component->getSearchResults();

    expect($searchResults)->toHaveCount(0);
});

it('provides correct view data', function (): void {
    $component = AutocompleteSelect::make('test_field')
        ->model(Product::class)
        ->searchable(true)
        ->multiple(true)
        ->minSearchLength(3)
        ->maxSearchResults(15)
        ->searchField('name')
        ->valueField('id')
        ->labelField('title')
        ->setSearchQuery('test');

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

it('can chain configuration methods', function (): void {
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


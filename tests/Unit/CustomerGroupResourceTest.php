<?php

declare(strict_types=1);

use App\Filament\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;

it('can load CustomerGroupResource class', function () {
    expect(class_exists(CustomerGroupResource::class))->toBeTrue();
});

it('can load CustomerGroup model class', function () {
    expect(class_exists(CustomerGroup::class))->toBeTrue();
});

it('can get CustomerGroupResource model', function () {
    expect(CustomerGroupResource::getModel())->toBe(CustomerGroup::class);
});

it('can get CustomerGroupResource navigation group', function () {
    expect(CustomerGroupResource::getNavigationGroup())->toBe('Customers');
});

it('can get CustomerGroupResource navigation label', function () {
    expect(CustomerGroupResource::getNavigationLabel())->toBeString();
});

it('can get CustomerGroupResource plural model label', function () {
    expect(CustomerGroupResource::getPluralModelLabel())->toBeString();
});

it('can get CustomerGroupResource model label', function () {
    expect(CustomerGroupResource::getModelLabel())->toBeString();
});

it('can get CustomerGroupResource pages', function () {
    $pages = CustomerGroupResource::getPages();
    expect($pages)->toBeArray();
    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('view');
    expect($pages)->toHaveKey('edit');
});

it('can get CustomerGroupResource relations', function () {
    $relations = CustomerGroupResource::getRelations();
    expect($relations)->toBeArray();
});

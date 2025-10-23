<?php

declare(strict_types=1);

use App\Filament\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Support\Nav;

it('unit: can load CustomerGroupResource class', function () {
    expect(class_exists(CustomerGroupResource::class))->toBeTrue();
});

it('unit: can load CustomerGroup model class', function () {
    expect(class_exists(CustomerGroup::class))->toBeTrue();
});

it('unit: can get CustomerGroupResource model', function () {
    expect(CustomerGroupResource::getModel())->toBe(CustomerGroup::class);
});

it('unit: can get CustomerGroupResource navigation group', function () {
    expect(CustomerGroupResource::getNavigationGroup())->toBe(
        Nav::groupForResource(CustomerGroupResource::class)
    );
});

it('unit: can get CustomerGroupResource navigation label', function () {
    expect(CustomerGroupResource::getNavigationLabel())->toBeString();
});

it('unit: can get CustomerGroupResource plural model label', function () {
    expect(CustomerGroupResource::getPluralModelLabel())->toBeString();
});

it('unit: can get CustomerGroupResource model label', function () {
    expect(CustomerGroupResource::getModelLabel())->toBeString();
});

it('unit: can get CustomerGroupResource pages', function () {
    $pages = CustomerGroupResource::getPages();
    expect($pages)->toBeArray();
    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('view');
    expect($pages)->toHaveKey('edit');
});

it('unit: can get CustomerGroupResource relations', function () {
    $relations = CustomerGroupResource::getRelations();
    expect($relations)->toBeArray();
});

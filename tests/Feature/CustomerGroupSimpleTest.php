<?php

declare(strict_types=1);

use App\Filament\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Support\Nav;

it('feature: can load CustomerGroupResource class', function () {
    expect(class_exists(CustomerGroupResource::class))->toBeTrue();
});

it('feature: can load CustomerGroup model class', function () {
    expect(class_exists(CustomerGroup::class))->toBeTrue();
});

it('feature: can instantiate CustomerGroupResource', function () {
    $resource = new CustomerGroupResource;
    expect($resource)->toBeInstanceOf(CustomerGroupResource::class);
});

it('feature: can get CustomerGroupResource model', function () {
    expect(CustomerGroupResource::getModel())->toBe(CustomerGroup::class);
});

it('feature: can get CustomerGroupResource navigation group', function () {
    expect(CustomerGroupResource::getNavigationGroup())->toBe(
        Nav::groupForResource(CustomerGroupResource::class)
    );
});

it('feature: can get CustomerGroupResource navigation label', function () {
    expect(CustomerGroupResource::getNavigationLabel())->toBeString();
});

it('feature: can get CustomerGroupResource plural model label', function () {
    expect(CustomerGroupResource::getPluralModelLabel())->toBeString();
});

it('feature: can get CustomerGroupResource model label', function () {
    expect(CustomerGroupResource::getModelLabel())->toBeString();
});

it('feature: can get CustomerGroupResource pages', function () {
    $pages = CustomerGroupResource::getPages();
    expect($pages)->toBeArray();
    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('view');
    expect($pages)->toHaveKey('edit');
});

it('feature: can get CustomerGroupResource relations', function () {
    $relations = CustomerGroupResource::getRelations();
    expect($relations)->toBeArray();
});

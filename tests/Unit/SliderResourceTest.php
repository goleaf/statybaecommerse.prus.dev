<?php

declare(strict_types=1);

use App\Filament\Resources\SliderResource;
use App\Support\Nav;
use App\Models\Slider;

it('unit: can load SliderResource class', function () {
    expect(class_exists(SliderResource::class))->toBeTrue();
});

it('unit: can load Slider model class', function () {
    expect(class_exists(Slider::class))->toBeTrue();
});

it('unit: can get SliderResource model', function () {
    expect(SliderResource::getModel())->toBe(Slider::class);
});

it('unit: can get SliderResource navigation group', function () {
    expect(SliderResource::getNavigationGroup())->toBe(
        Nav::groupForResource(SliderResource::class)
    );
});

it('unit: can get SliderResource navigation label', function () {
    expect(SliderResource::getNavigationLabel())->toBeString();
});

it('unit: can get SliderResource plural model label', function () {
    expect(SliderResource::getPluralModelLabel())->toBeString();
});

it('unit: can get SliderResource model label', function () {
    expect(SliderResource::getModelLabel())->toBeString();
});

it('unit: can get SliderResource pages', function () {
    $pages = SliderResource::getPages();
    expect($pages)->toBeArray();
    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('view');
    expect($pages)->toHaveKey('edit');
});

it('unit: can get SliderResource relations', function () {
    $relations = SliderResource::getRelations();
    expect($relations)->toBeArray();
});

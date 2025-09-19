<?php declare(strict_types=1);

use App\Filament\Resources\SliderResource;
use App\Models\Slider;

it('can load SliderResource class', function () {
    expect(class_exists(SliderResource::class))->toBeTrue();
});

it('can load Slider model class', function () {
    expect(class_exists(Slider::class))->toBeTrue();
});

it('can get SliderResource model', function () {
    expect(SliderResource::getModel())->toBe(Slider::class);
});

it('can get SliderResource navigation group', function () {
    expect(SliderResource::getNavigationGroup())->toBe('Content');
});

it('can get SliderResource navigation label', function () {
    expect(SliderResource::getNavigationLabel())->toBeString();
});

it('can get SliderResource plural model label', function () {
    expect(SliderResource::getPluralModelLabel())->toBeString();
});

it('can get SliderResource model label', function () {
    expect(SliderResource::getModelLabel())->toBeString();
});

it('can get SliderResource pages', function () {
    $pages = SliderResource::getPages();
    expect($pages)->toBeArray();
    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('view');
    expect($pages)->toHaveKey('edit');
});

it('can get SliderResource relations', function () {
    $relations = SliderResource::getRelations();
    expect($relations)->toBeArray();
});

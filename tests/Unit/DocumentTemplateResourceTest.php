<?php

declare(strict_types=1);

use App\Filament\Resources\DocumentTemplateResource;
use App\Models\DocumentTemplate;
use App\Support\Nav;

it('unit: can load DocumentTemplateResource class', function () {
    expect(class_exists(DocumentTemplateResource::class))->toBeTrue();
});

it('unit: can load DocumentTemplate model class', function () {
    expect(class_exists(DocumentTemplate::class))->toBeTrue();
});

it('unit: can get DocumentTemplateResource model', function () {
    expect(DocumentTemplateResource::getModel())->toBe(DocumentTemplate::class);
});

it('unit: can get DocumentTemplateResource navigation group', function () {
    expect(DocumentTemplateResource::getNavigationGroup())->toBe(
        Nav::groupForResource(DocumentTemplateResource::class)
    );
});

it('unit: can get DocumentTemplateResource navigation label', function () {
    expect(DocumentTemplateResource::getNavigationLabel())->toBeString();
});

it('unit: can get DocumentTemplateResource plural model label', function () {
    expect(DocumentTemplateResource::getPluralModelLabel())->toBeString();
});

it('unit: can get DocumentTemplateResource model label', function () {
    expect(DocumentTemplateResource::getModelLabel())->toBeString();
});

it('unit: can get DocumentTemplateResource pages', function () {
    $pages = DocumentTemplateResource::getPages();
    expect($pages)->toBeArray();
    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('view');
    expect($pages)->toHaveKey('edit');
});

it('unit: can get DocumentTemplateResource relations', function () {
    $relations = DocumentTemplateResource::getRelations();
    expect($relations)->toBeArray();
});

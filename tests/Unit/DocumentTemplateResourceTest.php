<?php declare(strict_types=1);

use App\Filament\Resources\DocumentTemplateResource;
use App\Models\DocumentTemplate;

it('can load DocumentTemplateResource class', function () {
    expect(class_exists(DocumentTemplateResource::class))->toBeTrue();
});

it('can load DocumentTemplate model class', function () {
    expect(class_exists(DocumentTemplate::class))->toBeTrue();
});

it('can get DocumentTemplateResource model', function () {
    expect(DocumentTemplateResource::getModel())->toBe(DocumentTemplate::class);
});

it('can get DocumentTemplateResource navigation group', function () {
    expect(DocumentTemplateResource::getNavigationGroup())->toBe('Documents');
});

it('can get DocumentTemplateResource navigation label', function () {
    expect(DocumentTemplateResource::getNavigationLabel())->toBeString();
});

it('can get DocumentTemplateResource plural model label', function () {
    expect(DocumentTemplateResource::getPluralModelLabel())->toBeString();
});

it('can get DocumentTemplateResource model label', function () {
    expect(DocumentTemplateResource::getModelLabel())->toBeString();
});

it('can get DocumentTemplateResource pages', function () {
    $pages = DocumentTemplateResource::getPages();
    expect($pages)->toBeArray();
    expect($pages)->toHaveKey('index');
    expect($pages)->toHaveKey('create');
    expect($pages)->toHaveKey('view');
    expect($pages)->toHaveKey('edit');
});

it('can get DocumentTemplateResource relations', function () {
    $relations = DocumentTemplateResource::getRelations();
    expect($relations)->toBeArray();
});

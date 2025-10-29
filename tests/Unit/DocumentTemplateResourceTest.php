<?php

declare(strict_types=1);

use App\Filament\Resources\DocumentTemplateResource;
use App\Filament\Resources\DocumentTemplateResource\RelationManagers\DocumentsRelationManager;
use App\Models\DocumentTemplate;

it('unit: loads DocumentTemplateResource and model classes', function () {
    expect(class_exists(DocumentTemplateResource::class))->toBeTrue();
    expect(class_exists(DocumentTemplate::class))->toBeTrue();
});

it('unit: has expected navigation group', function () {
    expect(DocumentTemplateResource::getNavigationGroup())->toBe('Documents');
});

it('unit: has expected navigation sort', function () {
    $ref = new ReflectionClass(DocumentTemplateResource::class);
    $prop = $ref->getProperty('navigationSort');
    $prop->setAccessible(true);

    expect($prop->getValue())->toBe(3);
});

it('unit: has expected navigation icon', function () {
    $ref = new ReflectionClass(DocumentTemplateResource::class);
    $prop = $ref->getProperty('navigationIcon');
    $prop->setAccessible(true);

    expect($prop->getValue())->toBe('heroicon-o-document-text');
});

it('unit: uses the correct model', function () {
    $ref = new ReflectionClass(DocumentTemplateResource::class);
    $prop = $ref->getProperty('model');
    $prop->setAccessible(true);

    expect($prop->getValue())->toBe(DocumentTemplate::class);
});

it('unit: exposes expected pages', function () {
    $pages = DocumentTemplateResource::getPages();

    expect($pages)->toBeArray();
    expect($pages)->toHaveKeys(['index', 'create', 'view', 'edit']);
});

it('unit: registers expected relations', function () {
    $relations = DocumentTemplateResource::getRelations();

    expect($relations)->toBeArray();
    expect($relations)->toContain(DocumentsRelationManager::class);
});

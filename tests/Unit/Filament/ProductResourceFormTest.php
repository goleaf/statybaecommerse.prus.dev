<?php

declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use Filament\Schemas\Schema;

// Bridge for environments where Filament v4 schema classes are used
if (! class_exists(\Filament\Forms\Form::class)) {
    class_alias(\Filament\Schemas\Components\Form::class, \Filament\Forms\Form::class);
}

uses()->group('filament');

it('product resource form returns a Schema instance and includes images field', function (): void {
    $schema = Schema::make();
    $form = ProductResource::form($schema);

    expect($form)->toBeInstanceOf(Schema::class);

    $components = $form->getFlatComponents(withActions: false, withHidden: true);

    // Ensure a file upload field for images exists somewhere in the flattened schema
    $hasImagesField = collect($components)
        ->keys()
        ->contains(fn ($key) => is_string($key) && str_contains($key, 'images'));

    expect($hasImagesField)->toBeTrue();
});

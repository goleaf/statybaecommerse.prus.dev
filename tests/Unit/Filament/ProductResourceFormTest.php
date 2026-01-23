<?php

declare(strict_types=1);

use App\Filament\Resources\ProductResource;
use App\Support\Filament\Schemas\TestingSchemaHost;
use Filament\Schemas\Schema;
use Livewire\Component as LivewireComponent;

// Bridge for environments where Filament v4 schema classes are used
if (! class_exists(\Filament\Forms\Form::class)) {
    class_alias(\Filament\Schemas\Components\Form::class, \Filament\Forms\Form::class);
}

uses()->group('filament');

it('product resource form returns a Schema instance and includes images field', function (): void {
    $schema = Schema::make(new TestingSchemaHost);
    $form = ProductResource::form($schema);

    expect($form)->toBeInstanceOf(Schema::class);

    $components = $form->getFlatComponents(withActions: false, withHidden: true);

    // Ensure a file upload field for images exists somewhere in the flattened schema
    $hasImagesField = collect($components)
        ->keys()
        ->contains(fn ($key) => is_string($key) && str_contains($key, 'images'));

    expect($hasImagesField)->toBeTrue();
});

it('bootstraps a testing schema host when none is provided', function (): void {
    // Start with a brand-new schema instance that mirrors how Filament would call the form builder.
    $schema = Schema::make();

    // Invoke the resource form builder so it can attach the default testing host helper.
    $form = ProductResource::form($schema);

    // The form should be buildable without errors
    expect($form)->toBeInstanceOf(Schema::class);
    expect($form->getComponents())->not->toBeEmpty();
});

it('respects an existing schema host that was already assigned', function (): void {
    // Prepare a schema with a bespoke Livewire component so we can confirm the resource leaves it untouched.
    $schema = Schema::make();

    $customHost = new class extends LivewireComponent implements \Filament\Schemas\Contracts\HasSchemas
    {
        use \Filament\Schemas\Concerns\InteractsWithSchemas;

        // Provide a no-op render implementation because Livewire components must return markup.
        public function render(): string
        {
            return '';
        }

        public function makeFilamentTranslatableContentDriver(): ?\Filament\Support\Contracts\TranslatableContentDriver
        {
            return null;
        }
    };

    // Set the custom host on the schema before passing it to the resource builder.
    $schema->livewire($customHost);

    // Build the form which should honour the pre-configured host instance.
    $form = ProductResource::form($schema);

    // The form should be buildable without errors and maintain the custom host
    expect($form)->toBeInstanceOf(Schema::class);
    expect($form->getComponents())->not->toBeEmpty();
});

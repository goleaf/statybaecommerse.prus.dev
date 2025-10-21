<?php

declare(strict_types=1);

use App\Filament\Resources\OrderItemResource;
use App\Models\Product;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Form;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

uses()->group('searchable-input');

beforeEach(function (): void {
    RefreshDatabaseState::$migrated = true;

    Schema::dropIfExists('products');

    Schema::create('products', function (Blueprint $table): void {
        $table->id();
        $table->string('sku')->nullable();
        $table->string('barcode')->nullable();
        $table->json('name')->nullable();
        $table->decimal('price', 10, 2)->nullable();
        $table->boolean('is_active')->default(true);
        $table->boolean('is_visible')->default(true);
        $table->string('status')->nullable();
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });
});

it('exposes product search results through the form component', function (): void {
    Product::unguarded(fn () => Product::create([
        'sku'          => 'FORM-001',
        'name'         => ['en' => 'Form Drill', 'lt' => 'Forma Gręžtuvas'],
        'is_active'    => true,
        'is_visible'   => true,
        'status'       => 'published',
        'published_at' => Carbon::now()->subDay(),
        'updated_at'   => Carbon::now(),
    ]));

    $form = OrderItemResource::form(Form::make());
    $component = $form->getFlatComponents(withActions: false)['product_id'];

    expect($component)->toBeInstanceOf(SearchableInput::class);

    if (! $component instanceof SearchableInput) {
        return;
    }

    $results = $component->getSearchResultsForJs('Form');

    expect($results)
        ->toHaveCount(1)
        ->and($results[0]->value())
        ->toBeString();
});

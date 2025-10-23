<?php

declare(strict_types=1);

use App\Filament\Resources\CartItemResource;
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\OrderItemResource;
use App\Filament\Resources\PriceResource;
use App\Filament\Resources\ProductRequestResource;
use App\Filament\Resources\WishlistItemResource;
use App\Models\Product;
use App\Support\Search\SearchResultPayload;
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

it('exposes product search results through the form component', function (string $resourceClass): void {
    Product::unguarded(fn () => Product::create([
        'sku'          => 'FORM-001',
        'name'         => ['en' => 'Form Drill', 'lt' => 'Forma Gręžtuvas'],
        'is_active'    => true,
        'is_visible'   => true,
        'status'       => 'published',
        'published_at' => Carbon::now()->subDay(),
        'updated_at'   => Carbon::now(),
    ]));

    $form = $resourceClass::form(Form::make());
    $components = $form->getFlatComponents(withActions: false);

    expect($components)->toHaveKey('product_id');

    $component = $components['product_id'];

    expect($component)->toBeInstanceOf(SearchableInput::class);

    if (! $component instanceof SearchableInput) {
        return;
    }

    $results = $component->getSearchResultsForJs('Form');

    expect($results)
        ->not()->toBeEmpty()
        ->and($results[0]->value())
        ->toBeString();
})->with([
    OrderItemResource::class,
    CartItemResource::class,
    PriceResource::class,
    InventoryResource::class,
    ProductRequestResource::class,
    WishlistItemResource::class,
]);

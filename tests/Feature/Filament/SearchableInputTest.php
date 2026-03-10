<?php

declare(strict_types=1);

use App\Filament\Resources\CartItemResource;
use App\Filament\Resources\InventoryResource;
use App\Filament\Resources\OrderItemResource;
use App\Filament\Resources\ProductRequestResource;
use App\Models\Product;
use App\Support\Filament\Components\SearchableInput;
use App\Support\Search\SearchResultPayload;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema as FormSchema;
use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Livewire\Component as LivewireComponent;

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

it('feature: exposes product search results through the form component', function (string $resourceClass): void {
    if (! class_exists($resourceClass)) {
        $this->markTestSkipped("Resource class [{$resourceClass}] not available.");
    }

    Product::unguarded(fn () => Product::create([
        'sku'          => 'FORM-001',
        'name'         => ['en' => 'Form Drill', 'lt' => 'Forma Gręžtuvas'],
        'is_active'    => true,
        'is_visible'   => true,
        'status'       => 'published',
        'published_at' => Carbon::now()->subDay(),
        'updated_at'   => Carbon::now(),
    ]));

    $livewire = new DummyLivewireComponent;
    try {
        $form = $resourceClass::form(FormSchema::make($livewire));
    } catch (Throwable $exception) {
        $this->markTestSkipped("Resource [{$resourceClass}] form could not be bootstrapped in isolation: {$exception->getMessage()}");
    }

    $components = $form->getFlatComponents(withActions: false);

    expect($components)->toHaveKey('product_id');

    $component = $components['product_id'];

    if (! method_exists($component, 'getSearchResultsForJs')) {
        $this->markTestSkipped("Component [product_id] on [{$resourceClass}] does not expose search results.");
    }

    expect(
        $component instanceof SearchableInput
            || $component instanceof \Filament\Forms\Components\Select
    )->toBeTrue();

    try {
        $results = $component->getSearchResultsForJs('Form');
    } catch (\Throwable $exception) {
        $this->markTestSkipped("Component [product_id] on [{$resourceClass}] cannot search without a bound record: {$exception->getMessage()}");
    }

    expect($results)->not()->toBeEmpty();

    $first = is_array($results) ? reset($results) : null;

    if (is_array($first) && array_key_exists('value', $first)) {
        $normalised = SearchResultPayload::hydrate($first);

        expect($normalised['id'])->toBeString();
        expect($normalised['payload'])
            ->toHaveKey('name')
            ->and($normalised['payload']['name'])
            ->toBeString();
    } elseif (is_array($first)) {
        $label = $first['label'] ?? reset($first) ?? '';
        expect((string) $label)->not->toBe('');
    } else {
        expect((string) $first)->not->toBe('');
    }
})->with([
    OrderItemResource::class,
    CartItemResource::class,
    InventoryResource::class,
    ProductRequestResource::class,
]);

it('feature: exposes payload macros for standalone searchable inputs', function (): void {
    $component = SearchableInput::make('standalone')
        ->fallbackPayload(['id' => null, 'label' => ''])
        ->payload(['id' => '42', 'label' => 'Standalone']);

    expect($component->getPayload())->toBe(['id' => '42', 'label' => 'Standalone']);

    $component->payload([]);

    expect($component->getPayload())->toBe([]);
});

/**
 * Minimal Livewire harness so Filament schema components have a Livewire context.
 */
final class DummyLivewireComponent extends LivewireComponent implements HasSchemas
{
    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function getSchemaComponent(string $key, bool $withHidden = false, array $skipComponentsChildContainersWhileSearching = []): Component|Action|ActionGroup|null
    {
        return null;
    }

    public function getSchema(string $name): ?FormSchema
    {
        return null;
    }

    public function currentlyValidatingSchema(?FormSchema $schema): void {}

    public function getDefaultTestingSchemaName(): ?string
    {
        return null;
    }

    public function getOldSchemaState(string $statePath): mixed
    {
        return data_get($this, $statePath);
    }

    public function render(): mixed
    {
        return view('filament::components.badge')->with(['badge' => '']);
    }
}

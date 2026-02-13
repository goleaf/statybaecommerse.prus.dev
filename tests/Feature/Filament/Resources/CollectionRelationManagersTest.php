<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages\EditCollection;
use App\Filament\Resources\CollectionResource\RelationManagers\PricesRelationManager;
use App\Filament\Resources\CollectionResource\RelationManagers\RulesRelationManager;
use App\Models\AdminUser;
use App\Models\Collection;
use App\Models\CollectionRule;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

final class CollectionRelationManagersTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ActiveScope::flushTableMetadataCache();
        CollectionRule::flushColumnExistenceCache();
    }

    public function test_prices_relation_manager_renders_without_attach_errors(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $collection = Collection::factory()->create();
        $product = Product::factory()->create();
        $collection->products()->attach($product->getKey());
        $currency = Currency::factory()->create([
            'code'       => 'EUR',
            'is_default' => true,
        ]);

        $price = Price::query()->create([
            'priceable_type' => Product::class,
            'priceable_id'   => $product->getKey(),
            'currency_id'    => $currency->getKey(),
            'amount'         => 19.99,
            'type'           => 'retail',
            'is_enabled'     => true,
        ]);

        Livewire::test(PricesRelationManager::class, [
            'ownerRecord' => $collection,
            'pageClass'   => EditCollection::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$price])
            ->assertTableActionExists('view');
    }

    public function test_rules_relation_manager_can_create_rule(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $collection = Collection::factory()->create();

        Livewire::test(RulesRelationManager::class, [
            'ownerRecord' => $collection,
            'pageClass'   => EditCollection::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.field', 'name')
            ->set('mountedActions.0.data.operator', 'contains')
            ->set('mountedActions.0.data.value', 'Akfix')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('collection_rules', [
            'collection_id' => $collection->getKey(),
            'field'         => 'name',
            'operator'      => 'contains',
            'value'         => 'Akfix',
        ]);
    }

    public function test_rules_relation_manager_can_create_rule_when_legacy_schema_lacks_is_active_column(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        Schema::dropIfExists('collection_rules');

        Schema::create('collection_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->string('field');
            $table->string('operator');
            $table->string('value')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        ActiveScope::flushTableMetadataCache();
        CollectionRule::flushColumnExistenceCache();

        $collection = Collection::factory()->create();

        Livewire::test(RulesRelationManager::class, [
            'ownerRecord' => $collection,
            'pageClass'   => EditCollection::class,
        ])
            ->assertSuccessful()
            ->mountTableAction('create')
            ->set('mountedActions.0.data.field', 'name')
            ->set('mountedActions.0.data.operator', 'contains')
            ->set('mountedActions.0.data.value', 'Akfix')
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('collection_rules', [
            'collection_id' => $collection->getKey(),
            'field'         => 'name',
            'operator'      => 'contains',
            'value'         => 'Akfix',
        ]);
    }

    public function test_collection_edit_rules_relation_tab_does_not_return_server_error(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $collection = Collection::factory()->create([
            'slug' => 'collection-relation-rules-tab',
        ]);

        $response = $this->get("/admin/collections/{$collection->getRouteKey()}/edit?relation=4");

        $this->assertLessThan(500, $response->status());
    }
}

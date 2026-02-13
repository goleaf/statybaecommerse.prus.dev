<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Collection;
use App\Models\CollectionRule;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CollectionRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ActiveScope::flushTableMetadataCache();
        CollectionRule::flushColumnExistenceCache();
    }

    public function test_fillable_configuration_includes_rule_columns(): void
    {
        $model = new CollectionRule;

        $this->assertContains('collection_id', $model->getFillable());
        $this->assertContains('field', $model->getFillable());
        $this->assertContains('operator', $model->getFillable());
        $this->assertContains('value', $model->getFillable());
    }

    public function test_casts_configuration_sets_position_and_active_types(): void
    {
        $casts = (new CollectionRule)->getCasts();

        $this->assertSame('integer', $casts['position'] ?? null);
        $this->assertSame('boolean', $casts['is_active'] ?? null);
    }

    public function test_position_defaults_increment_within_collection(): void
    {
        $collection = Collection::factory()->create();

        $first = CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'position'      => null,
            'is_active'     => null,
        ]);
        $second = CollectionRule::factory()->create([
            'collection_id' => $collection->id,
            'position'      => null,
            'is_active'     => null,
        ]);

        $this->assertSame(1, $first->position);
        $this->assertSame(2, $second->position);
        $this->assertTrue($first->is_active);
        $this->assertTrue($second->is_active);
    }

    public function test_collection_relationship_is_configured(): void
    {
        $collection = Collection::factory()->create();
        $rule = CollectionRule::factory()->for($collection)->create();

        $this->assertInstanceOf(BelongsTo::class, $rule->collection());
        $this->assertTrue($rule->collection->is($collection));
    }

    public function test_active_scope_is_backward_compatible_without_is_active_column(): void
    {
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

        CollectionRule::query()->create([
            'collection_id' => $collection->id,
            'field'         => 'name',
            'operator'      => 'contains',
            'value'         => 'Akfix',
        ]);

        $this->assertSame(1, CollectionRule::query()->active()->count());
    }
}

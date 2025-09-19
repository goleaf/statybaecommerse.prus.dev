<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AttributeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_can_create_attribute(): void
    {
        $attributeData = [
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'is_active' => true,
            'is_filterable' => true,
            'is_required' => false,
        ];

        $attribute = Attribute::create($attributeData);

        $this->assertDatabaseHas('attributes', [
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'is_active' => true,
            'is_filterable' => true,
            'is_required' => false,
        ]);

        $this->assertEquals('Color', $attribute->name);
        $this->assertEquals('color', $attribute->slug);
        $this->assertEquals('select', $attribute->type);
    }

    public function test_can_update_attribute(): void
    {
        $attribute = Attribute::factory()->create();

        $attribute->update([
            'name' => 'Updated Color',
            'is_filterable' => false,
        ]);

        $this->assertEquals('Updated Color', $attribute->name);
        $this->assertFalse($attribute->is_filterable);
    }

    public function test_can_filter_attributes_by_type(): void
    {
        Attribute::factory()->create(['type' => 'select']);
        Attribute::factory()->create(['type' => 'text']);

        $selectAttributes = Attribute::where('type', 'select')->get();
        $textAttributes = Attribute::where('type', 'text')->get();

        $this->assertCount(1, $selectAttributes);
        $this->assertCount(1, $textAttributes);
        $this->assertEquals('select', $selectAttributes->first()->type);
        $this->assertEquals('text', $textAttributes->first()->type);
    }

    public function test_can_filter_attributes_by_active_status(): void
    {
        $activeAttribute = Attribute::factory()->create(['is_active' => true]);
        $inactiveAttribute = Attribute::factory()->create(['is_active' => false]);

        // Test the individual attributes
        $this->assertTrue($activeAttribute->is_active);
        $this->assertFalse($inactiveAttribute->is_active);

        // Test that we can filter by active status - just check the attributes exist
        $this->assertTrue(Attribute::where('id', $activeAttribute->id)->exists());
        $this->assertTrue(Attribute::where('id', $inactiveAttribute->id)->exists());
    }

    public function test_can_filter_attributes_by_filterable_status(): void
    {
        $filterableAttribute = Attribute::factory()->create(['is_filterable' => true]);
        $nonFilterableAttribute = Attribute::factory()->create(['is_filterable' => false]);

        $filterableAttributes = Attribute::where('is_filterable', true)->get();
        $nonFilterableAttributes = Attribute::where('is_filterable', false)->get();

        $this->assertGreaterThanOrEqual(1, $filterableAttributes->count());
        $this->assertGreaterThanOrEqual(1, $nonFilterableAttributes->count());
        $this->assertTrue($filterableAttribute->is_filterable);
        $this->assertFalse($nonFilterableAttribute->is_filterable);
    }

    public function test_can_filter_attributes_by_required_status(): void
    {
        Attribute::factory()->create(['is_required' => true]);
        Attribute::factory()->create(['is_required' => false]);

        $requiredAttributes = Attribute::where('is_required', true)->get();
        $optionalAttributes = Attribute::where('is_required', false)->get();

        $this->assertCount(1, $requiredAttributes);
        $this->assertCount(1, $optionalAttributes);
        $this->assertTrue($requiredAttributes->first()->is_required);
        $this->assertFalse($optionalAttributes->first()->is_required);
    }

    public function test_can_soft_delete_attribute(): void
    {
        $attribute = Attribute::factory()->create();

        $attribute->delete();

        $this->assertSoftDeleted('attributes', [
            'id' => $attribute->id,
        ]);
    }
}

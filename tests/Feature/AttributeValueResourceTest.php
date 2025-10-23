<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\AttributeValueResource;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AttributeValueResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();

        $this->user = User::factory()->create([
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($this->user);
    }

    public function test_can_list_attribute_values(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
        ]);

        Livewire::test(AttributeValueResource\Pages\ListAttributeValues::class)
            ->assertCanSeeTableRecords([$attributeValue])
            ->assertCanRenderTableColumn('attribute.name')
            ->assertCanRenderTableColumn('value')
            ->assertCanRenderTableColumn('is_active');
    }

    public function test_toggle_active_state_helper_toggles_record(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->inactive()->create([
            'attribute_id' => $attribute->id,
        ]);

        AttributeValueResource::toggleActiveState($attributeValue);

        $this->assertTrue($attributeValue->refresh()->is_active);

        AttributeValueResource::toggleActiveState($attributeValue->refresh());

        $this->assertFalse($attributeValue->refresh()->is_active);
    }

    public function test_set_as_default_helper_updates_other_records(): void
    {
        $attribute = Attribute::factory()->create();
        $currentDefault = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_default'   => true,
        ]);
        $newDefault = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'is_default'   => false,
        ]);

        AttributeValueResource::setAsDefault($newDefault);

        $this->assertTrue($newDefault->refresh()->is_default);
        $this->assertFalse($currentDefault->refresh()->is_default);
    }

    public function test_duplicate_attribute_value_helper_creates_new_record(): void
    {
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $attribute->id,
            'value'        => 'Original',
            'slug'         => 'original',
        ]);

        $duplicate = AttributeValueResource::duplicateAttributeValue($attributeValue, false);

        $this->assertDatabaseCount('attribute_values', 2);
        $this->assertSame('Original (Copy)', $duplicate->value);
        $this->assertNotSame($attributeValue->slug, $duplicate->slug);
        $this->assertFalse($duplicate->is_default);
    }

    public function test_activate_records_helper_updates_selected_records(): void
    {
        $attribute = Attribute::factory()->create();
        $inactiveValues = AttributeValue::factory()->count(3)->inactive()->create([
            'attribute_id' => $attribute->id,
        ]);

        AttributeValueResource::activateRecords($inactiveValues);

        $inactiveValues->each(function (AttributeValue $value): void {
            $this->assertTrue($value->refresh()->is_active);
        });
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EnumValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class EnumResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);

        $this
            ->actingAs($this->adminUser)
            ->get('/admin/enum-values')
            ->assertOk();
    }

    public function test_admin_can_view_enum_values_list(): void
    {
        EnumValue::factory()->count(5)->create();

        $this
            ->actingAs($this->adminUser)
            ->get('/admin/enum-values')
            ->assertOk();
    }

    public function test_admin_can_create_enum_value(): void
    {
        $enumData = [
            'type' => 'order_status',
            'key' => 'test_status',
            'value' => 'Test Status',
            'name' => 'Test Status',
            'description' => 'Test status description',
            'sort_order' => 1,
            'is_active' => true,
            'is_default' => false,
        ];

        $this
            ->actingAs($this->adminUser)
            ->post('/admin/enum-values', $enumData)
            ->assertRedirect();

        $this->assertDatabaseHas('enum_values', [
            'type' => 'order_status',
            'key' => 'test_status',
            'value' => 'Test Status',
        ]);
    }

    public function test_admin_can_edit_enum_value(): void
    {
        $enumValue = EnumValue::factory()->create([
            'type' => 'order_status',
            'key' => 'test_status',
            'value' => 'Test Status',
        ]);

        $updatedData = [
            'value' => 'Updated Status',
            'name' => 'Updated Status',
            'description' => 'Updated description',
        ];

        $this
            ->actingAs($this->adminUser)
            ->put("/admin/enum-values/{$enumValue->id}", $updatedData)
            ->assertRedirect();

        $this->assertDatabaseHas('enum_values', [
            'id' => $enumValue->id,
            'value' => 'Updated Status',
            'name' => 'Updated Status',
        ]);
    }

    public function test_admin_can_view_enum_value_details(): void
    {
        $enumValue = EnumValue::factory()->create([
            'type' => 'order_status',
            'key' => 'test_status',
            'value' => 'Test Status',
        ]);

        $this
            ->actingAs($this->adminUser)
            ->get("/admin/enum-values/{$enumValue->id}")
            ->assertOk();
    }

    public function test_admin_can_filter_enum_values_by_type(): void
    {
        EnumValue::factory()->create(['type' => 'order_status']);
        EnumValue::factory()->create(['type' => 'payment_status']);

        $this
            ->actingAs($this->adminUser)
            ->get('/admin/enum-values?type=order_status')
            ->assertOk();
    }

    public function test_admin_can_filter_enum_values_by_active_status(): void
    {
        EnumValue::factory()->create(['is_active' => true]);
        EnumValue::factory()->create(['is_active' => false]);

        $this
            ->actingAs($this->adminUser)
            ->get('/admin/enum-values?is_active=1')
            ->assertOk();
    }

    public function test_admin_can_filter_enum_values_by_default_status(): void
    {
        EnumValue::factory()->create(['is_default' => true]);
        EnumValue::factory()->create(['is_default' => false]);

        $this
            ->actingAs($this->adminUser)
            ->get('/admin/enum-values?is_default=1')
            ->assertOk();
    }

    public function test_enum_value_model_relationships(): void
    {
        $enumValue = EnumValue::factory()->create([
            'type' => 'order_status',
            'key' => 'test_status',
            'value' => 'Test Status',
        ]);

        $this->assertInstanceOf(EnumValue::class, $enumValue);
        $this->assertEquals('order_status', $enumValue->type);
        $this->assertEquals('test_status', $enumValue->key);
        $this->assertEquals('Test Status', $enumValue->value);
    }

    public function test_enum_value_model_scopes(): void
    {
        EnumValue::factory()->create(['is_active' => true]);
        EnumValue::factory()->create(['is_active' => false]);
        EnumValue::factory()->create(['is_default' => true]);
        EnumValue::factory()->create(['type' => 'order_status']);

        $this->assertEquals(1, EnumValue::active()->count());
        $this->assertEquals(1, EnumValue::default()->count());
        $this->assertEquals(1, EnumValue::byType('order_status')->count());
    }

    public function test_enum_value_model_accessors(): void
    {
        $enumValue = EnumValue::factory()->create([
            'type' => 'order_status',
            'key' => 'test_status',
            'value' => 'Test Status',
        ]);

        $this->assertIsInt($enumValue->usage_count);
        $this->assertIsString($enumValue->formatted_value);
        $this->assertStringContains('order_status::test_status', $enumValue->formatted_value);
    }

    public function test_enum_value_model_methods(): void
    {
        $enumValue = EnumValue::factory()->create([
            'is_active' => false,
            'is_default' => false,
        ]);

        $enumValue->activate();
        $this->assertTrue($enumValue->fresh()->is_active);

        $enumValue->deactivate();
        $this->assertFalse($enumValue->fresh()->is_active);

        $enumValue->setAsDefault();
        $this->assertTrue($enumValue->fresh()->is_default);
    }

    public function test_enum_value_duplicate_method(): void
    {
        $originalEnum = EnumValue::factory()->create([
            'type' => 'order_status',
            'key' => 'test_status',
            'value' => 'Test Status',
        ]);

        $duplicatedEnum = $originalEnum->duplicate();

        $this->assertNotEquals($originalEnum->id, $duplicatedEnum->id);
        $this->assertEquals('test_status_copy', $duplicatedEnum->key);
        $this->assertFalse($duplicatedEnum->is_default);
    }

    public function test_enum_value_static_methods(): void
    {
        $types = EnumValue::getTypes();
        $this->assertIsArray($types);
        $this->assertArrayHasKey('order_status', $types);

        EnumValue::factory()->create([
            'type' => 'order_status',
            'key' => 'pending',
            'value' => 'Pending',
        ]);

        $values = EnumValue::getValuesByType('order_status');
        $this->assertIsArray($values);
        $this->assertArrayHasKey('pending', $values);

        $defaultValue = EnumValue::getDefaultValue('order_status');
        $this->assertNull($defaultValue);  // No default set yet
    }

    public function test_enum_value_cleanup_method(): void
    {
        EnumValue::factory()->create([
            'created_at' => now()->subMonths(7),
        ]);

        $deletedCount = EnumValue::cleanupUnused();
        $this->assertIsInt($deletedCount);
    }

    public function test_enum_value_bulk_actions(): void
    {
        $enumValues = EnumValue::factory()->count(3)->create(['is_active' => false]);

        $this
            ->actingAs($this->adminUser)
            ->post('/admin/enum-values/bulk-activate', [
                'records' => $enumValues->pluck('id')->toArray(),
            ])
            ->assertRedirect();

        foreach ($enumValues as $enumValue) {
            $this->assertTrue($enumValue->fresh()->is_active);
        }
    }

    public function test_enum_value_bulk_deactivate(): void
    {
        $enumValues = EnumValue::factory()->count(3)->create(['is_active' => true]);

        $this
            ->actingAs($this->adminUser)
            ->post('/admin/enum-values/bulk-deactivate', [
                'records' => $enumValues->pluck('id')->toArray(),
            ])
            ->assertRedirect();

        foreach ($enumValues as $enumValue) {
            $this->assertFalse($enumValue->fresh()->is_active);
        }
    }

    public function test_enum_value_set_default_action(): void
    {
        $enumValue = EnumValue::factory()->create([
            'type' => 'order_status',
            'is_default' => false,
        ]);

        $this
            ->actingAs($this->adminUser)
            ->post("/admin/enum-values/{$enumValue->id}/set-default")
            ->assertRedirect();

        $this->assertTrue($enumValue->fresh()->is_default);
    }

    public function test_enum_value_metadata_handling(): void
    {
        $metadata = [
            'color' => 'blue',
            'icon' => 'heroicon-o-star',
            'category' => 'test',
        ];

        $enumValue = EnumValue::factory()->create([
            'metadata' => $metadata,
        ]);

        $this->assertEquals($metadata, $enumValue->metadata);
        $this->assertIsArray($enumValue->metadata);
    }

    public function test_enum_value_validation(): void
    {
        $this
            ->actingAs($this->adminUser)
            ->post('/admin/enum-values', [
                'type' => '',
                'key' => '',
                'value' => '',
            ])
            ->assertSessionHasErrors(['type', 'key', 'value']);
    }

    public function test_enum_value_unique_constraints(): void
    {
        EnumValue::factory()->create([
            'type' => 'order_status',
            'key' => 'pending',
        ]);

        $this
            ->actingAs($this->adminUser)
            ->post('/admin/enum-values', [
                'type' => 'order_status',
                'key' => 'pending',
                'value' => 'Pending',
                'name' => 'Pending',
            ])
            ->assertSessionHasErrors(['key']);
    }
}

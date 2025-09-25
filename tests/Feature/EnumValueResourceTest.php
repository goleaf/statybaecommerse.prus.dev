<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\EnumValue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class EnumValueResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
        ]));
    }

    public function test_can_list_enum_values(): void
    {
        EnumValue::factory()->count(3)->create();

        $this
            ->get('/admin/enum-values')
            ->assertOk()
            ->assertSee('Enum Values');
    }

    public function test_can_create_enum_value(): void
    {
        $enumValueData = [
            'type' => 'product_status',
            'key' => 'active',
            'name' => 'Active',
            'value' => 'active',
            'description' => 'Product is active',
            'sort_order' => 1,
            'is_active' => true,
            'is_default' => false,
        ];

        Livewire::test('App\Filament\Resources\EnumValueResource\Pages\CreateEnumValue')
            ->fillForm($enumValueData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enum_values', [
            'type' => 'product_status',
            'key' => 'active',
            'name' => 'Active',
        ]);
    }

    public function test_can_edit_enum_value(): void
    {
        $enumValue = EnumValue::factory()->create([
            'type' => 'product_status',
            'key' => 'active',
        ]);

        $updatedData = [
            'name' => 'Updated Active',
            'description' => 'Updated description',
        ];

        Livewire::test('App\Filament\Resources\EnumValueResource\Pages\EditEnumValue', [
            'record' => $enumValue->getRouteKey(),
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('enum_values', [
            'id' => $enumValue->id,
            'name' => 'Updated Active',
            'description' => 'Updated description',
        ]);
    }

    public function test_can_view_enum_value(): void
    {
        $enumValue = EnumValue::factory()->create();

        $this
            ->get("/admin/enum-values/{$enumValue->id}")
            ->assertOk()
            ->assertSee($enumValue->name);
    }

    public function test_can_delete_enum_value(): void
    {
        $enumValue = EnumValue::factory()->create();

        Livewire::test('App\Filament\Resources\EnumValueResource\Pages\EditEnumValue', [
            'record' => $enumValue->getRouteKey(),
        ])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('enum_values', [
            'id' => $enumValue->id,
        ]);
    }

    public function test_can_filter_by_type(): void
    {
        EnumValue::factory()->create(['type' => 'product_status']);
        EnumValue::factory()->create(['type' => 'order_status']);

        $this
            ->get('/admin/enum-values?tableFilters[type][value]=product_status')
            ->assertOk()
            ->assertSee('product_status')
            ->assertDontSee('order_status');
    }

    public function test_can_filter_by_active_status(): void
    {
        EnumValue::factory()->create(['is_active' => true]);
        EnumValue::factory()->create(['is_active' => false]);

        $this
            ->get('/admin/enum-values?tableFilters[is_active][value]=1')
            ->assertOk();
    }

    public function test_can_bulk_activate_enum_values(): void
    {
        $enumValues = EnumValue::factory()->count(3)->create(['is_active' => false]);

        Livewire::test('App\Filament\Resources\EnumValueResource\Pages\ListEnumValues')
            ->callTableBulkAction('bulk_activate', $enumValues);

        foreach ($enumValues as $enumValue) {
            $this->assertDatabaseHas('enum_values', [
                'id' => $enumValue->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_can_bulk_deactivate_enum_values(): void
    {
        $enumValues = EnumValue::factory()->count(3)->create(['is_active' => true]);

        Livewire::test('App\Filament\Resources\EnumValueResource\Pages\ListEnumValues')
            ->callTableBulkAction('bulk_deactivate', $enumValues);

        foreach ($enumValues as $enumValue) {
            $this->assertDatabaseHas('enum_values', [
                'id' => $enumValue->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_can_set_default_enum_value(): void
    {
        $enumValue1 = EnumValue::factory()->create([
            'type' => 'product_status',
            'is_default' => true,
        ]);
        $enumValue2 = EnumValue::factory()->create([
            'type' => 'product_status',
            'is_default' => false,
        ]);

        Livewire::test('App\Filament\Resources\EnumValueResource\Pages\ListEnumValues')
            ->callTableBulkAction('set_default', [$enumValue2]);

        $this->assertDatabaseHas('enum_values', [
            'id' => $enumValue1->id,
            'is_default' => false,
        ]);

        $this->assertDatabaseHas('enum_values', [
            'id' => $enumValue2->id,
            'is_default' => true,
        ]);
    }

    public function test_enum_value_validation(): void
    {
        Livewire::test('App\Filament\Resources\EnumValueResource\Pages\CreateEnumValue')
            ->fillForm([
                'type' => '',
                'key' => '',
                'name' => '',
                'value' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['type', 'key', 'name', 'value']);
    }

    public function test_enum_value_unique_key_validation(): void
    {
        EnumValue::factory()->create(['key' => 'existing_key']);

        Livewire::test('App\Filament\Resources\EnumValueResource\Pages\CreateEnumValue')
            ->fillForm([
                'type' => 'product_status',
                'key' => 'existing_key',
                'name' => 'Test',
                'value' => 'test',
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);
    }
}

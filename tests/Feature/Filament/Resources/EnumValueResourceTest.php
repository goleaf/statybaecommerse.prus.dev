<?php

declare(strict_types=1);

namespace Tests\\Feature\\Filament\\Resources;

use App\\Filament\\Resources\\EnumValueResource\\Pages\\ListEnumValues;
use App\\Models\\EnumValue;
use App\\Models\\User;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Livewire\\Livewire;
use Tests\\TestCase;

final class EnumValueResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel so resource policies and panels initialise correctly.
        $this->resolveAdminPanel();

        // Pin locales to English so translation driven copy remains stable in assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate a reusable administrator used by every scenario in this suite.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_seeded_enum_values(): void
    {
        // Arrange a deterministic enum so the listing can expose a clear row.
        $enumValue = EnumValue::factory()->create([
            'type'  => 'product_status',
            'key'   => 'coverage-product-status',
            'value' => 'Coverage Product Status',
            'name'  => 'Coverage Product Status',
        ]);

        Livewire::test(ListEnumValues::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$enumValue]);
    }

    public function test_type_filter_handles_single_selection(): void
    {
        // Prepare both product and order status enums to exercise the dropdown filter behaviour.
        $productStatus = EnumValue::factory()->create(['type' => 'product_status']);
        $orderStatus = EnumValue::factory()->create(['type' => 'order_status']);

        Livewire::test(ListEnumValues::class)
            ->call('loadTable')
            ->filterTable('type', 'product_status')
            ->assertCanSeeTableRecords([$productStatus])
            ->assertCanNotSeeTableRecords([$orderStatus]);
    }

    public function test_status_filter_limits_results_to_active_rows(): void
    {
        // Create an active and an inactive enum to validate the status select filter.
        $active = EnumValue::factory()->create(['type' => 'campaign_status', 'is_active' => true]);
        $inactive = EnumValue::factory()->create(['type' => 'campaign_status', 'is_active' => false]);

        Livewire::test(ListEnumValues::class)
            ->call('loadTable')
            ->filterTable('is_active', '1')
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_activate_and_deactivate_table_actions_toggle_state(): void
    {
        // Start with an inactive enum value so both actions can be asserted sequentially.
        $enumValue = EnumValue::factory()->create([
            'type'      => 'notification_type',
            'is_active' => false,
        ]);

        $component = Livewire::test(ListEnumValues::class)
            ->call('loadTable');

        // Activate the record via the row action and confirm persistence toggles the flag.
        $component->callTableAction('activate', $enumValue);
        $enumValue->refresh();
        self::assertTrue($enumValue->is_active, 'Activation action should flip the is_active flag to true.');

        // Deactivate the same record to ensure the complementary action works as expected.
        $component->callTableAction('deactivate', $enumValue);
        $enumValue->refresh();
        self::assertFalse($enumValue->is_active, 'Deactivation action should reset the is_active flag to false.');
    }
}

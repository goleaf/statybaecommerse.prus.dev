<?php

declare(strict_types=1);

namespace Tests\\Feature\\Filament\\Resources;

use App\\Filament\\Resources\\EnumResource\\Pages\\ListEnumValues;
use App\\Filament\\Resources\\EnumResource\\Pages\\ListEnums;
use App\\Models\\EnumValue;
use App\\Models\\User;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Livewire\\Livewire;
use Tests\\TestCase;

final class EnumResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the Filament admin panel so Livewire pages boot with the expected configuration.
        $this->resolveAdminPanel();

        // Normalise locales to avoid translated assertions flapping across environments.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Authenticate a deterministic administrator that can access enum management tools.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_enum_list_page_renders_existing_records(): void
    {
        // Seed a clear enum entry so the table has a predictable row to render.
        $enumValue = EnumValue::factory()->create([
            'type'  => 'order_status',
            'key'   => 'coverage-order-status',
            'value' => 'Coverage Status',
            'name'  => 'Coverage Status',
        ]);

        Livewire::test(ListEnums::class)
            ->call('loadTable') // Hydrate the table before making visibility assertions.
            ->assertCanSeeTableRecords([$enumValue]);
    }

    public function test_type_filter_limits_enum_results(): void
    {
        // Create enums for two distinct types to verify the single select filter.
        $orderStatus = EnumValue::factory()->create(['type' => 'order_status']);
        $paymentStatus = EnumValue::factory()->create(['type' => 'payment_status']);

        Livewire::test(ListEnums::class)
            ->call('loadTable')
            ->filterTable('type', 'order_status') // Apply the filter to emulate the panel UI interaction.
            ->assertCanSeeTableRecords([$orderStatus])
            ->assertCanNotSeeTableRecords([$paymentStatus]);
    }

    public function test_activate_table_action_marks_enum_as_active(): void
    {
        // Provision an inactive enum value so the activate action becomes available.
        $enumValue = EnumValue::factory()->create([
            'type'      => 'order_status',
            'is_active' => false,
        ]);

        Livewire::test(ListEnums::class)
            ->call('loadTable')
            ->callTableAction('activate', $enumValue); // Trigger the custom action exposed in the table row dropdown.

        $enumValue->refresh();

        self::assertTrue($enumValue->is_active, 'Enum activation action should toggle the database flag.');
    }

    public function test_enum_values_list_respects_active_filter(): void
    {
        // Prepare both active and inactive enum values to prove the status filter wiring.
        $activeEnum = EnumValue::factory()->create(['type' => 'product_status', 'is_active' => true]);
        $inactiveEnum = EnumValue::factory()->create(['type' => 'product_status', 'is_active' => false]);

        Livewire::test(ListEnumValues::class)
            ->call('loadTable')
            ->filterTable('is_active', '1') // Filter down to active rows only.
            ->assertCanSeeTableRecords([$activeEnum])
            ->assertCanNotSeeTableRecords([$inactiveEnum]);
    }
}

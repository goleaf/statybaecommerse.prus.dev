<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\ShippingOptionResource\Pages\CreateShippingOption;
use App\Filament\Resources\ShippingOptionResource\Pages\EditShippingOption;
use App\Filament\Resources\ShippingOptionResource\Pages\ListShippingOptions;
use App\Models\ShippingOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

final class ShippingOptionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_access_resource_list_page(): void
    {
        $this->get('/admin/shipping-options')->assertStatus(200);
    }

    public function test_can_list_shipping_options(): void
    {
        $records = ShippingOption::factory()->count(3)->create([
            'currency_code' => 'EUR',
        ]);

        Livewire::test(ListShippingOptions::class)
            ->assertCanSeeTableRecords($records);
    }

    public function test_can_create_shipping_option(): void
    {
        Livewire::test(CreateShippingOption::class)
            ->fillForm([
                'name' => 'Kurieris Express',
                'slug' => 'kurieris-express',
                'carrier_name' => 'Kurieris',
                'service_type' => 'express',
                'description' => 'Greitas pristatymas',
                'price' => 9.99,
                'currency_code' => 'EUR',
                'is_enabled' => true,
                'is_default' => false,
                'sort_order' => 1,
                'estimated_days_min' => 1,
                'estimated_days_max' => 3,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shipping_options', [
            'slug' => 'kurieris-express',
            'carrier_name' => 'Kurieris',
            'service_type' => 'express',
            'currency_code' => 'EUR',
            'is_enabled' => true,
        ]);
    }

    public function test_can_edit_shipping_option(): void
    {
        $record = ShippingOption::factory()->create([
            'name' => 'Standartinis',
            'slug' => 'standartinis',
            'carrier_name' => 'LT Post',
            'service_type' => 'standard',
            'price' => 4.5,
            'currency_code' => 'EUR',
            'is_enabled' => true,
            'estimated_days_min' => 2,
            'estimated_days_max' => 5,
        ]);

        Livewire::test(EditShippingOption::class, ['record' => $record->getKey()])
            ->fillForm([
                'name' => 'Standartinis Plus',
                'price' => 5.25,
                'estimated_days_min' => 2,
                'estimated_days_max' => 4,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shipping_options', [
            'id' => $record->getKey(),
            'name' => 'Standartinis Plus',
            'price' => 5.25,
        ]);
    }
}

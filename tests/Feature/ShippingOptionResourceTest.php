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
        $matrix = [
            'domestic' => [
                'courier'       => true,
                'parcel_locker' => true,
            ],
        ];

        Livewire::test(CreateShippingOption::class)
            ->fillForm([
                'name'               => 'Kurieris Express',
                'slug'               => 'kurieris-express',
                'carrier_name'       => 'Kurieris',
                'service_type'       => 'express',
                'description'        => 'Greitas pristatymas',
                'price'              => 9.99,
                'currency_code'      => 'EUR',
                'is_enabled'         => true,
                'is_default'         => false,
                'sort_order'         => 1,
                'estimated_days_min' => 1,
                'estimated_days_max' => 3,
                'shipping_matrix'    => $matrix,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shipping_options', [
            'slug'          => 'kurieris-express',
            'carrier_name'  => 'Kurieris',
            'service_type'  => 'express',
            'currency_code' => 'EUR',
            'is_enabled'    => true,
        ]);

        $record = ShippingOption::firstWhere('slug', 'kurieris-express');
        $this->assertSame($this->normalizeMatrixState($matrix), $record?->shipping_matrix);
    }

    public function test_can_edit_shipping_option(): void
    {
        $record = ShippingOption::factory()->create([
            'name'               => 'Standartinis',
            'slug'               => 'standartinis',
            'carrier_name'       => 'LT Post',
            'service_type'       => 'standard',
            'price'              => 4.5,
            'currency_code'      => 'EUR',
            'is_enabled'         => true,
            'estimated_days_min' => 2,
            'estimated_days_max' => 5,
            'shipping_matrix'    => $this->normalizeMatrixState([
                'domestic' => [
                    'courier' => true,
                ],
            ]),
        ]);

        $updatedMatrix = [
            'domestic' => [
                'courier'       => true,
                'parcel_locker' => false,
                'post'          => true,
            ],
            'baltics' => [
                'courier' => true,
            ],
        ];

        Livewire::test(EditShippingOption::class, ['record' => $record->getKey()])
            ->assertFormSet('shipping_matrix', $this->normalizeMatrixState([
                'domestic' => [
                    'courier' => true,
                ],
            ]))
            ->fillForm([
                'name'               => 'Standartinis Plus',
                'price'              => 5.25,
                'estimated_days_min' => 2,
                'estimated_days_max' => 4,
                'shipping_matrix'    => $updatedMatrix,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shipping_options', [
            'id'    => $record->getKey(),
            'name'  => 'Standartinis Plus',
            'price' => 5.25,
        ]);

        expect($record->refresh()->shipping_matrix)->toBe($this->normalizeMatrixState($updatedMatrix));
    }

    public function test_shipping_matrix_is_optional_per_row(): void
    {
        $record = ShippingOption::factory()->create([
            'shipping_matrix' => $this->normalizeMatrixState([
                'domestic' => [
                    'courier' => true,
                ],
            ]),
        ]);

        Livewire::test(EditShippingOption::class, ['record' => $record->getKey()])
            ->fillForm([
                'shipping_matrix' => [
                    'domestic' => [
                        'courier'       => false,
                        'parcel_locker' => false,
                        'post'          => false,
                        'freight'       => false,
                    ],
                    'baltics' => [
                        'courier' => false,
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($record->refresh()->shipping_matrix)->toBe($this->normalizeMatrixState([]));
    }

    /**
     * @param  array<string, array<string, bool>> $matrix
     * @return array<string, array<string, bool>>
     */
    private function normalizeMatrixState(array $matrix): array
    {
        $rows = array_keys((array) config('shipping.matrix.zones'));
        $columns = array_keys((array) config('shipping.matrix.methods'));

        $normalized = [];

        foreach ($rows as $rowKey) {
            foreach ($columns as $columnKey) {
                $normalized[$rowKey][$columnKey] = (bool) ($matrix[$rowKey][$columnKey] ?? false);
            }

            if ($normalized[$rowKey] === []) {
                $normalized[$rowKey] = [];
            }
        }

        return $normalized;
    }
}

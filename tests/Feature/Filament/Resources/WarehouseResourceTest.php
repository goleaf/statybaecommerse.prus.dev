<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages\CreateWarehouse;
use App\Filament\Resources\WarehouseResource\Pages\EditWarehouse;
use App\Filament\Resources\WarehouseResource\Pages\ListWarehouses;
use App\Models\Location;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class WarehouseResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolveAdminPanel();
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        $this->admin = User::factory()->create([
            'email'    => 'info@egisstatyba.lt',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_shows_only_warehouse_locations(): void
    {
        $warehouse = Location::factory()->warehouse()->create([
            'name' => 'Main Warehouse',
            'code' => 'WH-001',
        ]);

        $store = Location::factory()->storeType()->create([
            'name' => 'Retail Store',
            'code' => 'ST-001',
        ]);

        Livewire::test(ListWarehouses::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$warehouse])
            ->assertCanNotSeeTableRecords([$store]);
    }

    public function test_create_page_forces_type_to_warehouse(): void
    {
        Livewire::test(CreateWarehouse::class)
            ->fillForm([
                'code'        => 'WH-100',
                'name'        => ['en' => 'Central Warehouse'],
                'slug'        => ['en' => 'central-warehouse'],
                'description' => ['en' => 'Main distribution center'],
                'is_enabled'  => true,
                'is_default'  => false,
                'sort_order'  => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $warehouse = Location::query()->where('code', 'WH-100')->first();

        $this->assertNotNull($warehouse);
        $this->assertSame('warehouse', $warehouse->type);
    }

    public function test_edit_page_keeps_type_as_warehouse(): void
    {
        $warehouse = Location::factory()->warehouse()->create([
            'code' => 'WH-210',
            'name' => 'Legacy Warehouse',
            'slug' => 'legacy-warehouse',
        ]);

        Livewire::test(EditWarehouse::class, ['record' => $warehouse->getRouteKey()])
            ->fillForm([
                'code'        => 'WH-211',
                'name'        => ['en' => 'Updated Warehouse'],
                'slug'        => ['en' => 'updated-warehouse'],
                'description' => ['en' => 'Updated description'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $warehouse->refresh();

        $this->assertSame('WH-211', $warehouse->code);
        $this->assertSame('warehouse', $warehouse->type);
    }

    public function test_delete_action_removes_warehouse_record(): void
    {
        $warehouse = Location::factory()->warehouse()->create([
            'code' => 'WH-301',
        ]);

        Livewire::test(EditWarehouse::class, ['record' => $warehouse->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($warehouse);
    }
}

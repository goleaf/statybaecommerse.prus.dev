<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\StockMovementResource;
use App\Filament\Resources\StockMovementResource\Pages\ListStockMovements;
use App\Models\StockMovement;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

final class StockMovementResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Ensure required permissions exist for Filament topbar checks
        \Spatie\Permission\Models\Permission::findOrCreate('view notifications');
        $this->adminUser->givePermissionTo('view notifications');

        // Define dummy route for system settings navigation link used by Filament topbar
        Route::get('/__dummy-system-settings', fn () => 'ok')
            ->name('filament.admin.resources.system-settings.index');

        Filament::setCurrentPanel('admin');
    }

    public function test_pages_resolve_and_list_view_renders(): void
    {
        $records = StockMovement::factory()->count(2)->create();

        Livewire::actingAs($this->adminUser)
            ->test(ListStockMovements::class)
            ->assertStatus(200)
            ->assertCanSeeTableRecords($records);
    }

    public function test_create_page_renders(): void
    {
        $this
            ->actingAs($this->adminUser)
            ->get(StockMovementResource::getUrl('create'))
            ->assertOk();
    }

    public function test_edit_page_renders(): void
    {
        $record = StockMovement::factory()->create();

        $this
            ->actingAs($this->adminUser)
            ->get(StockMovementResource::getUrl('edit', ['record' => $record]))
            ->assertOk();
    }
}

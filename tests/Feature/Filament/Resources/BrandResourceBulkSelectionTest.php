<?php

declare(strict_types=1);

use App\Filament\Resources\BrandResource\Pages\ListBrands;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->resolveAdminPanel();

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'is_admin' => true,
    ]);

    $this->actingAs($admin);
});

it('allows bulk deleting brands from the list table', function (): void {
    $brands = Brand::factory()->count(2)->create();

    Livewire::test(ListBrands::class)
        ->callTableBulkAction('delete', $brands)
        ->assertHasNoTableBulkActionErrors();

    foreach ($brands as $brand) {
        $this->assertSoftDeleted('brands', [
            'id' => $brand->id,
        ]);
    }
});

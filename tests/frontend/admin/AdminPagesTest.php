<?php

declare(strict_types=1);

use App\Filament\Pages\DataImportExport;
use App\Filament\Pages\InventoryManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->create(['email' => 'admin@example.com']);
    Role::findOrCreate('admin');
    $this->admin->assignRole('admin');
});

describe('DataImportExport Page', function (): void {
    it('can render data import export page', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(DataImportExport::class);
        $component->assertOk();
    });

    it('allows admin users to access import export page', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(DataImportExport::class);
        $component->assertOk();
    });

    it('denies non-admin users access to import export page', function (): void {
        $user = User::factory()->create();

        actingAs($user);

        $component = Livewire::test(DataImportExport::class);
        $component->assertStatus(403);
    });
});

describe('InventoryManagement Page', function (): void {
    it('can render inventory management page', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(InventoryManagement::class);
        $component->assertOk();
    });

    it('allows admin users to access inventory management page', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(InventoryManagement::class);
        $component->assertOk();
    });

    it('denies non-admin users access to inventory management page', function (): void {
        $user = User::factory()->create();

        actingAs($user);

        $component = Livewire::test(InventoryManagement::class);
        $component->assertStatus(403);
    });
});

describe('Admin Page Security', function (): void {
    it('requires authentication for all admin pages', function (): void {
        $pages = [
            DataImportExport::class,
            InventoryManagement::class,
        ];

        foreach ($pages as $page) {
            $component = Livewire::test($page);
            $component->assertStatus(403);
        }
    });

    it('validates user permissions for admin pages', function (): void {
        $user = User::factory()->create();

        actingAs($user);

        $pages = [
            DataImportExport::class,
            InventoryManagement::class,
        ];

        foreach ($pages as $page) {
            $component = Livewire::test($page);
            $component->assertStatus(403);
        }
    });
});

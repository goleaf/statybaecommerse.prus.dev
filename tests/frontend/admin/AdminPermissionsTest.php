<?php

declare(strict_types=1);

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\DataImportExport;
use App\Filament\Pages\InventoryManagement;
use App\Filament\Resources\AnalyticsEventResource;
use App\Filament\Resources\AnalyticsResource;
use App\Filament\Resources\CampaignResource;
use App\Filament\Resources\NotificationResource;
use App\Filament\Resources\PartnerTierResource;
use App\Filament\Resources\SystemSettingsResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->create(['email' => 'admin@example.com']);
    $this->manager = User::factory()->create(['email' => 'manager@example.com']);
    $this->user = User::factory()->create(['email' => 'user@example.com']);

    // Create roles
    $adminRole = Role::findOrCreate('admin');
    $managerRole = Role::findOrCreate('manager');
    $userRole = Role::findOrCreate('user');

    // Create permissions
    $permissions = [
        'view_dashboard',
        'view_campaigns',
        'create_campaigns',
        'edit_campaigns',
        'delete_campaigns',
        'view_settings',
        'create_settings',
        'edit_settings',
        'delete_settings',
        'view_notifications',
        'create_notifications',
        'edit_notifications',
        'delete_notifications',
        'view_analytics',
        'view_analytics_events',
        'create_analytics_events',
        'edit_analytics_events',
        'delete_analytics_events',
        'view_partner_tiers',
        'create_partner_tiers',
        'edit_partner_tiers',
        'delete_partner_tiers',
        'view_import_export',
        'view_inventory',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission);
    }

    // Assign roles
    $this->admin->assignRole('admin');
    $this->manager->assignRole('manager');
    $this->user->assignRole('user');

    // Give admin all permissions
    $this->admin->givePermissionTo($permissions);

    // Give manager limited permissions
    $this->manager->givePermissionTo([
        'view_dashboard',
        'view_campaigns',
        'create_campaigns',
        'edit_campaigns',
        'view_notifications',
        'view_analytics',
    ]);

    // Give user minimal permissions
    $this->user->givePermissionTo([
        'view_dashboard',
        'view_notifications',
    ]);
});

describe('Admin Role Permissions', function (): void {
    it('allows admin to access all resources', function (): void {
        actingAs($this->admin);

        $resources = [
            CampaignResource::class,
            SystemSettingsResource::class,
            NotificationResource::class,
            AnalyticsEventResource::class,
            PartnerTierResource::class,
            AnalyticsResource::class,
        ];

        foreach ($resources as $resource) {
            $this->get($resource::getUrl('index'))
                ->assertOk();
        }
    });

    it('allows admin to access all pages', function (): void {
        actingAs($this->admin);

        $pages = [
            Dashboard::class,
            DataImportExport::class,
            InventoryManagement::class,
        ];

        foreach ($pages as $page) {
            $component = Livewire::test($page);
            $component->assertOk();
        }
    });

    it('allows admin to perform all CRUD operations', function (): void {
        actingAs($this->admin);

        // Test campaign creation
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Admin Campaign',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Test setting creation
        Livewire::test(\App\Filament\Resources\SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => 'admin_setting',
                'value' => 'admin_value',
                'display_name' => 'Admin Setting',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    });
});

describe('Manager Role Permissions', function (): void {
    it('allows manager to access permitted resources', function (): void {
        actingAs($this->manager);

        // Should have access to these
        $this->get(CampaignResource::getUrl('index'))
            ->assertOk();
        $this->get(NotificationResource::getUrl('index'))
            ->assertOk();
        $this->get(AnalyticsResource::getUrl('index'))
            ->assertOk();
    });

    it('denies manager access to restricted resources', function (): void {
        actingAs($this->manager);

        // Should not have access to these
        $this->get(SystemSettingsResource::getUrl('index'))
            ->assertStatus(403);
        $this->get(PartnerTierResource::getUrl('index'))
            ->assertStatus(403);
    });

    it('allows manager to perform permitted CRUD operations', function (): void {
        actingAs($this->manager);

        // Should be able to create campaigns
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Manager Campaign',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    });

    it('denies manager restricted CRUD operations', function (): void {
        actingAs($this->manager);

        // Should not be able to create settings
        Livewire::test(\App\Filament\Resources\SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->assertStatus(403);
    });
});

describe('User Role Permissions', function (): void {
    it('allows user to access minimal resources', function (): void {
        actingAs($this->user);

        // Should have access to dashboard and notifications only
        $component = Livewire::test(Dashboard::class);
        $component->assertOk();

        $this->get(NotificationResource::getUrl('index'))
            ->assertOk();
    });

    it('denies user access to most resources', function (): void {
        actingAs($this->user);

        $restrictedResources = [
            CampaignResource::class,
            SystemSettingsResource::class,
            AnalyticsEventResource::class,
            PartnerTierResource::class,
            AnalyticsResource::class,
        ];

        foreach ($restrictedResources as $resource) {
            $this->get($resource::getUrl('index'))
                ->assertStatus(403);
        }
    });

    it('denies user access to admin pages', function (): void {
        actingAs($this->user);

        $adminPages = [
            DataImportExport::class,
            InventoryManagement::class,
        ];

        foreach ($adminPages as $page) {
            $component = Livewire::test($page);
            $component->assertStatus(403);
        }
    });
});

describe('Permission-based Access Control', function (): void {
    it('respects individual permissions', function (): void {
        $user = User::factory()->create();
        $user->givePermissionTo('view_campaigns');

        actingAs($user);

        // Should have access to campaigns
        $this->get(CampaignResource::getUrl('index'))
            ->assertOk();

        // Should not have access to settings
        $this->get(SystemSettingsResource::getUrl('index'))
            ->assertStatus(403);
    });

    it('allows users with view_dashboard permission to access dashboard', function (): void {
        $user = User::factory()->create();
        $user->givePermissionTo('view_dashboard');

        actingAs($user);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();
    });

    it('denies users without view_dashboard permission', function (): void {
        $user = User::factory()->create();
        // No permissions assigned

        actingAs($user);

        $component = Livewire::test(Dashboard::class);
        $component->assertStatus(403);
    });
});

describe('Resource-specific Permissions', function (): void {
    it('enforces campaign permissions correctly', function (): void {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_campaigns', 'create_campaigns']);

        actingAs($user);

        // Should be able to view and create
        $this->get(CampaignResource::getUrl('index'))
            ->assertOk();

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => 'Test Campaign',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        // Should not be able to edit without permission
        $campaign = \App\Models\Campaign::factory()->create();
        $this->get(CampaignResource::getUrl('edit', ['record' => $campaign->id]))
            ->assertStatus(403);
    });

    it('enforces settings permissions correctly', function (): void {
        $user = User::factory()->create();
        $user->givePermissionTo(['view_settings', 'edit_settings']);

        actingAs($user);

        // Should be able to view
        $this->get(SystemSettingsResource::getUrl('index'))
            ->assertOk();

        // Should not be able to create without permission
        Livewire::test(\App\Filament\Resources\SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->assertStatus(403);
    });
});

describe('Role Hierarchy', function (): void {
    it('admin role has all permissions', function (): void {
        expect($this->admin->hasRole('admin'))->toBeTrue();
        expect($this->admin->can('view_dashboard'))->toBeTrue();
        expect($this->admin->can('create_campaigns'))->toBeTrue();
        expect($this->admin->can('edit_settings'))->toBeTrue();
    });

    it('manager role has limited permissions', function (): void {
        expect($this->manager->hasRole('manager'))->toBeTrue();
        expect($this->manager->can('view_dashboard'))->toBeTrue();
        expect($this->manager->can('create_campaigns'))->toBeTrue();
        expect($this->manager->can('edit_settings'))->toBeFalse();
    });

    it('user role has minimal permissions', function (): void {
        expect($this->user->hasRole('user'))->toBeTrue();
        expect($this->user->can('view_dashboard'))->toBeTrue();
        expect($this->user->can('create_campaigns'))->toBeFalse();
        expect($this->user->can('edit_settings'))->toBeFalse();
    });
});

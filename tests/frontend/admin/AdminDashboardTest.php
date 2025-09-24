<?php

declare(strict_types=1);

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\EcommerceOverview;
use App\Filament\Widgets\RealtimeAnalyticsWidget;
use App\Filament\Widgets\TopProductsWidget;
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

describe('Admin Dashboard', function (): void {
    it('can render admin dashboard', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();
    });

    it('displays correct navigation label', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();

        // Check if the dashboard title is displayed
        expect(Dashboard::getNavigationLabel())->toBe(__('admin.navigation.dashboard'));
    });

    it('displays correct subheading', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();

        // Check if the dashboard description is displayed
        expect($component->get('subheading'))->toBe(__('admin.dashboard.description'));
    });

    it('includes all required widgets', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $widgets = $component->get('widgets');

        expect($widgets)->toContain(EcommerceOverview::class);
        expect($widgets)->toContain(RealtimeAnalyticsWidget::class);
        expect($widgets)->toContain(TopProductsWidget::class);
    });

    it('has correct column configuration', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $columns = $component->get('columns');

        expect($columns)->toBe([
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ]);
    });

    it('allows access to admin users', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();

        expect(Dashboard::canAccess())->toBeTrue();
    });

    it('denies access to non-admin users', function (): void {
        $user = User::factory()->create();

        actingAs($user);

        $component = Livewire::test(Dashboard::class);
        $component->assertStatus(403);
    });
});

describe('Dashboard Widgets Integration', function (): void {
    it('renders all widgets without errors', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();

        // Test that all widgets can be rendered
        $widgets = [
            EcommerceOverview::class,
            RealtimeAnalyticsWidget::class,
            TopProductsWidget::class,
        ];

        foreach ($widgets as $widget) {
            $widgetComponent = Livewire::test($widget);
            $widgetComponent->assertOk();
        }
    });

    it('displays widgets in correct order', function (): void {
        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $widgets = $component->get('widgets');

        // Check that widgets are in the expected order
        expect($widgets[0])->toBe(EcommerceOverview::class);
        expect($widgets[1])->toBe(RealtimeAnalyticsWidget::class);
        expect($widgets[2])->toBe(TopProductsWidget::class);
    });
});

describe('Dashboard Performance', function (): void {
    it('loads dashboard quickly', function (): void {
        actingAs($this->admin);

        $startTime = microtime(true);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Dashboard should load in less than 2 seconds
        expect($executionTime)->toBeLessThan(2.0);
    });

    it('handles large datasets efficiently', function (): void {
        // Create large dataset
        \App\Models\Product::factory()->count(100)->create();
        \App\Models\Order::factory()->count(50)->create();
        \App\Models\Campaign::factory()->count(20)->create();

        actingAs($this->admin);

        $startTime = microtime(true);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Dashboard should still load quickly with large datasets
        expect($executionTime)->toBeLessThan(3.0);
    });
});

describe('Dashboard Translations', function (): void {
    it('displays correct Lithuanian translations', function (): void {
        app()->setLocale('lt');

        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();

        expect(Dashboard::getNavigationLabel())->toBe(__('admin.navigation.dashboard'));
    });

    it('displays correct English translations', function (): void {
        app()->setLocale('en');

        actingAs($this->admin);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();

        expect(Dashboard::getNavigationLabel())->toBe(__('admin.navigation.dashboard'));
    });
});

describe('Dashboard Security', function (): void {
    it('requires authentication', function (): void {
        $component = Livewire::test(Dashboard::class);
        $component->assertStatus(403);
    });

    it('validates user permissions', function (): void {
        $user = User::factory()->create();

        actingAs($user);

        $component = Livewire::test(Dashboard::class);
        $component->assertStatus(403);
    });

    it('allows access only to users with view_dashboard permission', function (): void {
        $user = User::factory()->create();
        $user->givePermissionTo('view_dashboard');

        actingAs($user);

        $component = Livewire::test(Dashboard::class);
        $component->assertOk();
    });
});

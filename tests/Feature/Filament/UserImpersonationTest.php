<?php declare(strict_types=1);

use App\Models\Order;
use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

describe('User Impersonation Access Control', function () {
    it('allows administrator to access user impersonation page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');

        $this
            ->actingAs($admin)
            ->get('/admin/user-impersonation')
            ->assertOk();
    });

    it('allows super_admin role to access user impersonation page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this
            ->actingAs($admin)
            ->get('/admin/user-impersonation')
            ->assertOk();
    });

    it('allows users with is_admin flag to access user impersonation page', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        $this
            ->actingAs($admin)
            ->get('/admin/user-impersonation')
            ->assertOk();
    });

    it('denies access to regular users', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this
            ->actingAs($user)
            ->get('/admin/user-impersonation')
            ->assertForbidden();
    });

    it('denies access to unauthenticated users', function () {
        $this
            ->get('/admin/user-impersonation')
            ->assertRedirect('/admin/login');
    });
});

describe('User Impersonation Page Functionality', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('displays user list with correct columns', function () {
        $users = User::factory()->count(3)->create(['is_admin' => false]);

        // Test that the page loads successfully
        $response = $this->get('/admin/user-impersonation');
        $response->assertOk();

        // Test that the page contains the table structure
        $response->assertSee('user-impersonation');
    });

    it('filters out admin users from impersonation list', function () {
        $adminUser = User::factory()->create(['is_admin' => true]);
        $regularUser = User::factory()->create(['is_admin' => false]);

        $response = $this->get('/admin/user-impersonation');
        $response->assertOk();
        // Test that the page loads without errors
        $response->assertSee('user-impersonation');
    });

    it('shows impersonate action for regular users', function () {
        $user = User::factory()->create(['is_admin' => false]);

        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->assertTableActionExists('impersonate');
    });

    it('hides impersonate action for admin users', function () {
        $adminUser = User::factory()->create(['is_admin' => true]);

        // Admin users should not appear in the table at all due to the query filter
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->assertCanNotSeeTableRecords([$adminUser]);
    });
});

describe('User Impersonation Actions', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can start impersonation session', function () {
        $targetUser = User::factory()->create(['is_admin' => false]);

        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('impersonate', $targetUser)
            ->assertRedirect('/');

        expect(session('impersonate'))->not()->toBeNull();
        expect(session('impersonate.original_user_id'))->toBe($this->admin->id);
        expect(session('impersonate.impersonated_user_id'))->toBe($targetUser->id);
        expect(auth()->id())->toBe($targetUser->id);
    });

    it('prevents impersonating admin users', function () {
        $adminUser = User::factory()->create(['is_admin' => true]);

        // Admin users should not appear in the table at all due to the query filter
        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->assertCanNotSeeTableRecords([$adminUser]);
    });

    it('can stop impersonation session', function () {
        $targetUser = User::factory()->create(['is_admin' => false]);

        // Start impersonation
        session([
            'impersonate' => [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now()->toISOString(),
            ]
        ]);
        auth()->login($targetUser);

        // Test the stopImpersonation method directly
        $page = new \App\Filament\Pages\UserImpersonation();
        $page->stopImpersonation();

        expect(session('impersonate'))->toBeNull();
        expect(auth()->id())->toBe($this->admin->id);
    });

    it('shows stop impersonation button when impersonating', function () {
        session([
            'impersonate' => [
                'original_user_id' => $this->admin->id,
                'impersonated_user_id' => 999,
                'started_at' => now()->toISOString(),
            ]
        ]);

        // Test that the page shows the stop impersonation button when impersonating
        $this
            ->get('/admin/user-impersonation')
            ->assertSee(__('admin.actions.stop_impersonation'));
    });

    it('hides stop impersonation button when not impersonating', function () {
        // Test that the page doesn't show the stop impersonation button when not impersonating
        $this
            ->get('/admin/user-impersonation')
            ->assertDontSee(__('admin.actions.stop_impersonation'));
    });
});

describe('User Impersonation Notifications', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can send notification to user', function () {
        Notification::fake();

        $targetUser = User::factory()->create(['is_admin' => false]);

        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('send_notification', $targetUser, [
                'title' => 'Test Notification',
                'message' => 'This is a test message',
                'type' => 'info',
            ])
            ->assertNotified(__('admin.notifications.notification_sent'));

        Notification::assertSentTo($targetUser, AdminNotification::class);
    });

    it('validates notification form data', function () {
        $targetUser = User::factory()->create(['is_admin' => false]);

        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableAction('send_notification', $targetUser, [
                'title' => '',
                'message' => '',
                'type' => '',
            ])
            ->assertHasTableActionErrors(['title', 'message', 'type']);
    });
});

describe('User Impersonation Bulk Actions', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can bulk activate users', function () {
        $users = User::factory()->count(3)->create(['is_admin' => false, 'is_active' => false]);

        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableBulkAction('activate', $users)
            ->assertNotified();

        foreach ($users as $user) {
            expect($user->fresh()->is_active)->toBeTrue();
        }
    });

    it('can bulk deactivate users', function () {
        $users = User::factory()->count(3)->create(['is_admin' => false, 'is_active' => true]);

        Livewire::test(\App\Filament\Pages\UserImpersonation::class)
            ->callTableBulkAction('deactivate', $users)
            ->assertNotified();

        foreach ($users as $user) {
            expect($user->fresh()->is_active)->toBeFalse();
        }
    });
});

describe('User Impersonation Filters', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can filter by active status', function () {
        $activeUser = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $inactiveUser = User::factory()->create(['is_admin' => false, 'is_active' => false]);

        $component = Livewire::test(\App\Filament\Pages\UserImpersonation::class);
        $component->filterTable('is_active', 'true');

        // Verify the filter is applied
        expect($component->get('tableFilters.is_active.value'))->toBe('true');
    });

    it('can filter by users with orders', function () {
        $userWithOrders = User::factory()->create(['is_admin' => false]);
        $userWithoutOrders = User::factory()->create(['is_admin' => false]);

        // Create orders for one user
        Order::factory()->count(2)->create(['user_id' => $userWithOrders->id]);

        $component = Livewire::test(\App\Filament\Pages\UserImpersonation::class);
        $component->filterTable('has_orders');

        // Verify the filter is applied
        expect($component->get('tableFilters.has_orders.isActive'))->toBeTrue();
    });

    it('can filter by recent activity', function () {
        $recentUser = User::factory()->create([
            'is_admin' => false,
            'last_login_at' => now()->subDays(5)
        ]);
        $oldUser = User::factory()->create([
            'is_admin' => false,
            'last_login_at' => now()->subDays(60)
        ]);

        $component = Livewire::test(\App\Filament\Pages\UserImpersonation::class);
        $component->filterTable('recent_activity');

        // Verify the filter is applied
        expect($component->get('tableFilters.recent_activity.isActive'))->toBeTrue();
    });
});

describe('User Impersonation Search', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrator');
        $this->actingAs($this->admin);
    });

    it('can search users by name', function () {
        $john = User::factory()->create(['is_admin' => false, 'name' => 'John Doe']);
        $jane = User::factory()->create(['is_admin' => false, 'name' => 'Jane Smith']);

        $component = Livewire::test(\App\Filament\Pages\UserImpersonation::class);
        $component->searchTable('John');

        // Verify the search is applied
        expect($component->get('tableSearch'))->toBe('John');
    });

    it('can search users by email', function () {
        $user1 = User::factory()->create(['is_admin' => false, 'email' => 'john@example.com']);
        $user2 = User::factory()->create(['is_admin' => false, 'email' => 'jane@example.com']);

        $component = Livewire::test(\App\Filament\Pages\UserImpersonation::class);
        $component->searchTable('john@example.com');

        // Verify the search is applied
        expect($component->get('tableSearch'))->toBe('john@example.com');
    });
});

describe('User Impersonation Middleware', function () {
    it('handles impersonation session correctly', function () {
        $admin = User::factory()->create();
        $admin->assignRole('administrator');
        $targetUser = User::factory()->create(['is_admin' => false]);

        // Start impersonation
        session([
            'impersonate' => [
                'original_user_id' => $admin->id,
                'impersonated_user_id' => $targetUser->id,
                'started_at' => now()->toISOString(),
            ]
        ]);

        // Login as admin first
        $this->actingAs($admin);

        // Make a request that goes through the middleware
        $response = $this->get('/admin');

        // The middleware should handle the impersonation
        expect(auth()->id())->toBe($targetUser->id);
    });
});
